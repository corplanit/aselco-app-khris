<script>
    window.AUTH_ID = {{ auth()->id() }};
    window.ACTIVE_CONV_ID = null; // set when a conversation is opened
    window.ACTIVE_CONVERSATION = null; // set when a conversation is opened
</script>

<style>
    .stack {
        position: relative;
        width: 2.25rem;
        height: 2.25rem
    }

    .stack img {
        position: absolute;
        border: 2px solid #fff;
        border-radius: 9999px;
        width: 2.25rem;
        height: 2.25rem;
        object-fit: cover
    }

    .stack img:nth-child(1) {
        left: 0;
        z-index: 30
    }

    .stack img:nth-child(2) {
        left: .8rem;
        z-index: 20
    }

    .stack img:nth-child(3) {
        left: 1.6rem;
        z-index: 10
    }

    /* Soft pulse highlight when a conversation receives a new message */
    @keyframes pulse-glow-light {
        0% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.26);
        }

        70% {
            box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        }
    }

    .row-pulse {
        animation: pulse-glow-light 1.2s ease-out 1;
    }
</style>

<style>
    /* Small pulsing red dot used for meeting indicators */
    .meeting-dot {
        position: relative;
        display: inline-block;
        width: 8px;
        height: 8px;
        background-color: #44ef5b;
        /* tailwind red-500 */
        border-radius: 9999px;
    }

    .meeting-dot::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        animation: pulse-ring 1.2s infinite;
        box-shadow: 0 0 0 0 rgba(68, 239, 111, 0.6);
    }

    @keyframes pulse-ring {
        0% {
            box-shadow: 0 0 0 0 rgba(68, 239, 154, 0.6);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        }
    }

    /* Call header reveal (re-uses your fadeDown idea) */
    #header-call {
        display: none;
    }

    #header-call.show {
        display: flex;
        animation: fadeDown 0.3s ease-out forwards;
    }

    @keyframes fadeDown {
        0% {
            opacity: 0;
            transform: translateY(-10px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
<audio id="meeting-ringtone" preload="auto" loop>
    <source src="/assets/ringtone.mp3" type="audio/mpeg">
</audio>
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
(function () {
  // ---------- helpers ----------
  function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  function getUserIdFromQuery() {
    const val = new URLSearchParams(location.search).get('chat');
    const n = Number(val);
    return Number.isFinite(n) && n > 0 ? n : null;
  }

  function ready(fn) {
    const iv = setInterval(() => {
      if (window.jQuery &&
          typeof fetchConversations === 'function' &&
          typeof setActiveConversation === 'function') {
        clearInterval(iv); fn();
      }
    }, 80);
    setTimeout(() => clearInterval(iv), 8000);
  }

  async function hydrateMeetingLink(convId) {
    try {
      const res = await $.post({
        url: `/chats/conversations/${encodeURIComponent(convId)}/info`,
        headers: { 'X-CSRF-TOKEN': csrf() }
      });
      const key = 'ZAVPsOPpa5GIb4Cb7EizxHXAlv7YduOqfYqeHIZD';
      const name = res?.conv?.name || null;
      const safe = name ? (name + ' Meeting ' + key + (res?.conv?.id ?? convId))
                        : (key + (res?.conv?.id ?? convId));
      window.ACTIVE_CONVERSATION = safe;
      const late = document.getElementById('joinBtnLate');
      if (late) late.dataset.room = window.ACTIVE_CONVERSATION;
    } catch (e) { console.warn('hydrateMeetingLink failed', e); }
  }

  function openInSidebar(conv) {
    if (conv && typeof upsertConversationRow === 'function') {
      upsertConversationRow(conv, { toTop: true, pulse: false });
    } else if (typeof fetchConversations === 'function') {
      fetchConversations();
    }
  }

  async function markReadAndRefresh(convId) {
    try {
      await $.post({
        url: `/chats/conversations/${encodeURIComponent(convId)}/read`,
        headers: { 'X-CSRF-TOKEN': csrf() }
      });
    } catch (_) {}
    if (typeof fetchConversations === 'function') fetchConversations();
  }

  async function activate(convId) {
    await setActiveConversation(convId);
    window.dispatchEvent(new CustomEvent('open-conversation', { detail: { id: convId }}));
    await hydrateMeetingLink(convId);
    await markReadAndRefresh(convId);
  }

  // Find-or-create DM then open
  async function openDmWithUser(userId) {
    try {
      const res = await $.ajax({
        url: '/chats/conversations/open',
        method: 'POST',
        dataType: 'json',
        data: { user_id: userId },
        headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
      });
      const conv = res?.conversation || res?.data || res;
      if (!conv?.id) throw new Error('No conversation returned');
      openInSidebar(conv);
      await activate(conv.id);
    } catch (err) {
      console.error('DM create/open failed:', err);
      // Helpful debugging toast:
      const msg = err?.responseJSON?.message || err?.statusText || String(err);
      alert('Could not open/create DM. ' + msg);
    }
  }

  // ---------- Auto-open via ?chat=USER_ID ----------
  const userId = getUserIdFromQuery();
  if (userId) {
    ready(() => {
      if (typeof fetchConversations === 'function') fetchConversations();
      setTimeout(() => openDmWithUser(userId), 220);

      // Clean URL so reload doesn't re-trigger
      if (history.replaceState) {
        const url = new URL(location.href);
        url.searchParams.delete('chat');
        history.replaceState({}, '', url.toString());
      }
    });
  }

  // ---------- Optional: button to open/create DM ----------
  // <button class="open-dm" data-user="123">Message</button>
  const dmBusy = new Set();
  $(document).on('click', '.open-dm', async function (e) {
    e.preventDefault();
    const targetId = Number($(this).data('user'));
    if (!Number.isFinite(targetId) || targetId <= 0 || targetId === Number(window.AUTH_ID)) return;
    if (dmBusy.has(targetId)) return;
    dmBusy.add(targetId);
    try { await openDmWithUser(targetId); }
    catch (err) { /* already logged */ }
    finally { dmBusy.delete(targetId); }
  });

})();
</script>



</script>


<script>
    let ringtoneAudio;
    let ringtoneTimer; // for auto-stop timeout


    function triggerMeetingUI() {

        const $meetingBtn = $('#meeting-btn');
        const $meetingStarted = $('#meeting-started');
        const meetBtn = document.getElementById('meeting-btn');
        const joinBtn = document.getElementById('joinBtnLate');

        $meetingBtn[0]?.classList.add('pulse-red-light');
        $meetingStarted[0]?.classList.add('pulse-red-light');
        $meetingStarted[0]?.classList.remove('hidden');

        console.log($meetingStarted)
        meetBtn.classList.remove('hidden');
        joinBtn.classList.add('show');

        const header = document.getElementById('header-call');
        if (header) header.classList.add('show');

        // Light up indicator
        const pulseTarget = document.getElementById('pulse-indicator');
        if (pulseTarget) pulseTarget.classList.add('meeting-dot');

        // Play ringtone
        ringtoneAudio = document.getElementById('meeting-ringtone');
        if (ringtoneAudio) {
            ringtoneAudio.currentTime = 0;
            ringtoneAudio.play().catch(err => console.warn("Autoplay blocked:", err));
        }

        // Auto-stop after 10s
        clearTimeout(ringtoneTimer);
        ringtoneTimer = setTimeout(() => {
            stopMeetingUI(); // hide header + stop ringtone
        }, 10000);
    }

    function stopMeetingUI() {
        const header = document.getElementById('header-call');

        if (header) header.classList.remove('show');

        const pulseTarget = document.getElementById('pulse-indicator');
        if (pulseTarget) pulseTarget.classList.remove('meeting-dot');

        if (ringtoneAudio) {
            ringtoneAudio.pause();
            ringtoneAudio.currentTime = 0;
        }

        const meetBtn = document.getElementById('meeting-btn');
        const joinBtn = document.getElementById('joinBtnLate');

        meetBtn.classList.add('hidden');
        joinBtn.classList.remove('hidden');

        clearTimeout(ringtoneTimer);
    }
</script>

<script>
    /*************************************************
     * POLLER — Only new incoming (exclude own msgs)
     *************************************************/
    const POLL_MIN_MS = 3000;
    const POLL_MAX_MS = 5000;
    const HIDDEN_MIN_MS = 10000;
    const HIDDEN_MAX_MS = 15000;

    let msgPollTimer = null;
    let msgInFlight = false;

    // Per-conversation state for messages
    const lastSeenByConv = Object.create(null); // { [convId]: number }
    const seenIdsByConv = Object.create(null); // { [convId]: Set<number> }

    /*********************************************
     * SIDEBAR real‑time (light) poller
     *********************************************/
    const SIDEBAR_MIN_MS = 5000;
    const SIDEBAR_MAX_MS = 8000;
    let sidebarPollTimer = null;
    let sidebarInFlight = false;

    // Track heads to detect updates without repainting everything
    // { [convId]: { latestId:number, unread:number } }
    const headByConv = Object.create(null);

    function jitter(a, b) {
        return a + Math.floor(Math.random() * (b - a + 1));
    }

    function getSeenSet(convId) {
        return (seenIdsByConv[convId] ||= new Set());
    }

    function getLastSeen(convId) {
        return typeof lastSeenByConv[convId] === 'number' ? lastSeenByConv[convId] : 0;
    }

    function setLastSeen(convId, id) {
        if (Number.isFinite(id) && id > getLastSeen(convId)) lastSeenByConv[convId] = id;
    }

    /** Prime pointer to latest id (no render) so opening a convo doesn't dump history */
    async function primeConversationPointer(convId) {
        try {
            const res = await $.ajax({
                url: `/chats/conversations/${encodeURIComponent(convId)}/messages?limit=1&order=desc`,
                method: 'GET',
                dataType: 'json'
            });

            const items = Array.isArray(res?.data) ? res.data : (Array.isArray(res) ? res : []);
            const latest = items[0];
            if (latest?.id) {
                const lid = Number(latest.id);
                setLastSeen(convId, lid);
                getSeenSet(convId).add(lid);
            }
        } catch (e) {
            /* ignore */
        }
    }

    /***************
     * MSG poller
     ***************/
    function startMessagePolling() {
        stopMessagePolling();
        const scheduleNext = (ms) => {
            clearTimeout(msgPollTimer);
            msgPollTimer = setTimeout(pollOnce, ms);
        };

        function pollOnce() {
            const convId = window.ACTIVE_CONV_ID;
            if (!convId || msgInFlight) {
                scheduleNext(jitter(POLL_MIN_MS, POLL_MAX_MS));
                return;
            }
            msgInFlight = true;

            const since = getLastSeen(convId) || 0;
            const url =
                `/chats/conversations/${encodeURIComponent(convId)}/messages?since_id=${encodeURIComponent(since)}&limit=50`;

            $.ajax({
                    url,
                    method: 'GET',
                    dataType: 'json',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .done(function(resp) {
                    const items = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? resp : []);
                    const seen = getSeenSet(convId);
                    const lastSeen = getLastSeen(convId);

                    const fresh = items
                        .map(m => ({
                            ...m,
                            id: Number(m.id)
                        }))
                        .filter(m =>
                            Number.isFinite(m.id) &&
                            m.id > lastSeen &&
                            !seen.has(m.id) &&
                            Number(m.user_id) !== Number(window.AUTH_ID)
                        )
                        .sort((a, b) => a.id - b.id);

                    for (const m of fresh) {

                        if (Number(m.meet) === 1) {
                            // 🔔 Add your alert / console here
                            // console.log("Meeting detected:", m);
                            // alert("A meeting has been triggered in this conversation!");

                            // Existing UI trigger
                            //triggerMeetingUI();
                            const meetBtn = document.getElementById('meeting-btn');
                            const joinBtn = document.getElementById('joinBtnLate');

                            meetBtn.classList.add('hidden');
                            joinBtn.classList.remove('hidden');
                        }

                        if (typeof addIncomingWithTyping === 'function') {
                            addIncomingWithTyping({
                                text: m.body || '',
                                type: m.type || '',
                                avatar: m.user?.profile_photo_path ?
                                    `/storage/${m.user.profile_photo_path}` : '/user.png',
                                name: m.user?.name || window.ACTIVE_CONV_ID
                            });
                        }
                        setLastSeen(convId, m.id);
                        seen.add(m.id);
                    }

                    const hidden = document.visibilityState === 'hidden';
                    scheduleNext(jitter(hidden ? HIDDEN_MIN_MS : POLL_MIN_MS, hidden ? HIDDEN_MAX_MS :
                        POLL_MAX_MS));
                })
                .always(function() {
                    msgInFlight = false;
                });
        }

        scheduleNext(200);
    }

    function stopMessagePolling() {
        if (msgPollTimer) clearTimeout(msgPollTimer);
        msgPollTimer = null;
        msgInFlight = false;
    }

    /** Open/activate a conversation: prime -> start polling */
    async function setActiveConversation(id) {
        window.ACTIVE_CONV_ID = id;
        getSeenSet(id); // ensure set exists
        await primeConversationPointer(id);
        startMessagePolling();
    }

    // Resume promptly when back to the tab
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            startMessagePolling();
            startSidebarPolling();
        }
    });

    /*********************
     * SIDEBAR poller
     * - Detects new heads for ANY conversation
     * - Updates preview/time/unread
     * - Moves updated convo to top of its section
     * - Soft pulse highlight
     *********************/
    function startSidebarPolling() {
        stopSidebarPolling();
        const scheduleNext = (ms) => {
            clearTimeout(sidebarPollTimer);
            sidebarPollTimer = setTimeout(pollSidebarOnce, ms);
        };

        function pollSidebarOnce() {
            if (sidebarInFlight) {
                scheduleNext(jitter(SIDEBAR_MIN_MS, SIDEBAR_MAX_MS));
                return;
            }
            sidebarInFlight = true;

            $.getJSON('/chats/conversations', function(res) {
                const convs = Array.isArray(res) ? res : (res.data || []);
                const dms = convs.filter(c => c.type === 'dm');
                const groups = convs.filter(c => c.type === 'group');


                // Update counts
                $('#dmCount').text(dms.length);
                $('#groupCount').text(groups.length);

                // Check each conversation for a newer head
                for (const c of convs) {
                    const latestMsg = (c.messages && c.messages.length) ? c.messages[0] : null;
                    const latestId = Number(latestMsg?.id || 0);
                    const latestUserId = Number(latestMsg?.user_id || 0);

                    const head = headByConv[c.id] || {
                        latestId: 0,
                        unread: 0
                    };
                    const changed = latestId > (head.latestId || 0);

                    // Keep head in memory
                    headByConv[c.id] = {
                        latestId,
                        unread: Number(c.unread_count || 0)
                    };

                    // Initial render (first run), or changed head (and not my own message)
                    const listId = (c.type === 'group') ? '#groupList' : '#dmList';
                    const $list = $(listId);
                    const selector = `a.conv[data-id="${c.id}"]`;
                    const exists = $list.find(selector).length > 0;

                    if (!exists) {
                        // If not present (e.g., new convo or first load), append according to section sort
                        upsertConversationRow(c, {
                            toTop: true,
                            pulse: false
                        });
                        continue;
                    }

                    // If the latest message is new and not authored by me -> update row, move to top, pulse
                    if (changed && latestId && latestUserId !== Number(window.AUTH_ID)) {
                        upsertConversationRow(c, {
                            toTop: true,
                            pulse: true
                        });
                    } else {
                        // If unread count changed (e.g., marked read), quietly refresh row to reflect badge/time
                        const prevUnread = head.unread;
                        if (Number(prevUnread) !== Number(c.unread_count || 0)) {
                            upsertConversationRow(c, {
                                toTop: false,
                                pulse: false,
                                soft: true
                            });
                        }
                    }
                }

                // Optional: reorder lists fully by latestId desc
                resortListByLatest('#dmList');
                resortListByLatest('#groupList');

            }).always(function() {
                sidebarInFlight = false;
                const hidden = document.visibilityState === 'hidden';
                scheduleNext(jitter(hidden ? HIDDEN_MIN_MS : SIDEBAR_MIN_MS, hidden ? HIDDEN_MAX_MS :
                    SIDEBAR_MAX_MS));
            });
        }

        scheduleNext(400);
    }

    function stopSidebarPolling() {
        if (sidebarPollTimer) clearTimeout(sidebarPollTimer);
        sidebarPollTimer = null;
        sidebarInFlight = false;
    }

    // Reorder anchors inside list by data-latest-id desc
    function resortListByLatest(listSel) {
        const $list = $(listSel);
        const $items = $list.children('a.conv').get();
        $items.sort((a, b) => Number($(b).data('latestId') || 0) - Number($(a).data('latestId') || 0));
        $list.append($items);
    }

    /*********************************
     * SIDEBAR: fetch + initial render
     *********************************/
    (function($) {
        window.fetchConversations = fetchConversations;

        $(function() {
            fetchConversations();
            startSidebarPolling(); // begin real-time sidebar updates
        });

        function removeSpecialCharacter(str) {
            if (!str) return '';
            // replace anything not a-z, A-Z, 0-9, underscore, or space with a space
            return str.replace(/[^a-zA-Z0-9_ ]/g, ' ');
        }

        // Open conversation (single handler)
        $(document).on('click', 'a.conv', async function(e) {
            e.preventDefault();
            const id = $(this).data('id');

            if (!id) return;

            // set active, re-render to highlight
            window.ACTIVE_CONV_ID = id;
            fetchConversations();

            // notify right pane
            window.dispatchEvent(new CustomEvent('open-conversation', {
                detail: {
                    id
                }
            }));

            $.post({
                    url: `/chats/conversations/${id}/info`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                    }
                })
                .done(function(res) {
                    // ✅ handle success response here
                    const key = 'ZAVPsOPpa5GIb4Cb7EizxHXAlv7YduOqfYqeHIZD';
                    const safeName = res.conv.name === null ? key + res.conv.id :
                        removeSpecialCharacter(res.conv.name) + ' Meeting ' + key + res.conv.id;
                    const link = safeName;

                    window.ACTIVE_CONVERSATION = link;

                    document.getElementById('joinBtnLate').dataset.room = window
                        .ACTIVE_CONVERSATION;
                })
                .fail(function(xhr) {
                    // ❌ handle error
                    console.error("Error fetching info:", xhr.responseText);
                });


            // mark as read, then refresh lists
            $.post({
                url: `/chats/conversations/${id}/read`,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                }
            }).always(fetchConversations);

            // ensure only-new incoming rendering
            await setActiveConversation(id);
        });

        function fetchConversations() {
            $.getJSON('/chats/conversations', function(res) {
                const convs = Array.isArray(res) ? res : (res.data || []);
                const filter = ($('#sidebarSearch').val() || '').toLowerCase();

                const dmsAll = convs.filter(c => c.type === 'dm');
                const groupsAll = convs.filter(c => c.type === 'group');

                const dms = dmsAll.filter(c => matchesFilter(c, filter));
                const groups = groupsAll.filter(c => matchesFilter(c, filter));

                // Remember heads for diffing in the sidebar poller
                for (const c of convs) {
                    const latestMsg = (c.messages && c.messages.length) ? c.messages[0] : null;
                    headByConv[c.id] = {
                        latestId: Number(latestMsg?.id || 0),
                        unread: Number(c.unread_count || 0)
                    };
                }

                // Render sections
                $('#dmCount').text(dms.length);
                $('#groupCount').text(groups.length);

                $('#dmList').html(dms.map(templateConversation).join('') || emptyRow(
                    'No direct messages yet.'));
                $('#groupList').html(groups.map(templateConversation).join('') || emptyRow(
                    'No groups yet.'));

                // Initial sort by latest desc
                resortListByLatest('#dmList');
                resortListByLatest('#groupList');
            });
        }

        function matchesFilter(c, filter) {
            if (!filter) return true;
            const title = c.type === 'group' ? (c.name || 'Group') : dmPeerName(c);
            return (title || '').toLowerCase().includes(filter);
        }

        function emptyRow(txt) {
            return `<div class="text-gray-500 text-xs px-3 py-2">${txt}</div>`;
        }

        // Strip HTML → plain text
        function htmlToPlain(input) {
            if (!input) return '';
            const doc = new DOMParser().parseFromString(String(input), 'text/html');
            let text = (doc.body.textContent || '').replace(/\u00A0/g, ' ');
            return text.replace(/\s+/g, ' ').trim();
        }

        function safeTruncate(str, max = 140) {
            if (!str) return '';
            if (str.length <= max) return str;
            const cut = str.slice(0, max);
            const lastSpace = cut.lastIndexOf(' ');
            return (lastSpace > 40 ? cut.slice(0, lastSpace) : cut) + '…';
        }

        // Build conversation row (anchor)
        function templateConversation(c) {
            const latestMsg = (c.messages && c.messages.length) ? c.messages[0] : null;
            const latestId = Number(latestMsg?.id || 0);

            const hasMeeting = Number(latestMsg?.meet) === 1 || Boolean(c.active_meeting);

            const isActive = String(c.id) === String(window.ACTIVE_CONV_ID);
            const pinnedClass = isActive ?
                'bg-blue-100 ring-1 ring-inset ring-blue-100 hover:bg-blue-100/60' :
                (c.is_pinned ? 'bg-blue-100 ring-1 ring-inset ring-blue-100 hover:bg-blue-100/60' :
                    'hover:bg-gray-100');

            const plainBody = htmlToPlain(latestMsg?.body || '');

            let preview;
            if (!plainBody && Array.isArray(latestMsg?.attachments) && latestMsg.attachments.length) {
                const first = latestMsg.attachments[0] || {};
                const mime = (first.mime || '').toLowerCase();
                let label = 'Attachment';
                if (mime.startsWith('image/')) label = 'Photo';
                else if (mime.startsWith('video/')) label = 'Video';
                else if (mime.includes('pdf')) label = 'PDF';
                else if (mime.includes('zip')) label = 'Archive';
                preview = label + (latestMsg.attachments.length > 1 ? ` +${latestMsg.attachments.length - 1}` : '');
            } else {
                preview = safeTruncate(plainBody, 140) || 'No messages yet';
            }

            const time = latestMsg?.created_at ? formatTime(latestMsg.created_at) : '';
            const unreadDot = (c.unread_count && c.unread_count > 0) ?
                `<span class="ml-auto flex h-2.5 w-2.5"><span class="absolute inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span></span>` :
                '';

            const title = c.type === 'group' ? escapeHtml(c.name || 'Group') : escapeHtml(dmPeerName(c));
            const badge = c.type === 'group' ?
                `<span class="ml-2 text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 align-middle">Group</span>` :
                '';
            const avatar = c.type === 'group' ? groupStack(c) : dmAvatar(c);

            const meetingDot = hasMeeting ?
                `<span class="ml-2 meeting-dot" title="Meeting in progress" aria-label="Meeting in progress"></span>` :
                '';

            // data-latest-id is used for resorting
            return `
            <a href="#" data-id="${c.id}" data-latest-id="${latestId}" class="conv block py-2 px-3 rounded-lg ${pinnedClass}">
                <div class="flex items-center gap-3">
                ${avatar}
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-3">
                    <span class="font-semibold text-gray-900 truncate">${title}${badge}</span>
                    <span class="text-xs ${c.is_pinned ? 'font-semibold text-gray-900' : 'text-gray-500'} whitespace-nowrap">${time}</span>
                    </div>
                    <p class="text-xs text-gray-600 truncate">${preview}</p>
                </div>
                ${unreadDot}
                </div>
            </a>
            `;
        }

        // Insert or replace row; optionally move to top & pulse
        function upsertConversationRow(c, {
            toTop = false,
            pulse = false,
            soft = false
        } = {}) {
            const listSel = (c.type === 'group') ? '#groupList' : '#dmList';
            const $list = $(listSel);
            const sel = `a.conv[data-id="${c.id}"]`;
            const html = templateConversation(c);
            const latestMsg = (c.messages && c.messages.length) ? c.messages[0] : null;
            const latestId = Number(latestMsg?.id || 0);

            if ($list.find(sel).length) {
                const $row = $list.find(sel);
                $row.replaceWith(html);
            } else {
                // Insert
                if (toTop) $list.prepend(html);
                else $list.append(html);
            }

            // Move to top if requested
            if (toTop) {
                const $row = $list.find(sel);
                $row.attr('data-latest-id', latestId);
                $row.prependTo($list);
            }

            // Pulse highlight for noticeable new content
            if (pulse) {
                const $row = $list.find(sel);
                $row.addClass('row-pulse');
                setTimeout(() => $row.removeClass('row-pulse'), 1300);
            }
        }

        function dmPeerName(c) {
            const peer = (c.participants || []).find(p => Number(p.user_id) !== Number(window.AUTH_ID));
            return peer?.user?.name || 'Direct Message';
        }

        function dmAvatar(c) {
            const peer = (c.participants || []).find(p => Number(p.user_id) !== Number(window.AUTH_ID));
            const src = peer?.user?.profile_photo_path ? (`/storage/${peer.user.profile_photo_path}`) :
                '/user.png';
            return `<img src="${src}" class="h-9 w-9 rounded-full object-cover" onerror="this.src='/user.png'">`;
        }

        // function groupStack(c){
        //     const imgs = (c.participants || []).slice(0,3).map(p => {
        //         const src = p.user?.profile_photo_path ? (`/storage/${p.user.profile_photo_path}`) : '/user.png';
        //         return `<img src="${src}" onerror="this.src='/user.png'">`;
        //     }).join('');
        //     return `<div class="stack">${imgs}</div>`;
        // }

        // function groupStack(c) {
        //     const p = (c.participants || [])[0]; // take only the first participant
        //     let avatar;

        //     if (p?.user?.profile_photo_path) {
        //         // use profile photo
        //         avatar = `<img class="w-8 h-8 rounded-full object-cover" 
        //               src="/storage/${p.user.profile_photo_path}" 
        //               onerror="this.src='/user.png'">`;
        //     } else {
        //         // fallback: first letter of name
        //         const initial = (p?.user?.name || '?').charAt(0).toUpperCase();
        //         avatar = `<div class="w-8 h-8 flex items-center justify-center 
        //                     rounded-full bg-gray-300 text-gray-700 font-semibold">
        //              ${initial}
        //           </div>`;
        //     }

        //     return `<div class="stack">${avatar}</div>`;
        // }

        function groupStack(c) {
            const p = (c.participants || [])[0]; // first participant only
            const initial = (p?.user?.name || '?').charAt(0).toUpperCase();

            return `
                <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-300 text-gray-700 font-semibold">
                   GC
                </div>
            `;
        }


        function escapeHtml(str) {
            return (str || '').replace(/[&<>"']/g, s => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [s]));
        }

        function formatTime(iso) {
            try {
                const d = new Date(iso),
                    n = new Date();
                const sameDay = d.toDateString() === n.toDateString();
                const tOpt = {
                    hour: 'numeric',
                    minute: '2-digit'
                };
                const dOpt = {
                    month: 'short',
                    day: 'numeric'
                };
                return sameDay ? d.toLocaleTimeString([], tOpt) :
                    d.toLocaleDateString([], dOpt) + ' · ' + d.toLocaleTimeString([], tOpt);
            } catch (e) {
                return '';
            }
        }

        // Client-side filter
        $('#sidebarSearch').on('input', fetchConversations);

        // Expose helper so sidebar poller can use it
        window.upsertConversationRow = upsertConversationRow;

    })(jQuery);

    // Kick off sidebar poller on load
    startSidebarPolling();
</script>
