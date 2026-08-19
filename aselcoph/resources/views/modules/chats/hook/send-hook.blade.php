
<style>
    /* Centered, neutral system notice bubble */
    .bubble.system{
        background:#f9fafb;     /* very light gray */
        color:#374151;           /* gray-700 */
        border-radius:9999px;    /* pill */
        padding:8px 12px;
        display:inline-block;
    }
    .system-row{
        display:flex;
        justify-content:center;
        margin:10px 0;
    }
</style>


<script>
    // ===== Rich-text helpers (GLOBAL) =====
    window.sanitizeAllowedHtml = function (html) {
        const allowed = new Set(['b','strong','i','em','u','code','pre','ul','ol','li','br','p','span','a']);
        const doc = new DOMParser().parseFromString(html || '', 'text/html');

        const walk = (node) => {
            if (node.nodeType !== Node.ELEMENT_NODE) return;
            const tag = node.tagName.toLowerCase();

            if (!allowed.has(tag)) {
                node.replaceWith(...[...node.childNodes]);
                return;
            }

            // Remove all attributes by default…
            [...node.attributes].forEach(a => {
                // …but keep safe ones for <a> and <ol start>
                if (tag === 'a' && a.name === 'href') return;
                if (tag === 'ol' && a.name === 'start' && /^\d+$/.test(a.value)) return;
                node.removeAttribute(a.name);
            });

            if (tag === 'a') {
                const href = node.getAttribute('href') || node.textContent || '';
                try {
                    const u = new URL(href, location.origin);
                    if (u.protocol === 'http:' || u.protocol === 'https:') {
                        node.setAttribute('href', u.href);
                        node.setAttribute('rel', 'noopener noreferrer');
                        node.setAttribute('target', '_blank');
                    } else {
                        node.removeAttribute('href');
                    }
                } catch { node.removeAttribute('href'); }
            }

            [...node.childNodes].forEach(walk);
        };

        [...doc.body.childNodes].forEach(walk);
        return doc.body.innerHTML.trim();
    };

    window.looksLikeHtml = function (s) {
        return /<\/?[a-z][\s\S]*>/i.test(s || '');
    };

    window.linkify = function (text) {
        if (!text) return '';
        const withBreaks = String(text).replace(/\n/g, '<br>');
        return withBreaks.replace(/((https?:\/\/|www\.)[^\s<]+)/gi, (m) => {
            const url = m.startsWith('http') ? m : `https://${m}`;
            return `<a href="${url}" target="_blank" rel="noopener noreferrer">${m}</a>`;
        });
    };

    function checkMeetStatus(meet) {
        if (meet === 1) {
            // Show the call header
            document.getElementById('header-call').classList.add('show');

            // Add the pulse effect to the target element
            const pulseTarget = document.getElementById('pulse-indicator');
            if (pulseTarget) {
                pulseTarget.classList.add('pulse-red-light');
            }
        }
    }

</script>

<script>
    const API_BASE = '/chats'; // <-- change to '/chats' if your routes are under /chats'

    $(function(){

        // --- SYSTEM SEND (no attachments) ---
        $('#meeting-btn').on('click', async function(){
            if (sending) return;
            if (!window.CURRENT_CONV) { toast('Select a conversation first.'); return; }

            const rawHtml = $('#editor').html();
            const source  = looksLikeHtml(rawHtml) ? rawHtml : linkify(rawHtml);
            const bodyHtml = sanitizeAllowedHtml(source);

            const $meetingBtn  = $('#meeting-btn');
            const $meetingStarted  = $('#meeting-started');

            $meetingBtn[0]?.classList.add('pulse-red-light');
            $meetingStarted[0]?.classList.add('pulse-red-light');
            $meetingStarted[0]?.classList.remove('hidden');

            sending = true; toggleSending(true);
            try {
                const form = new FormData();
                form.append('type', 'system');
                form.append('status', 'system');
                form.append('is_system', '1');

                // 🔴 Mark this message as a meeting trigger
                form.append('meet', '1');

                form.append('body', bodyHtml || '{{ Auth::user()->name }} Started the Meeting');
                form.append('is_html', '1');

                // 1) Create the system message (with meet=1)
                const res = await $.ajax({
                    url: `${API_BASE}/conversations/${encodeURIComponent(CURRENT_CONV)}/messages`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: form,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                });

                // 2) Also update the conversation's meet flag (for UIs that read it from convo)
                try {
                    await $.ajax({
                        url: `${API_BASE}/conversations/${encodeURIComponent(CURRENT_CONV)}`,
                        method: 'PATCH', // or 'POST' if your route uses POST
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        data: { meet: 1 } // backend should persist this to the conversation record
                    });
                } catch (e) {
                    console.warn('Conversation meet=1 update failed (non-fatal):', e?.status, e?.responseText);
                }

                // Clear editor
                $('#editor').empty();

                // Render the system message locally (optional; poller will catch it anyway)
                const justSent = { ...res, body_html: bodyHtml, type:'system', status:'system', is_system:1, meet:1 };
                appendMessage(justSent);

                // Ensure “No Message Yet” removed & scroll
                $('#chat-messages').find('.no-msg-placeholder').remove();
                scrollChatToBottom();

                // Refresh lists so the meeting dot can appear immediately
                if (typeof window.fetchConversations === 'function') window.fetchConversations();

            } catch (xhr) {
                console.warn('system send failed', xhr.status, xhr.responseText);
                toast('Failed to send system message.');
            } finally {
                sending = false; toggleSending(false);
            }
        });

        $('#btn-end-meet').on('click', async function(){
            if (!window.CURRENT_CONV) { toast('Select a conversation first.'); return; }

            try {
                await $.ajax({
                    url: `${API_BASE}/conversations/${encodeURIComponent(CURRENT_CONV)}`,
                    method: 'PATCH',                      // ← use PATCH
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: JSON.stringify({ meet: null }), // send real null
                    processData: false,
                    contentType: 'application/json'
                });

                toast('Meeting ended.');

                // Immediate UI clean-up (optional)
                const header = document.getElementById('header-call');
                if (header) header.classList.remove('show');
                const pulseTarget = document.getElementById('pulse-indicator');
                if (pulseTarget) pulseTarget.classList.remove('meeting-dot');

                if (typeof window.fetchConversations === 'function') window.fetchConversations();

            } catch (xhr){
                console.warn('end meet failed', xhr.status, xhr.responseText);
                toast('Failed to end meeting.');
            }
        });



        // --- DOM refs
        const $editor   = $('#editor');
        const $send     = $('#btn-send');
        const $clear    = $('#btn-clear');
        const $attPrev  = $('#pending-preview');
        const $fileIn   = $('#file-input');
        const $imgIn    = $('#img-input');

        // attachment buffers
        let pendingFiles  = [];   // File objects (generic + images)
        let sending       = false;

        // --- Buttons
        $('#btn-attach').on('click', ()=> $fileIn.trigger('click'));
        $('#btn-image').on('click',  ()=> $imgIn.trigger('click'));

        $fileIn.on('change', function(){ addFiles([...this.files]); this.value=''; });
        $imgIn.on('change',  function(){ addFiles([...this.files]); this.value=''; });

        // Paste images (keeps rich text/code if no files present)
        $editor.on('paste', function(e){
            const items = (e.originalEvent.clipboardData || {}).items || [];
            const files = [];
            for (const it of items) if (it.kind === 'file') files.push(it.getAsFile());
            if (files.length) { e.preventDefault(); addFiles(files); }
        });

        // Drag & drop
        // Drag over: only prevent default if actual files are being dragged
        $editor.on('dragover', function (e) {
            const dt = e.originalEvent.dataTransfer;
            const hasFiles = !!dt && Array.isArray(dt.types) && dt.types.includes('Files');
            if (hasFiles) {
                e.preventDefault();                // allow dropping files to attach
                try { dt.dropEffect = 'copy'; } catch {}
            }
            // else: don't preventDefault so text/HTML (incl. code) can be dropped normally
        });

        // Drop: only intercept when files exist; otherwise let text/HTML drop through
        $editor.on('drop', function (e) {
            const dt = e.originalEvent.dataTransfer;
            const files = [...(dt?.files || [])];
            if (files.length) {
                e.preventDefault();                // stop browser from inserting the file path
                addFiles(files);                   // attach files/images
                return;
            }
            // else: no preventDefault → keep original formatting (lists, bold, CODE, etc.)
        });

        // Render preview chips
        function refreshPreview(){
            if (!pendingFiles.length) { $attPrev.addClass('hidden').empty(); return; }
            const html = pendingFiles.map((f,i)=>`
            <div class="inline-flex items-center gap-2 px-2 py-1 border rounded-md me-2 mb-2">
              <i class="bi ${f.type.startsWith('image/')?'bi-image':'bi-paperclip'}"></i>
              <span class="text-xs max-w-48 truncate">${escapeHtml(f.name)} (${Math.ceil(f.size/1024)} KB)</span>
              <button type="button" class="text-red-600 hover:text-red-700" data-remove="${i}" title="Remove">
                <i class="bi bi-x"></i>
              </button>
            </div>
          `).join('');
            $attPrev.removeClass('hidden').html(html);
        }
        $attPrev.on('click', '[data-remove]', function(){
            const i = +$(this).data('remove');
            pendingFiles.splice(i,1);
            refreshPreview();
        });

        function addFiles(files){
            for (const f of files) if (f && f.size) pendingFiles.push(f);
            refreshPreview();
        }

        // Enter to send (Shift+Enter = newline)
        $editor.on('keydown', function(e){
            if (e.key === 'Enter' && !e.shiftKey){
                e.preventDefault();
                $send.trigger('click');
            }
        });

        // Clear
        $clear.on('click', function(){
            if (sending) return;
            $editor.empty();
            pendingFiles = [];
            refreshPreview();
        });

        function isEmptyHtml(html) {
            if (!html) return true;
            const div = document.createElement('div');
            div.innerHTML = html;
            // remove <br> and &nbsp; to test real emptiness
            div.querySelectorAll('br').forEach(br => br.remove());
            const text = (div.textContent || '').replace(/\u00A0/g, ' ').trim();
            return text.length === 0;
        }

        // SEND
        $send.on('click', async function(){
            if (sending) return;
            if (!window.CURRENT_CONV) { toast('Select a conversation first.'); return; }

            // Grab composer HTML
            const rawHtml = $editor.html();

            // If the editor has only plain text (no tags), linkify it first; otherwise keep HTML
            const source = looksLikeHtml(rawHtml) ? rawHtml : linkify(rawHtml);

            // Sanitize allow-listed tags/attrs
            const bodyHtml = sanitizeAllowedHtml(source);

            // Treat blank/whitespace-only as empty
            const bodyIsEmpty = isEmptyHtml(bodyHtml);

            if (bodyIsEmpty && pendingFiles.length === 0) return;

            sending = true; toggleSending(true);

            try {
                const form = new FormData();
                form.append('type', 'text');          // backend can infer too
                form.append('body', bodyIsEmpty ? '' : bodyHtml);
                // Optional: tell backend this is sanitized HTML
                form.append('is_html', '1');

                pendingFiles.forEach((f,idx)=> form.append('attachments[]', f, f.name));

                const res = await $.ajax({
                    url: `${API_BASE}/conversations/${encodeURIComponent(CURRENT_CONV)}/messages`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: form,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                });

                // success → clear composer & previews
                $editor.empty();
                pendingFiles = [];
                refreshPreview();

                // after POST succeeds, render the same sanitized HTML we sent
                const justSent = {
                    ...res,
                    body_html: bodyHtml
                };

                // append to chat
                appendMessage(justSent);

                // ensure "No Message Yet" placeholder is gone
                const $cm = $('#chat-messages');
                $cm.find('.no-msg-placeholder').remove();

                // scroll to bottom
                scrollChatToBottom();

                // refresh sidebar preview/unreads if available
                if (typeof window.fetchConversations === 'function') window.fetchConversations();

            } catch (xhr) {
                console.warn('send failed', xhr.status, xhr.responseText);
                toast('Failed to send. Please try again.');
            } finally {
                sending = false; toggleSending(false);
            }
        });

        function toggleSending(on){
            $send.prop('disabled', on);
            $clear.prop('disabled', on);
        }

        function appendMessage(m){
            // minimal safe render compatible with your bubble layout
            const me   = m.user_id === window.AUTH_ID;
            const user = m.user || {};
            const name = user.name || (me?'You':'User');
            const ava  = user.profile_photo_path ? `/storage/${user.profile_photo_path}` : '/user.png';
            const time = toTime(m.created_at);

            // text body
            const text = m.body_html ??
                window.sanitizeAllowedHtml(
                    window.looksLikeHtml(m.body) ? (m.body || '') : window.linkify(m.body || '')
                );

            // System message rendering (centered pill)
            const isSystem = m.is_system == 1 || m.type === 'system' || m.status === 'system';
            if (isSystem){
                const sysHtml = m.body_html ??
                    window.sanitizeAllowedHtml(
                        window.looksLikeHtml(m.body) ? (m.body || '') : window.linkify(m.body || '')
                    );

               // triggerMeetingUI();
                // Caller

                const t = toTime(m.created_at);
                // $('#chat-messages').append(`
                //     <div class="system-row">
                //       <div class="bubble system">
                //         ${sysHtml || 'System notice'}
                //       </div>
                //     </div>
                //     <div class="system-row">
                //       <span class="text-[11px] text-gray-400">${t}</span>
                //     </div>
                //   `);
                return; // stop; don't render in regular me/other layout
            }

            // attachments (if backend returns them)
            let atts = '';
            if (Array.isArray(m.attachments) && m.attachments.length){
                atts = `<div class="mt-2 space-y-1">` + m.attachments.map(a=>{

                    const url = a.url || a.path || '#';
                    const label = escapeHtml(a.name || a.filename || 'attachment');
                    if ((a.mime||'').startsWith('image/')){
                        return `<a href="${url}" target="_blank" class="block"><img src="${url}" class="max-w-[260px] rounded border"></a>`;
                    }
                    return `<a href="${url}" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 underline"><i class="bi bi-paperclip"></i>${label}</a>`;
                }).join('') + `</div>`;
            }

            const actions = (isMe) => `
        <div class="hs-dropdown relative inline-flex ${isMe?'[--trigger:hover]':''}">
          <button type="button" class="hs-dropdown-toggle ml-1 p-1.5 rounded-md ${isMe?'hover:bg-blue-600/10 text-white/80':'hover:bg-gray-100 text-gray-500'}">
            <i class="bi bi-three-dots"></i>
          </button>
          <div class="hs-dropdown-menu z-50 hidden transition-[opacity,margin] duration-150 hs-dropdown-open:opacity-100 opacity-0 min-w-36 bg-white shadow-md rounded-lg p-1 border border-gray-200 ${isMe?'right-0':''}">
            <button class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-50 text-sm flex items-center gap-2"><i class="bi bi-reply"></i> Reply</button>
            <button class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-50 text-sm flex items-center gap-2"><i class="bi bi-forward"></i> Forward</button>
            <button class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-50 text-sm flex items-center gap-2"><i class="bi bi-pencil"></i> Edit</button>
            <button class="w-full text-left px-3 py-2 rounded-md hover:bg-red-50 text-sm text-red-600 flex items-center gap-2"><i class="bi bi-trash"></i> Delete</button>
          </div>
        </div>
      `;

            const htmlMe = `
        <div class="pl-16 pr-12">
          <div class="flex items-end gap-3 justify-end">
            <div class="min-w-0">
              <div class="bubble me">${text}${atts}</div>
              <div class="mt-1 flex items-center gap-2 justify-end">
                <span class="stamp">${time}</span>
                ${actions(true)}
              </div>
            </div>
            <img src="${ava}" class="w-8 h-8 rounded-full object-cover -mb-1" onerror="this.src='/user.png'">
          </div>
        </div>`;

            const htmlOther = `
        <div class="pl-12 pr-16">
          <div class="flex items-end gap-3">
            <img src="${ava}" class="w-8 h-8 rounded-full object-cover -mb-1" onerror="this.src='/user.png'">
            <div class="min-w-0">
              ${m.conversation?.type === 'group' ? `<div class="text-xs font-semibold text-gray-700 mb-1">${escapeHtml(name)}</div>` : ''}
              <div class="bubble ai">${text}${atts}</div>
              <div class="mt-1 flex items-center gap-2">
                <span class="stamp">${time}</span>
                ${actions(false)}
              </div>
            </div>
          </div>
        </div>`;

            $('#chat-messages').append(me ? htmlMe : htmlOther);
        }

        // utils
        function sanitizeToText(html){ const d=document.createElement('div'); d.innerHTML=html; return (d.textContent||'').replace(/\u00A0/g,' ').trim(); }
        function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c])); }
        function toTime(iso){ const d=new Date(iso); return d.toLocaleTimeString([], {hour:'numeric',minute:'2-digit'}); }
        function toast(msg){ console.log(msg); /* replace with your toast UI if any */ }
        // Keep allowed formatting tags, strip unsafe tags/attrs

    });
</script>
