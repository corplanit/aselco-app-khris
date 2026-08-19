<script>
    window.AUTH_ID = {{ auth()->id() }};
</script>

<script>
    (function($) {
        // let CURRENT_CONV = null;
        let nextPageUrl = null; // older page URL from paginator
        let loadingOlder = false;
        let convCache = []; // from /api/conversations to fill header fast

        // Optional: cache conversations on any refresh
        function cacheConversations() {
            return $.getJSON('/chats/conversations', function(res) {
                convCache = Array.isArray(res) ? res : (res.data || []);
            });
        }

        // Listen from sidebar
        window.addEventListener('open-conversation', (e) => {
            const id = e.detail?.id;
            if (!id) return;
            openConversation(id);
        });

        function renderMessageSkeletonRows(count = 6) {
            const rows = [];
            for (let i = 0; i < count; i++) {
                const right = i % 2; // alternate left/right

                if (right) {
                    // my message (right aligned)
                    rows.push(`
        <div class="pl-16 pr-12">
          <div class="flex items-end gap-3 justify-end">
            <div class="min-w-0 max-w-[70%] flex flex-col items-end">
              <div class="skeleton h-4 w-[50%] mb-2 rounded" style="width: 100px"></div>
              <div class="skeleton h-10 w-full rounded-xl" style="width: 200px"></div>
            </div>
            <div class="w-8 h-8 rounded-full bg-gray-200 skeleton"></div>
          </div>
        </div>
      `);
                } else {
                    // other user (left aligned)
                    rows.push(`
        <div class="pl-12 pr-16">
          <div class="flex items-end gap-3">
            <div class="w-8 h-8 rounded-full bg-gray-200 skeleton"></div>
            <div class="min-w-0 max-w-[70%]">
              <div class="skeleton h-4 w-[40%] mb-2 rounded" style="width: 100px"></div>
              <div class="skeleton h-10 w-full rounded-xl" style="width: 200px"></div>
            </div>
          </div>
        </div>
      `);
                }
            }
            return rows.join('');
        }

        function showMessageSkeleton() {
            const $cm = $('#chat-messages');
            if (!$cm.find('#msgs-skeleton').length) {
                $cm.html(`<div id="msgs-skeleton" class="space-y-4">${renderMessageSkeletonRows(6)}</div>`);
            }
        }

        function hideMessageSkeleton() {
            $('#msgs-skeleton').remove();
        }




        async function openConversation(id) {
            try {
                CURRENT_CONV = id;
                const $cm = $('#chat-messages');

                // reset & show skeleton
                $cm.empty();
                showMessageSkeleton();

                // refresh meta once (removed duplicate call)
                await cacheConversations();

                // header
                const meta = convCache.find(c => c.id === id);
                setHeader(meta);

                // mark read (fire-and-forget)
                $.ajax({
                    url: `/chats/conversations/${encodeURIComponent(id)}/read`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).fail(xhr => console.warn('mark-read failed', xhr.status, xhr.responseText));

                // load first page
                nextPageUrl = `/chats/conversations/${encodeURIComponent(id)}/messages`;
                const added = await loadMessages({
                    initial: true
                });

                // hide skeleton AFTER we rendered
                hideMessageSkeleton();

                // robust empty check (no bubbles rendered)
                const hasBubbles = $cm.find('.bubble').length > 0;
                if (!hasBubbles && added === 0) {
                    $cm.html(
                        `<div class="no-msg-placeholder text-center text-gray-400 text-sm py-6">No Message Yet</div>`
                    );
                } else {
                    // ensure any old placeholder is gone
                    $cm.find('.no-msg-placeholder').remove();
                }

                scrollChatToBottom();
            } catch (err) {
                console.error('openConversation error:', err);
                hideMessageSkeleton();
            }
        }


        // Header updates
        function formatTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();

            const sameDay =
                date.getDate() === now.getDate() &&
                date.getMonth() === now.getMonth() &&
                date.getFullYear() === now.getFullYear();

            const yesterday =
                date.getDate() === now.getDate() - 1 &&
                date.getMonth() === now.getMonth() &&
                date.getFullYear() === now.getFullYear();

            if (sameDay) {
                return `Today · ${date.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                })}`;
            } else if (yesterday) {
                return `Yesterday · ${date.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                })}`;
            } else {
                return `${date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                })} · ${date.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                })}`;
            }
        }

        function setHeader(conv) {
            const $title = $('.text-base.font-semibold.leading-tight').first();
            const $sub = $('.text-xs.text-gray-500.flex.items-center.gap-2').first();
            const $ava = $('header img.w-10.h-10').first();
            const $join = $('#joinBtn');

            const $header_opt = $('#header-action');
            const $meetingBtn = $('#meeting-btn');
            const $meetingStarted = $('#meeting-started');

            $header_opt[0]?.classList.remove('hidden');
            $meetingStarted[0]?.classList.add('hidden');
            $meetingBtn[0]?.classList.remove('pulse-red-light');
            $meetingStarted[0]?.classList.remove('pulse-red-light');


            const joinBtn = document.getElementById('joinBtnLate');
            const endBtn = document.getElementById('endBtn');
            const meetBtn = document.getElementById('meeting-btn');

            meetBtn.classList.remove('hidden');
            joinBtn.classList.add('hidden');
            endBtn.classList.add('hidden');


            if (conv.meet === 1) {
                $meetingBtn[0]?.classList.add('pulse-red-light');
                $meetingStarted[0]?.classList.add('pulse-red-light');
                $meetingStarted[0]?.classList.remove('hidden');
                joinBtn.classList.remove('hidden');
                meetBtn.classList.add('hidden');

                if (conv.created_by === window.AUTH_ID) {
                    endBtn.classList.remove('hidden');
                }

                console.log(conv.joined);

                if (conv.joined >= 0) {
                    document.getElementById('count_join').innerText = conv.joined;
                }
            }

            if (conv.joined <= 0) {
                const room = conv.id;
                document.getElementById('count_join').innerText = '0';
                $.post({
                        url: `/chats/conversations/${room}/meet/end`,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                        },
                        data: {
                            meet_join: room,
                            user_id: {{ Auth::user()->id }}
                        },
                    })
                    .done(function(res) {
                        $meetingBtn[0]?.classList.remove('pulse-red-light');
                        $meetingStarted[0]?.classList.remove('pulse-red-light');
                        $meetingStarted[0]?.classList.remove('add');
                        meetBtn.classList.remove('hidden');
                        joinBtn.classList.add('hidden');
                        endBtn.classList.add('hidden');
                    })
                    .fail(function(xhr) {
                        console.error("Error fetching info:", xhr.responseText);
                    });
            }


            if (!conv) {
                $title.text('Conversation');
                $sub.text('Loading…');
                $ava.attr('src', '/user.png');
                $join.attr('data-room', ''); // clear
                return;
            }


            if (conv.type === 'group') {
                $title.text(conv.name || 'Group');
                $ava.attr('src', '/group.png').on('error', function() {
                    this.src = '/user.png';
                });
            } else {
                const peer = (conv.participants || []).find(p => p.user_id !== window.AUTH_ID);
                const name = peer?.user?.name || 'Direct Message';
                const src = peer?.user?.profile_photo_path ? `/storage/${peer.user.profile_photo_path}` :
                    '/user.png';
                $title.text(name);
                $ava.attr('src', src).on('error', function() {
                    this.src = '/user.png';
                });
            }

            const last = (conv.messages && conv.messages[0]) ? conv.messages[0] : null;
            const when = last?.created_at ? formatTime(last.created_at) : '—';
            $sub.html(`
            <span class="inline-flex items-center gap-1">
                <span class="w-2 h-2 bg-green-500 rounded-full"></span> Online
            </span>
            <span>•</span>
            <span>Last message ${when}</span>
            `);

            // 🔹 Change data-room dynamically
            // If you need encryption here, you'll have to request it from the backend
            $join.attr('data-room', conv.room || conv.id || '');
        }
        


        // Load (older) messages with day separators
        async function loadMessages({
            initial = false
        } = {}) {
            if (!nextPageUrl || loadingOlder) return 0;
            loadingOlder = true;

            try {
                const page = await $.getJSON(nextPageUrl);
                const raw = (page.data || []);

                const items = raw.map(m => {
                    const src = looksLikeHtml(m.body) ? (m.body || '') : linkify(m.body || '');
                    return {
                        ...m,
                        body_html: sanitizeAllowedHtml(src)
                    };
                }).slice().reverse();

                nextPageUrl = page.next_page_url || null;

                // Render
                prependBatch(items, {
                    showHeaderNames: isGroup(convCache.find(c => c.id === CURRENT_CONV))
                });

                return items.length; // ⬅️ tell caller how many we added
            } finally {
                loadingOlder = false;
            }
        }


        function isGroup(conv) {
            return conv && conv.type === 'group';
        }

        // Build & prepend a batch with date separators
        function prependBatch(messages, {
            showHeaderNames = false
        } = {}) {
            const $wrap = $('#chat-messages');
            if (messages.length > 0) $wrap.find('.no-msg-placeholder').remove(); // ⬅️

            const scroller = $wrap[0];
            const prevHeight = scroller.scrollHeight;
            const prevTop = scroller.scrollTop;

            let html = '';
            let lastDateKey = $wrap.data('last-top-date');

            messages.forEach(m => {
                const dateKey = dayKey(m.created_at);
                if (dateKey !== lastDateKey) {
                    html += daySeparator(dateKey);
                    lastDateKey = dateKey;
                }
                html += renderMessage(m, {
                    showName: showHeaderNames
                });
            });

            $wrap.prepend(html);
            $wrap.data('last-top-date', lastDateKey);

            const newHeight = scroller.scrollHeight;
            scroller.scrollTop = newHeight - prevHeight + prevTop;
        }

        // Render a single message to your bubble structure
        function renderMessage(m, {
            showName = false
        } = {}) {
            const me = m.user_id === window.AUTH_ID;
            const user = m.user || {};
            const name = user.name || (me ? 'You' : 'User');
            const ava = user.profile_photo_path ? `/storage/${user.profile_photo_path}` : '/user.png';
            const time = toTime(m.created_at);

            const body = m.body_html ??
                window.sanitizeAllowedHtml(
                    window.looksLikeHtml(m.body) ? (m.body || '') : window.linkify(m.body || '')
                );

            let atts = '';
            if (Array.isArray(m.attachments) && m.attachments.length) {
                atts = `<div class="mt-2 space-y-1">` + m.attachments.map(a => {
                    const url = a.url || a.path || '#';
                    const label = escapeHtml(a.name || a.filename || 'attachment');
                    const mime = (a.mime || '').toLowerCase();
                    if (mime.startsWith('image/')) {
                        return `<a href="${url}" target="_blank" class="block">
                          <img src="${url}" class="max-w-[200px] rounded-md border border-gray-100" onerror="this.closest('a').remove()">
                        </a>`;
                    }
                    return `<a href="${url}" target="_blank" class="inline-flex items-center gap-2 text-sm">
                      <i class="bi bi-paperclip"></i>${label}
                    </a>`;
                }).join('') + `</div>`;
            }

            // ✅ New branch for system messages
            if (m.type === 'system') {
                return `
                <div class="flex justify-center my-4">
                    <div class="bg-gray-100 text-gray-500 text-xs px-3 py-1 rounded-lg italic">
                        ${escapeHtml(m.body || 'System notice')}${atts} at ${time}
                    </div>
                </div>`;
            }

            // ✅ Outgoing (me)
            if (me) {
                return `
                <div class="pl-16 pr-12 msg-pop">
                    <div class="flex items-end gap-3 justify-end">
                    <div class="min-w-0">
                        ${showName ? `<div class="text-xs font-semibold text-gray-700 mb-1 text-right">${name}</div>` : ''}
                        <div class="bubble me">${body}${atts}</div>
                        <div class="mt-1 flex items-center gap-2 justify-end">
                        <span class="stamp">${time}</span>
                        <div class="hs-dropdown relative inline-flex [--trigger:hover]">
                            <button type="button" class="hs-dropdown-toggle ml-1 p-1.5 rounded-md hover:bg-blue-600/10 text-white/80">
                            <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                            </button>
                            <div class="hs-dropdown-menu z-50 hidden transition-[opacity,margin] duration-150 hs-dropdown-open:opacity-100 opacity-0 min-w-36 bg-white shadow-md rounded-lg p-1 border border-gray-200 right-0">
                            ${actionItems()}
                            </div>
                        </div>
                        </div>
                    </div>
                    <img src="${ava}" class="w-8 h-8 rounded-full object-cover -mb-1" alt="${escapeHtml(name)}" onerror="this.src='/user.png'">
                    </div>
                </div>`;
            }

            // ✅ Incoming (others)
            return `
            <div class="pl-12 pr-16 msg-pop">
                <div class="flex items-end gap-3">
                <img src="${ava}" class="w-8 h-8 rounded-full object-cover -mb-1" alt="${escapeHtml(name)}" onerror="this.src='/user.png'">
                <div class="min-w-0">
                    ${showName ? `<div class="text-xs font-semibold text-gray-700 mb-1">${name}</div>` : ''}
                    <div class="bubble ai">${body}${atts}</div>
                    <div class="mt-1 flex items-center gap-2">
                    <span class="stamp">${time}</span>
                    <div class="hs-dropdown relative inline-flex">
                        <button type="button" class="hs-dropdown-toggle ml-1 p-1.5 rounded-md hover:bg-gray-100 text-gray-500">
                        <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                        </button>
                        <div class="hs-dropdown-menu z-50 hidden transition-[opacity,margin] duration-150 hs-dropdown-open:opacity-100 opacity-0 min-w-36 bg-white shadow-md rounded-lg p-1 border border-gray-200">
                        ${actionItems()}
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>`;
        }


        function actionItems() {
            return `
   <button class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-50 text-sm flex items-center gap-2"><i data-lucide="reply" class="w-4 h-4"></i> Reply</button>
   <button class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-50 text-sm flex items-center gap-2"><i data-lucide="forward" class="w-4 h-4"></i> Forward</button>
   <button class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-50 text-sm flex items-center gap-2"><i data-lucide="pencil" class="w-4 h-4"></i> Edit</button>
   <button class="w-full text-left px-3 py-2 rounded-md hover:bg-red-50 text-sm text-red-600 flex items-center gap-2"><i data-lucide="trash-2" class="w-4 h-4"></i> Delete</button>
 `;
        }

        // Day separator
        function daySeparator(key) {
            const label = keyLabel(key);
            return `<div class="day-sep"><span class="line"></span><span>${label}</span><span class="line"></span></div>`;
        }

        // Scrolling helpers
        function scrollChatToBottom() {
            const el = document.getElementById('chat-messages');
            if (!el) return;
            el.scrollTop = el.scrollHeight;
        }
        const chatContainer = document.getElementById('chat-messages');
        if (chatContainer) {
            const obs = new MutationObserver(scrollChatToBottom);
            obs.observe(chatContainer, {
                childList: true,
                subtree: true
            });
            // Load older when near top
            chatContainer.addEventListener('scroll', function() {
                if (this.scrollTop < 40) loadMessages();
            });
        }

        // Formatting helpers
        function escapeHtml(str) {
            return (str || '').replace(/[&<>"']/g, s => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [s]));
        }

        function dayKey(iso) {
            const d = new Date(iso);
            return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        }

        function keyLabel(key) {
            const [y, m, d] = key.split('-').map(n => parseInt(n, 10));
            const dt = new Date(y, m - 1, d);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const diff = (dt - today) / (1000 * 60 * 60 * 24);
            const base = dt.toLocaleDateString([], {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
            if (diff === 0) return `Today — ${base}`;
            if (diff === -1) return `Yesterday — ${base}`;
            return base;
        }

        function toTime(iso) {
            const d = new Date(iso);
            return d.toLocaleTimeString([], {
                hour: 'numeric',
                minute: '2-digit'
            });
        }
    })(jQuery);
</script>
