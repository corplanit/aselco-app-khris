@php
    use Illuminate\Support\Facades\Storage;

    $user = auth()->user();

    $isSupport = false;
    if ($user) {
        if (($user->role ?? null) === 'support') {
            $isSupport = true;
        } elseif (($user->role ?? null) === 'administrator') {
            $isSupport = true;
        } elseif (method_exists($user, 'hasRole') && $user->hasRole('support')) {
            $isSupport = true;
        }
    }
@endphp

<x-app-layout>

    <style>
        @keyframes messageFadeInUp {
            0% {
                transform: translateY(6px);
                opacity: 0
            }

            100% {
                transform: translateY(0);
                opacity: 1
            }
        }

        .message-enter {
            animation: messageFadeInUp .18s ease-out;
        }

        @keyframes sidebarFlash {
            0% {
                background-color: rgba(59, 130, 246, .05)
            }

            100% {
                background-color: transparent
            }
        }

        .sidebar-item-updated {
            animation: sidebarFlash .7s ease-out;
        }

        .conversation-item-active>div {
            background: linear-gradient(to right, rgba(59, 130, 246, 0.08), rgba(59, 130, 246, 0.03));
        }

        @keyframes bump {
            0% {
                transform: translate(-50%, 0) scale(1)
            }

            50% {
                transform: translate(-50%, -2px) scale(1.03)
            }

            100% {
                transform: translate(-50%, 0) scale(1)
            }
        }

        #new-messages-indicator.bump {
            animation: bump .25s ease-out;
        }

        .chat-bg {
            background-color: #ffffff;
            background-image:
                radial-gradient(circle, rgba(0, 0, 0, 0.04) 1px, transparent 1px),
                radial-gradient(circle, rgba(0, 0, 0, 0.02) 1.2px, transparent 1px);
            background-size: 26px 26px, 32px 32px;
            background-repeat: repeat, repeat;
            opacity: 1;
        }

        .message-system {
            font-size: 11px;
            color: #6b7280;
            background: rgba(243, 244, 246, .95);
            border-radius: 9999px;
            padding: 4px 10px;
            border: 1px dashed #d1d5db;
        }

        #chat-editor[contenteditable][data-placeholder]:empty:before {
            content: attr(data-placeholder);
            color: #9ca3af;
            pointer-events: none;
        }

        #chat-editor[contenteditable]:empty:focus:before {
            content: attr(data-placeholder);
        }
    </style>

    <div class="flex h-[calc(85vh-4rem)] gap-4">

        {{-- SIDEBAR --}}
        @if ($isSupport)
            <aside id="sidebare" class="hidden md:flex w-80 flex-col bg-white border rounded-lg">
                <div
                    class="px-4 py-3 border-b flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="flex items-center gap-2">
                        <div
                            class="h-7 w-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">
                            <i class="bi bi-headset"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-800 leading-none">Support Inbox</h2>
                            <p class="text-[11px] text-gray-500 mt-0.5">Customer conversations</p>
                        </div>
                    </div>
                </div>

                <div class="px-3 pt-2 pb-2 border-b bg-gray-50/80">
                    <div class="relative">
                        <input type="text" id="conversation-search" placeholder="Search customers..."
                            class="w-full rounded-full border border-gray-200 bg-white px-3 pr-8 py-1.5 text-xs focus:ring-1 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400">
                        <span class="absolute right-2 top-1.5 text-gray-400 text-xs">
                            <i class="bi bi-search"></i>
                        </span>
                    </div>
                </div>

                <div id="conversations-list" class="flex-1 overflow-y-auto max-h-[calc(100vh-8rem)] text-sm">
                    <div
                        class="px-3 pt-4 pb-1 text-[11px] text-gray-500 uppercase tracking-wide flex items-center gap-1">
                        <i class="bi bi-chat-dots text-[12px] text-gray-400"></i>
                        Customer Chats
                    </div>

                    <ul id="dm-conversations-list" class="divide-y">
                        @forelse($conversations as $conv)
                            @php
                                $title = $conv['title'] ?? 'Customer';
                                $unread = (int) ($conv['unread_count'] ?? 0);
                                $lastAt = $conv['last_message_at'] ?? null;
                                $avatarUrl = $conv['avatar_url'] ?? null;
                                $initials = strtoupper(mb_substr($title, 0, 2));
                                $lastMsg = trim(strip_tags($conv['last_message_body'] ?? ''));
                            @endphp

                            <li class="conversation-item cursor-pointer transition-colors"
                                data-conversation-id="{{ $conv['id'] }}"
                                data-conversation-title="{{ $title }}">
                                <div class="flex items-center justify-between px-3 py-2 hover:bg-gray-100/80">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="avatar-icon h-8 w-8 rounded-full flex items-center justify-center shadow overflow-hidden">
                                            @if ($avatarUrl)
                                                <img src="{{ $avatarUrl }}" onerror="this.src = '/user.png'"
                                                    alt="{{ $title }}" class="h-8 w-8 rounded-full object-cover">
                                            @else
                                                <span
                                                    class="h-8 w-8 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-[11px] font-semibold text-gray-700">
                                                    {{ $initials }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1">
                                                <span
                                                    class="font-semibold text-xs truncate max-w-[200px] conv-title">{{ $title }}</span>
                                            </div>
                                            <div
                                                class="text-[11px] text-gray-500 truncate max-w-[200px] conv-last-message">
                                                {{ $lastMsg ?: 'No messages yet' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <span
                                            class="conv-unread-badge {{ $unread ? '' : 'hidden' }} rounded-full bg-red-600 text-white text-[10px] px-2 shadow">
                                            {{ $unread ?: '' }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 conv-last-at">
                                            {{ $lastAt ? \Carbon\Carbon::parse($lastAt)->format('h:i A') : '' }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="px-4 py-2 text-[11px] text-gray-400">No customer conversations yet.</li>
                        @endforelse
                    </ul>
                </div>
            </aside>
        @endif

        {{-- MAIN --}}
        <div class="flex-1 flex flex-col border rounded-lg bg-white shadow overflow-hidden min-h-0">
            <div id="chat-header"
                class="px-4 py-3 border-b flex items-center justify-between bg-gradient-to-r from-white to-blue-50/60">
                <div class="flex items-center gap-3">
                    <button id="btn-open-sidebar-mobile"
                        class="md:hidden p-2 rounded-md hover:bg-gray-100 text-gray-600">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="h-9 w-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-500"
                        id="chat-avatar">
                        <i class="bi bi-chat-left-text"></i>
                    </div>
                    <div>
                        <div id="chat-title" class="font-semibold text-sm text-gray-800">
                            {{ $isSupport ? 'Select a customer' : 'Chat with Support' }}
                        </div>
                        <div id="chat-subtitle" class="text-xs text-gray-500 flex items-center gap-1">
                            <span class="inline-flex h-1.5 w-1.5 rounded-full bg-gray-300"></span>
                            {{ $isSupport ? 'No conversation opened' : 'Our support team can see this chat and reply.' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative flex-1 flex flex-col bg-slate-50/80 min-h-0">
                <div class="absolute inset-0 chat-bg pointer-events-none opacity-70"></div>

                <div id="messages-container"
                    class="relative z-10 flex-1 overflow-y-auto px-4 py-3 space-y-2 text-sm flex flex-col">
                    <div id="empty-state" class="m-auto text-center text-xs text-gray-500">
                        <div
                            class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 shadow text-blue-500 mb-2">
                            <i class="bi bi-chat-square-dots text-xl"></i>
                        </div>
                        <p class="font-medium text-gray-700 mb-1">
                            {{ $isSupport ? 'No customer selected' : 'Start a conversation with Support' }}
                        </p>
                    </div>
                </div>

                <button id="new-messages-indicator"
                    class="hidden absolute bottom-3 left-1/2 -translate-x-1/2 px-3 py-1.5 rounded-full bg-blue-600 text-white text-[11px] shadow flex items-center gap-1 z-20">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-white"></span>
                    New messages <span class="text-xs">↓</span>
                </button>
            </div>

            <form id="chat-form"
                class="border-t px-3 py-2 flex flex-col gap-2 bg-white/95 backdrop-blur-sm opacity-60 pointer-events-none">
                @csrf
                <input type="hidden" id="active-conversation-id" name="conversation_id" value="">

                <div class="flex items-end gap-2">
                    <div
                        class="flex-1 max-h-40 overflow-y-auto border border-gray-200 rounded-2xl px-3 py-2 bg-gray-50 text-sm">
                        <div id="chat-editor" contenteditable="true"
                            class="outline-none whitespace-pre-wrap break-words"
                            data-placeholder="{{ $isSupport ? 'Type a reply…' : 'Type your message…' }}"></div>
                    </div>
                    <button type="submit" id="btn-send" class="hidden"></button>
                </div>
            </form>
        </div>
    </div>

    <div id="mobile-sidebar-backdrop" class="hidden fixed inset-0 bg-black/40 z-30"></div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        const authUserId = @json(auth()->id());
        const isSupportUser = @json($isSupport);

        const dmList = document.getElementById('dm-conversations-list');
        const conversationsRoot = document.getElementById('conversations-list');
        const searchInput = document.getElementById('conversation-search');

        const chatTitle = document.getElementById('chat-title');
        const chatSubtitle = document.getElementById('chat-subtitle');
        const chatAvatar = document.getElementById('chat-avatar');
        const messagesContainer = document.getElementById('messages-container');
        const chatForm = document.getElementById('chat-form');
        const hiddenConvIdInput = document.getElementById('active-conversation-id');
        const emptyState = document.getElementById('empty-state');
        const newMsgIndicator = document.getElementById('new-messages-indicator');
        const editor = document.getElementById('chat-editor');

        let loadingMessages = false;
        let nextPageCursor = null;
        let currentConversation = null;
        let renderedMessageIds = new Set();
        let sending = false;

        // ✅ polling
        let pollTimer = null;
        let inboxTimer = null;
        let lastMessageId = 0;
        let lastInboxCheck = null;

        function looksLikeHtml(s) {
            return /<\/?[a-z][\s\S]*>/i.test(s || '');
        }

        function linkify(text) {
            if (!text) return '';
            const withBreaks = String(text).replace(/\n/g, '<br>');
            return withBreaks.replace(/((https?:\/\/|www\.)[^\s<]+)/gi, (m) => {
                const url = m.startsWith('http') ? m : `https://${m}`;
                return `<a href="${url}" target="_blank" rel="noopener noreferrer">${m}</a>`;
            });
        }

        function sanitizeAllowedHtml(html) {
            const allowed = new Set(['b', 'strong', 'i', 'em', 'u', 'code', 'pre', 'ul', 'ol', 'li', 'br', 'p', 'span',
                'a'
            ]);
            const parser = new DOMParser();
            const doc = parser.parseFromString(html || '', 'text/html');

            const walk = (node) => {
                if (node.nodeType !== Node.ELEMENT_NODE) return;
                const tag = node.tagName.toLowerCase();

                if (!allowed.has(tag)) {
                    node.replaceWith(...Array.from(node.childNodes));
                    return;
                }

                [...node.attributes].forEach(a => {
                    if (tag === 'a' && a.name === 'href') return;
                    node.removeAttribute(a.name);
                });

                if (tag === 'a') {
                    const href = node.getAttribute('href') || node.textContent || '';
                    try {
                        const u = new URL(href, window.location.origin);
                        if (u.protocol === 'http:' || u.protocol === 'https:') {
                            node.setAttribute('href', u.href);
                            node.setAttribute('rel', 'noopener noreferrer');
                            node.setAttribute('target', '_blank');
                        } else node.removeAttribute('href');
                    } catch {
                        node.removeAttribute('href');
                    }
                }

                [...node.childNodes].forEach(walk);
            };

            [...doc.body.childNodes].forEach(walk);
            return doc.body.innerHTML.trim();
        }

        function isEmptyHtml(html) {
            if (!html) return true;
            const div = document.createElement('div');
            div.innerHTML = html;
            div.querySelectorAll('br').forEach(br => br.remove());
            const text = (div.textContent || '').replace(/\u00A0/g, ' ').trim();
            return text.length === 0;
        }

        function isNearBottom() {
            const threshold = 80;
            return (messagesContainer.scrollHeight - messagesContainer.scrollTop - messagesContainer.clientHeight) <
                threshold;
        }

        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function clearMessages() {
            messagesContainer.innerHTML = '';
            renderedMessageIds.clear();
            lastMessageId = 0;
        }

        function renderMessage(msg, {
            animated = true
        } = {}) {
            if (msg.id && renderedMessageIds.has(msg.id)) return;
            if (msg.id) renderedMessageIds.add(msg.id);
            if (msg.id) lastMessageId = Math.max(lastMessageId, parseInt(msg.id, 10));

            const isMine = msg.user && msg.user.id === authUserId;

            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-end gap-2 ' + (isMine ? 'justify-end' : 'justify-start');
            if (animated) wrapper.classList.add('message-enter');

            const bubble = document.createElement('div');
            bubble.className =
                'max-w-[72%] rounded-2xl px-3 py-2 text-sm shadow ' +
                (isMine ? 'bg-blue-600 text-white rounded-br-sm' :
                    'bg-white/95 text-gray-900 border border-gray-200/80 rounded-bl-sm');

            const name = document.createElement('div');
            name.className = 'text-[11px] font-semibold mb-0.5 opacity-80';
            name.textContent = msg.user?.name ?? 'Unknown';

            const body = document.createElement('div');
            body.className = 'whitespace-pre-wrap break-words';
            const raw = msg.body || '';
            body.innerHTML = sanitizeAllowedHtml(looksLikeHtml(raw) || msg.is_html ? raw : linkify(raw));

            const meta = document.createElement('div');
            meta.className = 'mt-0.5 text-[10px] opacity-80 ' + (isMine ? 'text-right' : 'text-left');
            meta.textContent = new Date(msg.created_at).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });

            bubble.appendChild(name);
            bubble.appendChild(body);
            bubble.appendChild(meta);

            wrapper.appendChild(bubble);
            messagesContainer.appendChild(wrapper);
        }

        async function loadMessages(conversationId, beforeId = null) {
            if (loadingMessages) return;
            loadingMessages = true;

            const url = new URL(`/supp/chat/${conversationId}/messages`, window.location.origin);
            if (beforeId) url.searchParams.append('before_id', beforeId);

            try {
                const {
                    data
                } = await axios.get(url.toString());
                if (!beforeId) clearMessages();

                (data.data || []).forEach(m => renderMessage(m, {
                    animated: !beforeId
                }));
                nextPageCursor = data.next_page;

                if (!beforeId) scrollToBottom();
            } finally {
                loadingMessages = false;
            }
        }

        async function pollNewMessages() {
            if (!currentConversation) return;
            if (document.hidden) return;

            try {
                const url = new URL(`/supp/chat/${currentConversation}/messages/new`, window.location.origin);
                url.searchParams.set('after_id', String(lastMessageId || 0));

                const {
                    data
                } = await axios.get(url.toString());
                const items = data.data || [];
                if (!items.length) return;

                const wasNear = isNearBottom();
                items.forEach(m => renderMessage(m, {
                    animated: true
                }));

                if (wasNear) scrollToBottom();
                else {
                    newMsgIndicator.classList.remove('hidden');
                    newMsgIndicator.classList.add('bump');
                    setTimeout(() => newMsgIndicator.classList.remove('bump'), 250);
                }

                await axios.post(`/supp/chat/${currentConversation}/read`);
            } catch (e) {}
        }

        function upsertConversationInSidebar(payload) {
            if (!dmList) return;

            const convId = String(payload.conversation_id);
            let item = dmList.querySelector(`[data-conversation-id="${convId}"]`);
            if (!item) return;

            const titleEl = item.querySelector('.conv-title');
            const lastEl = item.querySelector('.conv-last-message');
            const atEl = item.querySelector('.conv-last-at');
            const badgeEl = item.querySelector('.conv-unread-badge');

            if (titleEl) titleEl.textContent = payload.title || 'Customer';
            if (lastEl) lastEl.textContent = (payload.last_message || '').replace(/\s+/g, ' ').trim();
            if (atEl) atEl.textContent = payload.last_at ?
                new Date(payload.last_at).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                }) :
                '';

            const unread = parseInt(payload.unread_count ?? 0, 10) || 0;

            if (String(convId) === String(currentConversation)) {
                if (badgeEl) {
                    badgeEl.classList.add('hidden');
                    badgeEl.textContent = '';
                }
            } else {
                if (badgeEl) {
                    if (unread > 0) {
                        badgeEl.textContent = unread;
                        badgeEl.classList.remove('hidden');
                    } else {
                        badgeEl.textContent = '';
                        badgeEl.classList.add('hidden');
                    }
                }
            }

            dmList.prepend(item);
            item.classList.add('sidebar-item-updated');
            setTimeout(() => item.classList.remove('sidebar-item-updated'), 700);
        }

        async function pollInboxUpdates() {
            if (!isSupportUser) return;
            if (document.hidden) return;

            try {
                const url = new URL(`/supp/chat/poll/updates`, window.location.origin);
                if (lastInboxCheck) url.searchParams.set('after', lastInboxCheck);

                const {
                    data
                } = await axios.get(url.toString());

                (data.data || []).forEach(row => upsertConversationInSidebar(row));
                if (data.server_time) lastInboxCheck = data.server_time;
            } catch (e) {}
        }

        function startPolling() {
            if (pollTimer) clearInterval(pollTimer);
            if (inboxTimer) clearInterval(inboxTimer);

            pollTimer = setInterval(pollNewMessages, 3000);
            inboxTimer = setInterval(pollInboxUpdates, 3000);
        }

        // open conversation (support)
        if (conversationsRoot) {
            conversationsRoot.addEventListener('click', async (e) => {
                const li = e.target.closest('.conversation-item');
                if (!li) return;

                const convId = li.dataset.conversationId;
                const title = li.dataset.conversationTitle;

                currentConversation = convId;
                hiddenConvIdInput.value = convId;

                enableChatForm(true);

                chatTitle.textContent = title || 'Customer';
                chatSubtitle.textContent = 'Customer support conversation';

                conversationsRoot.querySelectorAll('.conversation-item').forEach(item => item.classList.remove(
                    'conversation-item-active'));
                li.classList.add('conversation-item-active');

                await axios.post(`/supp/chat/${convId}/read`);

                if (emptyState) emptyState.remove();
                await loadMessages(convId);
            });
        }

        // send message
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!currentConversation) return;
            if (sending) return;

            const rawHtml = editor.innerHTML;
            const source = looksLikeHtml(rawHtml) ? rawHtml : linkify(rawHtml);
            const bodyHtml = sanitizeAllowedHtml(source);

            if (isEmptyHtml(bodyHtml)) return;

            sending = true;
            try {
                const fd = new FormData();
                fd.append('type', 'text');
                fd.append('body', bodyHtml);
                fd.append('is_html', '1');

                const {
                    data: msg
                } = await axios.post(`/supp/chat/${currentConversation}/messages`, fd);
                editor.innerHTML = '';
                renderMessage(msg, {
                    animated: true
                });
                scrollToBottom();
                await axios.post(`/supp/chat/${currentConversation}/read`);
            } finally {
                sending = false;
            }
        });

        // customer ensure
        async function ensureCustomerConversation() {
            const {
                data
            } = await axios.post('/supp/chat/ensure-mine');
            return data.conversation_id;
        }

        document.addEventListener('DOMContentLoaded', async () => {
            enableChatForm(false);
            if (isSupportUser) {
                pollInboxUpdates();
                const first = dmList ? dmList.querySelector('.conversation-item') : null;
                if (first) first.click();
            } else {
                enableChatForm(true);
                const convId = await ensureCustomerConversation();
                currentConversation = convId;
                hiddenConvIdInput.value = convId;

                if (emptyState) emptyState.remove();
                await loadMessages(convId);
                await axios.post(`/supp/chat/${convId}/read`);
            }

            startPolling();
        });

        // enter to send
        editor.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.requestSubmit();
            }
        });

        function enableChatForm(enable) {
            if (!chatForm) return;
            if (enable) chatForm.classList.remove('opacity-60', 'pointer-events-none');
            else chatForm.classList.add('opacity-60', 'pointer-events-none');
        }
    </script>

</x-app-layout>
