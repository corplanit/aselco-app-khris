@php
    use Illuminate\Support\Facades\Storage;
    use App\Models\User;

    $user = auth()->user();

    // Detect if current user is a support agent
    $isSupport = false;
    if ($user) {
        if ($user->role === 'support') {
            $isSupport = true;        
        } elseif ($user->role === 'Administrator') {
            $isSupport = true;
        }  elseif (method_exists($user, 'hasRole') && $user->hasRole('support')) {
            $isSupport = true;
        }
    }

    // All support users (for determining support conversations & building support room)
    $supportUserIds       = User::where('role', 'support')->pluck('id')->all();
    $supportIdCollection  = collect($supportUserIds);
@endphp

<x-app-layout>

    {{-- Styles --}}
    <style>

        /* ===========================
   MOBILE CHAT (<= 768px)
   - Full width chat
   - Sidebar is off-canvas
   - Hide buttons (toggle/attach/etc.)
   =========================== */
@media (max-width: 768px) {

  /* Parent wrapper: remove gaps so chat becomes full width */
  .flex.h-\[calc\(85vh-4rem\)\] {
    gap: 0 !important;
  }

  /* MAIN chat should be full width */
  .flex.h-\[calc\(85vh-4rem\)\] > .flex-1 {
    width: 100% !important;
    min-width: 100% !important;
  }

  /* If sidebar exists, keep it hidden by default on mobile */
  #sidebar {
    display: none !important;
  }

  /* Header spacing tighter */
  #chat-header {
    padding: 10px 12px !important;
  }

  /* Messages area padding smaller */
  #messages-container {
    padding-left: 12px !important;
    padding-right: 12px !important;
    padding-top: 10px !important;
    padding-bottom: 10px !important;
  }

  /* Input area tighter */
  #chat-form {
    padding: 10px 10px !important;
  }

  /* Bubble max width larger on mobile */
  .message-bubble {
    max-width: 86% !important;
  }

  /* --- HIDE BUTTONS ON MOBILE --- */

  /* Hide sidebar open button */
  #btn-open-sidebar-mobile {
    display: none !important;
  }

  /* Hide attachment/image buttons */
  #btn-attach,
  #btn-image {
    display: none !important;
  }

  /* (Optional) hide emoji button + picker */
  #btn-emoji,
  #emoji-picker {
    display: none !important;
  }

  /* If you want Send button icon-only (optional) */
  #btn-send {
    padding: 10px 14px !important;
    font-size: 13px !important;
  }

  /* Editor smaller height so it doesn't eat the screen */
  .max-h-40 {
    max-height: 120px !important;
  }
}


        @keyframes messageFadeInUp {
            0% { transform: translateY(6px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        .message-enter { animation: messageFadeInUp 0.18s ease-out; }

        @keyframes sidebarFlash {
            0% { background-color: rgba(59, 130, 246, 0.05); }
            100% { background-color: transparent; }
        }
        .sidebar-item-updated { animation: sidebarFlash 0.7s ease-out; }

        .conversation-item-active > div {
            background: linear-gradient(to right, rgba(59,130,246,0.08), rgba(59,130,246,0.03));
        }

        @keyframes bump {
            0%   { transform: translate(-50%,0) scale(1); }
            50%  { transform: translate(-50%,-2px) scale(1.03); }
            100% { transform: translate(-50%,0) scale(1); }
        }
        #new-messages-indicator.bump { animation: bump 0.25s ease-out; }

        .chat-bg {
            background-color: #ffffff;
            background-image:
                radial-gradient(circle, rgba(0,0,0,0.04) 1px, transparent 1px),
                radial-gradient(circle, rgba(0,0,0,0.02) 1.2px, transparent 1px),
                url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160'><g fill='none' stroke='%23e5e5e5' stroke-width='1' opacity='0.5' stroke-linecap='round' stroke-linejoin='round'><g transform='rotate(-15 40 40)'><rect x='18' y='18' width='46' height='26' rx='8'/><path d='M24 44l-5 7'/></g><g transform='rotate(12 116 46)'><path d='M120 32c6 6 10 14 10 20 0 3-1 6-3 8l-3 3c-2 2-5 3-8 3-6 0-12-5-18-10'/><path d='M114 30l5-3'/><path d='M126 60l-3 5'/></g><g transform='rotate(-10 60 120)'><rect x='34' y='104' width='48' height='24' rx='8'/><path d='M42 126l-4 6'/></g></g></svg>");
            background-size: 26px 26px, 32px 32px, 220px 220px;
            background-repeat: repeat, repeat, repeat;
            background-position: center center, center center, center center;
            opacity: 1;
        }

        .message-bubble { position: relative; }
        .message-actions { display: none; }
        .message-bubble:hover .message-actions { display: inline-flex; }

        .message-system {
            font-size: 11px;
            color: #6b7280;
            background: rgba(243,244,246,0.95);
            border-radius: 9999px;
            padding: 4px 10px;
            border: 1px dashed #d1d5db;
        }

        .message-avatar { flex-shrink: 0; }

        #chat-editor[contenteditable][data-placeholder]:empty:before {
            content: attr(data-placeholder);
            color: #9ca3af;
            pointer-events: none;
        }
        #chat-editor[contenteditable]:empty:focus:before {
            content: attr(data-placeholder);
        }

        #pending-preview .att-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            background-color: #f9fafb;
            font-size: 0.75rem;
        }
    </style>

    <div class="flex h-[calc(85vh-4rem)] gap-4">
        {{-- SIDEBAR --}}
        @if ($isSupport)
        <aside id="sidebar" class="hidden md:flex w-80 flex-col bg-white border rounded-lg">
            <div class="px-4 py-3 border-b flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center gap-2">
                    <div class="h-7 w-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 leading-none">Support Inbox</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">Customer conversations</p>
                    </div>
                </div>
            </div>


            {{-- Search --}}
            <div class="px-3 pt-2 pb-2 border-b bg-gray-50/80">
                <div class="relative">
                    <input type="text" id="conversation-search"
                           placeholder="{{ $isSupport ? 'Search customers...' : 'Search support history...' }}"
                           class="w-full rounded-full border border-gray-200 bg-white px-3 pr-8 py-1.5 text-xs focus:ring-1 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400">
                    <span class="absolute right-2 top-1.5 text-gray-400 text-xs">
                        <i class="bi bi-search"></i>
                    </span>
                </div>
            </div>

            <div id="conversations-list" class="flex-1 overflow-y-auto max-h-[calc(100vh-8rem)] text-sm">
                <div class="px-3 pt-4 pb-1 text-[11px] text-gray-500 uppercase tracking-wide flex items-center gap-1">
                    <i class="bi bi-chat-dots text-[12px] text-gray-400"></i>
                    {{ $isSupport ? 'Customer Chats' : 'Support Conversations' }}
                </div>

                <ul id="dm-conversations-list" class="divide-y">
                    @php
                        $authUserId = auth()->id();

                        // Filter conversations:
                        // 1) Not closed (status != 'closed' and type != 'closed')
                        // 2) Must contain at least one support user AND at least one non-support user
                        // This ensures we only show true "support" conversations.
                        $dmConversations = $conversations
                            ->filter(function ($c) {
                                return ($c->status ?? null) !== 'closed'
                                    && ($c->type ?? null) !== 'closed';
                            })
                            ->filter(function ($c) use ($supportIdCollection) {
                                if (! $c->relationLoaded('participants')) {
                                    return false;
                                }

                                $participantIds = $c->participants->pluck('user_id');
                                $hasSupport     = $participantIds->intersect($supportIdCollection)->isNotEmpty();
                                $hasNonSupport  = $participantIds->diff($supportIdCollection)->isNotEmpty();

                                return $hasSupport && $hasNonSupport;
                            })
                            ->sortByDesc(function ($c) {
                                return $c->last_message_at ?? $c->updated_at;
                            });
                    @endphp

                    @forelse($dmConversations as $conv)
                        @php
                            $rawLastMsg = $conv->last_message_body ?? 'No messages yet';
                            $lastMsg    = trim(strip_tags($rawLastMsg));
                            $unread     = $conv->unread_count ?? 0;
                            $lastAt     = $conv->last_message_at ?? $conv->updated_at;

                            $participants = $conv->participants->pluck('user')->filter();

                            $customerUser = null;
                            $supportUser  = null;

                            foreach ($participants as $pUser) {
                                if (! $pUser) {
                                    continue;
                                }

                                $isSupportRole = ($pUser->role ?? null) === 'support';

                                if ($isSupportRole) {
                                    if (! $supportUser) {
                                        $supportUser = $pUser;
                                    }
                                } else {
                                    if (! $customerUser) {
                                        $customerUser = $pUser;
                                    }
                                }
                            }

                            if ($isSupport) {
                                // Support view: title is customer’s name
                                $displayTitle = $customerUser->name
                                    ?? $conv->display_title
                                    ?? 'Customer';
                                $avatarUser = $customerUser ?: $customerUser;
                            } else {
                                // Customer view: always "Support Team"
                                $displayTitle = 'Support Team';
                                $avatarUser   = $supportUser ?: $participants->first();
                            }

                            $avatarUrl =
                                $avatarUser?->profile_photo_url ??
                                ($avatarUser?->profile_photo_path ? Storage::url($avatarUser->profile_photo_path) : null);

                            $initials = strtoupper(mb_substr($displayTitle, 0, 2));
                        @endphp

                        <li class="conversation-item cursor-pointer transition-colors"
                            data-conversation-id="{{ $conv->id }}"
                            data-conversation-title="{{ $displayTitle }}"
                            data-is-group="{{ (int) ($conv->is_group ?? false) }}"
                            data-last-at="{{ optional($lastAt)->toIso8601String() }}"
                            data-created-at="{{ optional($conv->created_at)->toIso8601String() }}"
                            @if ($avatarUrl) data-avatar-url="{{ $avatarUrl }}" @endif
                            @if ($customerUser)
                                data-other-user-id="{{ $customerUser->id }}"
                                data-other-user-name="{{ $customerUser->name }}"
                                data-other-user-email="{{ $customerUser->email }}"
                            @endif
                        >
                            <div class="flex items-center justify-between px-3 py-2 hover:bg-gray-100/80">
                                <div class="flex items-center gap-2">
                                    <div class="avatar-icon h-8 w-8 rounded-full flex items-center justify-center shadow overflow-hidden">
                                        @if ($avatarUrl)
                                            <img src="{{ $avatarUrl }}" onerror="this.src = '/user.png'"
                                                 alt="{{ $displayTitle }}"
                                                 class="h-8 w-8 rounded-full object-cover">
                                        @else
                                            <span class="h-8 w-8 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-[11px] font-semibold text-gray-700">
                                                {{ $initials }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1">
                                            <span class="font-semibold text-xs truncate max-w-[200px] conv-title">
                                                {{ $displayTitle }}
                                                @if (!$isSupport)
                                                    <span class="ml-1 text-[10px] text-emerald-600 font-normal">
                                                        Customer Support
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-gray-500 truncate max-w-[200px] conv-last-message">
                                            {{ $lastMsg }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <span
                                        class="conv-unread-badge {{ $unread ? '' : 'hidden' }} rounded-full bg-red-600 text-white text-[10px] px-2 shadow">
                                        {{ $unread ?: '' }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 conv-last-at">
                                        {{ $lastAt ? $lastAt->format('h:i A') : '' }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-2 text-[11px] text-gray-400">
                            {{ $isSupport ? 'No customer conversations yet.' : 'No support conversations yet. Type a message to start.' }}
                        </li>
                    @endforelse
                </ul>
            </div>
        </aside>
        @endif

        {{-- MAIN CHAT AREA --}}
        <div class="flex-1 flex flex-col border rounded-lg bg-white shadow overflow-hidden min-h-0">
            {{-- Header --}}
            <div id="chat-header"
                 class="px-4 py-3 border-b flex items-center justify-between bg-gradient-to-r from-white to-blue-50/60">
                <div class="flex items-center gap-3">
                    <button id="btn-open-sidebar-mobile" class="md:hidden p-2 rounded-md hover:bg-gray-100 text-gray-600" title="Open conversations">
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
                            @if ($isSupport)
                                No conversation opened
                            @else
                                Our support team can see this chat and reply.
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-[11px]">
                    <div class="flex items-center gap-1 text-emerald-600">
                        <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>{{ $isSupport ? 'You are online' : 'Support is online' }}</span>
                    </div>
                </div>
            </div>

            {{-- Messages --}}
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
                        <p>
                            @if ($isSupport)
                                Choose a customer conversation from the left to start replying.
                            @else
                                Just type a message below — we’ll open a support room for you.
                            @endif
                        </p>
                    </div>
                </div>

                {{-- New messages indicator --}}
                <button id="new-messages-indicator"
                        class="hidden absolute bottom-3 left-1/2 -translate-x-1/2 px-3 py-1.5 rounded-full bg-blue-600 text-white text-[11px] shadow flex items-center gap-1 z-20">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-white"></span>
                    New messages
                    <span class="text-xs">↓</span>
                </button>
            </div>

            {{-- Input --}}
            <form id="chat-form"
                  class="border-t px-3 py-2 flex flex-col gap-2 bg-white/95 backdrop-blur-sm {{ $isSupport ? 'opacity-60 pointer-events-none' : '' }}">
                @csrf
                <input type="hidden" id="active-conversation-id" name="conversation_id" value="">

                {{-- Attachments preview --}}
                <div id="pending-preview" class="flex flex-wrap gap-2 mb-1 hidden"></div>

                <div class="flex items-end gap-2">
                    {{-- Left tools --}}
                    <div class="flex items-center gap-1">
                        <button type="button" id="btn-attach"
                                class="p-2 rounded-full hover:bg-gray-100 text-gray-600" title="Attach files">
                            <i class="bi bi-paperclip"></i>
                        </button>
                        <button type="button" id="btn-image"
                                class="p-2 rounded-full hover:bg-gray-100 text-gray-600" title="Attach image">
                            <i class="bi bi-image"></i>
                        </button>
                        <div class="relative">
                            <button type="button" id="btn-emoji"
                                    class="p-2 rounded-full hover:bg-gray-100 text-gray-600" title="Insert emoji">
                                <i class="bi bi-emoji-smile"></i>
                            </button>
                            <div id="emoji-picker"
                                 class="hidden absolute bottom-10 left-0 bg-white border border-gray-200 rounded-lg shadow p-2 text-base z-30">
                                <div class="flex flex-wrap max-w-[200px]">
                                    <button type="button" class="emoji-item px-1">😀</button>
                                    <button type="button" class="emoji-item px-1">😁</button>
                                    <button type="button" class="emoji-item px-1">😂</button>
                                    <button type="button" class="emoji-item px-1">😊</button>
                                    <button type="button" class="emoji-item px-1">😍</button>
                                    <button type="button" class="emoji-item px-1">😎</button>
                                    <button type="button" class="emoji-item px-1">😅</button>
                                    <button type="button" class="emoji-item px-1">🤔</button>
                                    <button type="button" class="emoji-item px-1">👍</button>
                                    <button type="button" class="emoji-item px-1">🙏</button>
                                    <button type="button" class="emoji-item px-1">🔥</button>
                                    <button type="button" class="emoji-item px-1">🎉</button>
                                    <button type="button" class="emoji-item px-1">✅</button>
                                    <button type="button" class="emoji-item px-1">❗</button>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden file inputs --}}
                        <input id="file-input" type="file" multiple class="hidden">
                        <input id="img-input" type="file" accept="image/*" multiple class="hidden">
                    </div>

                    {{-- Editor --}}
                    <div
                        class="flex-1 max-h-40 overflow-y-auto border border-gray-200 rounded-2xl px-3 py-2 bg-gray-50 text-sm">
                        <div id="chat-editor" contenteditable="true" role="textbox" aria-multiline="true"
                             class="outline-none whitespace-pre-wrap break-words"
                             data-placeholder="{{ $isSupport ? 'Type a reply… (Shift+Enter for new line)' : 'Type your message… (Shift+Enter for new line)' }}"></div>
                    </div>

                    {{-- Send --}}
                    <button type="submit" id="btn-send"
                            class="inline-flex items-center justify-center rounded-full bg-blue-600 text-white text-sm px-3 py-2 hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 shadow">
                        <i class="bi bi-send-fill mr-1 text-xs"></i>
                        Send
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="mobile-sidebar-backdrop" class="hidden fixed inset-0 bg-black/40 z-30"></div>

    {{-- Axios --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        const authUserId     = @json(auth()->id());
        const authUserName   = @json(auth()->user()->name ?? 'Customer');
        const isSupportUser  = @json($isSupport);
        const supportUserIds = @json($supportUserIds ?? []);
        const autoOpenConversationId = @json($autoOpenConversationId ?? null);

        const conversationsRoot = document.getElementById('conversations-list');
        const dmList            = document.getElementById('dm-conversations-list');
        const searchInput       = document.getElementById('conversation-search');
        const chatTitle         = document.getElementById('chat-title');
        const chatSubtitle      = document.getElementById('chat-subtitle');
        const chatAvatar        = document.getElementById('chat-avatar');
        const messagesContainer = document.getElementById('messages-container');
        const chatForm          = document.getElementById('chat-form');
        const hiddenConvIdInput = document.getElementById('active-conversation-id');
        const emptyState        = document.getElementById('empty-state');
        const newMsgIndicator   = document.getElementById('new-messages-indicator');

        const editor        = document.getElementById('chat-editor');
        const sendBtn       = document.getElementById('btn-send');
        const btnAttach     = document.getElementById('btn-attach');
        const btnImage      = document.getElementById('btn-image');
        const fileInput     = document.getElementById('file-input');
        const imgInput      = document.getElementById('img-input');
        const pendingPreview= document.getElementById('pending-preview');
        const btnEmoji      = document.getElementById('btn-emoji');
        const emojiPicker   = document.getElementById('emoji-picker');

        let loadingMessages        = false;
        let nextPageCursor         = null;
        let currentConversation    = null;
        let currentEchoChannelName = null;
        let renderedMessageIds     = new Set();
        let pendingFiles           = [];
        let sending                = false;

        function clearMessages() {
            messagesContainer.innerHTML = '';
            renderedMessageIds.clear();
        }

        function showMessageSkeleton() {
            messagesContainer.innerHTML = '';
            renderedMessageIds.clear();

            for (let i = 0; i < 5; i++) {
                const mine = i % 2 === 0;
                const wrapper = document.createElement('div');
                wrapper.className = `flex ${mine ? 'justify-end' : 'justify-start'} mb-3 message-skeleton`;

                const inner = document.createElement('div');
                inner.className = 'flex items-end gap-2 max-w-[80%] animate-pulse';

                if (!mine) {
                    const avatar = document.createElement('div');
                    avatar.className = 'h-7 w-7 rounded-full bg-gray-200';
                    inner.appendChild(avatar);
                }

                const bubble = document.createElement('div');
                bubble.className = 'rounded-2xl bg-gray-200 h-4 w-40 sm:w-52';
                inner.appendChild(bubble);

                wrapper.appendChild(inner);
                messagesContainer.appendChild(wrapper);
            }
        }

        function showNewConversationPlaceholder() {
            if (messagesContainer.children.length > 0) return;

            const existing = document.getElementById('new-conversation-placeholder');
            if (existing) existing.remove();

            const wrapper = document.createElement('div');
            wrapper.id = 'new-conversation-placeholder';
            wrapper.className = 'p-6 mt-10 bg-white border rounded-lg shadow text-center max-w-md mx-auto';

            wrapper.innerHTML = `
                <p class="font-medium text-gray-700 mb-1 text-sm">New conversation</p>
                <p class="text-gray-500 text-xs">
                    ${isSupportUser ? 'Type your reply below.' : 'Start by saying hello 👋.'}
                </p>
                <div class="mt-4 flex justify-center">
                    <button type="button"
                        class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 shadow"
                        onclick="focusChatInput()">
                        ${isSupportUser ? 'Reply' : 'Say Hello'}
                    </button>
                </div>
            `;

            messagesContainer.appendChild(wrapper);
        }

        function focusChatInput() {
            if (editor) editor.focus();
        }

        function enableChatForm(enable) {
            if (enable) {
                chatForm.classList.remove('opacity-60', 'pointer-events-none');
            } else {
                chatForm.classList.add('opacity-60', 'pointer-events-none');
            }
        }

        function isNearBottom() {
            const threshold = 80;
            return (
                messagesContainer.scrollHeight -
                messagesContainer.scrollTop -
                messagesContainer.clientHeight
            ) < threshold;
        }

        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Date helpers
        function isSameDay(a, b) {
            return a.getFullYear() === b.getFullYear() &&
                   a.getMonth() === b.getMonth() &&
                   a.getDate() === b.getDate();
        }

        function getDateLabel(dateInput) {
            const d = dateInput instanceof Date ? dateInput : new Date(dateInput);
            const today = new Date();
            const yesterday = new Date();
            yesterday.setDate(today.getDate() - 1);

            if (isSameDay(d, today)) return 'Today';
            if (isSameDay(d, yesterday)) return 'Yesterday';

            return d.toLocaleDateString(undefined, {
                weekday: 'short',
                month: 'short',
                day: '2-digit',
                year: 'numeric',
            });
        }

        function getDayKey(dateInput) {
            const d = dateInput instanceof Date ? dateInput : new Date(dateInput);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        function createDateDivider(label, dayKey) {
            const divider = document.createElement('div');
            divider.className = 'flex items-center justify-center my-3 text-[11px] text-gray-500';
            divider.dataset.dividerKey = dayKey;
            divider.innerHTML = `
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/80 border border-gray-200 shadow">
                    <span class="h-px w-4 bg-gray-300"></span>
                    <span>${label}</span>
                    <span class="h-px w-4 bg-gray-300"></span>
                </div>
            `;
            return divider;
        }

        function insertMessageElement(wrapper, createdAt, { prepend = false } = {}) {
            const dateObj = new Date(createdAt);
            const dayKey  = getDayKey(dateObj);
            const label   = getDateLabel(dateObj);
            wrapper.dataset.dayKey = dayKey;

            if (!messagesContainer.children.length) {
                const divider = createDateDivider(label, dayKey);
                messagesContainer.appendChild(divider);
                messagesContainer.appendChild(wrapper);
                return;
            }

            if (prepend) {
                const first    = messagesContainer.firstElementChild;
                const firstKey = first.dataset.dayKey || first.dataset.dividerKey;

                if (firstKey !== dayKey) {
                    messagesContainer.prepend(wrapper);
                    messagesContainer.prepend(createDateDivider(label, dayKey));
                } else {
                    messagesContainer.prepend(wrapper);
                }
            } else {
                let lastKey = null;
                const children = messagesContainer.children;
                for (let i = children.length - 1; i >= 0; i--) {
                    const el  = children[i];
                    const key = el.dataset.dayKey || el.dataset.dividerKey;
                    if (key) {
                        lastKey = key;
                        break;
                    }
                }
                if (lastKey !== dayKey) {
                    messagesContainer.appendChild(createDateDivider(label, dayKey));
                }
                messagesContainer.appendChild(wrapper);
            }
        }

        // HTML helpers
        function sanitizeAllowedHtml(html) {
            const allowed = new Set(['b','strong','i','em','u','code','pre','ul','ol','li','br','p','span','a']);
            const parser = new DOMParser();
            const doc    = parser.parseFromString(html || '', 'text/html');

            const walk = (node) => {
                if (node.nodeType !== Node.ELEMENT_NODE) return;
                const tag = node.tagName.toLowerCase();

                if (!allowed.has(tag)) {
                    node.replaceWith(...Array.from(node.childNodes));
                    return;
                }

                [...node.attributes].forEach(a => {
                    if (tag === 'a' && a.name === 'href') return;
                    if (tag === 'ol' && a.name === 'start' && /^\d+$/.test(a.value)) return;
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
                        } else {
                            node.removeAttribute('href');
                        }
                    } catch (e) {
                        node.removeAttribute('href');
                    }
                }

                [...node.childNodes].forEach(walk);
            };

            [...doc.body.childNodes].forEach(walk);
            return doc.body.innerHTML.trim();
        }

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

        function isEmptyHtml(html) {
            if (!html) return true;
            const div = document.createElement('div');
            div.innerHTML = html;
            div.querySelectorAll('br').forEach(br => br.remove());
            const text = (div.textContent || '').replace(/\u00A0/g, ' ').trim();
            return text.length === 0;
        }

        function escapeHtml(s) {
            return (s || '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[c]));
        }

        function htmlToPlainText(html) {
            if (!html) return '';
            const div = document.createElement('div');
            div.innerHTML = html;
            return (div.textContent || '').replace(/\s+/g, ' ').trim();
        }

        function renderAvatar(url, name) {
            const safeName = name || 'User';
            if (url) {
                return `
                    <img src="${url}"
                         alt="${safeName}"
                         class="h-full w-full rounded-full object-cover"
                         onerror="this.onerror=null; this.src='/user.png';" />
                `;
            }
            const initials = safeName.trim().substring(0, 2).toUpperCase();
            return `
                <span class="inline-flex h-full w-full items-center justify-center rounded-full bg-gray-200 text-[10px] font-semibold text-gray-700">
                    ${initials}
                </span>
            `;
        }

        // Render a message
        function renderMessage(msg, { prepend = false, animated = true } = {}) {
            const placeholder = document.getElementById('new-conversation-placeholder');
            if (placeholder) placeholder.remove();

            if (msg.id && renderedMessageIds.has(msg.id)) return;
            if (msg.id) renderedMessageIds.add(msg.id);

            const isSystem = msg.type === 'system' || msg.is_system;
            const isMine   = !isSystem && msg.user && msg.user.id === authUserId;

            const wrapper = document.createElement('div');

            if (isSystem) {
                wrapper.className = 'flex justify-center my-1';
                const bubble = document.createElement('div');
                bubble.className = 'message-system message-enter';
                const sysBody = msg.body_html || msg.body || '';
                bubble.innerHTML = sanitizeAllowedHtml(
                    looksLikeHtml(sysBody) ? sysBody : linkify(sysBody)
                );
                wrapper.appendChild(bubble);
                insertMessageElement(wrapper, msg.created_at, { prepend });
                return;
            }

            wrapper.className =
                'flex items-end gap-2 ' + (isMine ? 'justify-end' : 'justify-start');
            if (animated && !prepend) {
                wrapper.classList.add('message-enter');
            }

            const avatar = document.createElement('div');
            avatar.className =
                'message-avatar h-7 w-7 rounded-full overflow-hidden flex items-center justify-center text-[10px] font-semibold shadow';

            const avatarUrl =
                (msg.user && (msg.user.profile_photo_url || msg.user.profile_photo_path)) ?
                (msg.user.profile_photo_url || msg.user.profile_photo_path) :
                null;

            avatar.innerHTML = renderAvatar(avatarUrl, msg.user?.name ?? '??');

            const bubble = document.createElement('div');
            bubble.className =
                'message-bubble group relative max-w-[72%] rounded-2xl px-3 py-2 text-sm shadow ' +
                (isMine
                    ? 'bg-blue-600 text-white rounded-br-sm'
                    : 'bg-white/95 text-gray-900 border border-gray-200/80 rounded-bl-sm backdrop-blur-sm');

            const name = document.createElement('div');
            name.className =
                'text-[11px] font-semibold mb-0.5 opacity-80 flex items-center gap-1';
            name.textContent = msg.user?.name ?? 'Unknown';

            const body = document.createElement('div');
            body.className = 'whitespace-pre-wrap break-words';

            const rawBody = msg.body_html || msg.body || '';
            if (looksLikeHtml(rawBody) || msg.is_html || msg.body_html) {
                body.innerHTML = sanitizeAllowedHtml(rawBody);
            } else {
                body.innerHTML = sanitizeAllowedHtml(linkify(rawBody));
            }

            if (Array.isArray(msg.attachments) && msg.attachments.length) {
                const attsWrap = document.createElement('div');
                attsWrap.className = 'mt-2 space-y-1';
                msg.attachments.forEach(a => {
                    const url   = a.url || a.path || '#';
                    const label = escapeHtml(a.name || a.filename || 'attachment');
                    if ((a.mime || '').startsWith('image/')) {
                        const link = document.createElement('a');
                        link.href = url;
                        link.target = '_blank';
                        link.rel = 'noopener noreferrer';
                        link.className = 'block';
                        const img = document.createElement('img');
                        img.src = url;
                        img.className = 'max-w-[260px] rounded border';
                        link.appendChild(img);
                        attsWrap.appendChild(link);
                    } else {
                        const link = document.createElement('a');
                        link.href = url;
                        link.target = '_blank';
                        link.rel = 'noopener noreferrer';
                        link.className = 'inline-flex items-center gap-2 text-xs text-blue-600 underline';
                        link.innerHTML = `<i class="bi bi-paperclip"></i>${label}`;
                        attsWrap.appendChild(link);
                    }
                });
                body.appendChild(attsWrap);
            }

            const metaRow = document.createElement('div');
            metaRow.className =
                'mt-0.5 flex items-center gap-2 text-[10px] opacity-80 ' +
                (isMine ? 'justify-end' : 'justify-start');

            const time = document.createElement('span');
            time.textContent = new Date(msg.created_at).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });

            const actions = document.createElement('div');
            actions.className =
                'message-actions items-center gap-1 text-[11px] ' +
                (isMine ? 'text-white/80' : 'text-gray-400');

            actions.innerHTML = `
                <button type="button"
                    class="hover:text-amber-300 transition"
                    data-message-action="react"
                    data-message-id="${msg.id}">
                    <i class="bi bi-emoji-smile"></i>
                </button>
            `;

            metaRow.appendChild(time);
            metaRow.appendChild(actions);

            bubble.appendChild(name);
            bubble.appendChild(body);
            bubble.appendChild(metaRow);

            if (isMine) {
                wrapper.appendChild(bubble);
                wrapper.appendChild(avatar);
            } else {
                wrapper.appendChild(avatar);
                wrapper.appendChild(bubble);
            }

            insertMessageElement(wrapper, msg.created_at, { prepend });
        }

        messagesContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-message-action]');
            if (!btn) return;
            const action    = btn.dataset.messageAction;
            const messageId = btn.dataset.messageId;
            if (action === 'react') {
                console.log('React to message', messageId);
            }
        });

        // Load messages
        async function loadMessages(conversationId, beforeId = null) {
            if (loadingMessages) return;
            loadingMessages = true;

            const url = new URL(`/chats/${conversationId}/messages`, window.location.origin);
            if (beforeId) {
                url.searchParams.append('before_id', beforeId);
            }

            try {
                const { data } = await axios.get(url.toString());
                const previousScrollHeight = messagesContainer.scrollHeight;

                if (!beforeId) {
                    clearMessages();
                }

                if (!beforeId && (!data.data || data.data.length === 0)) {
                    showNewConversationPlaceholder();
                    nextPageCursor = null;
                    return;
                }

                data.data.forEach(m => {
                    const prepend = !!beforeId;
                    renderMessage(m, { prepend, animated: !beforeId });
                });

                nextPageCursor = data.next_page;

                if (!beforeId) {
                    scrollToBottom();
                } else {
                    const newScrollHeight = messagesContainer.scrollHeight;
                    messagesContainer.scrollTop = newScrollHeight - previousScrollHeight;
                }
            } catch (e) {
                console.error(e);
            } finally {
                loadingMessages = false;
            }
        }

        // Echo
        function joinConversationChannel(convId) {
            if (!window.Echo) return;

            if (currentEchoChannelName) {
                window.Echo.leave(currentEchoChannelName);
            }

            currentEchoChannelName = `conversations.${convId}`;

            window.Echo
                .private(currentEchoChannelName)
                .listen('.message.sent', (e) => {
                    const msg = e.message;
                    if (msg.user.id === authUserId) return;
                    if (String(msg.conversation_id) !== String(currentConversation)) return;

                    const wasNearBottom = isNearBottom();

                    const placeholder = document.getElementById('new-conversation-placeholder');
                    if (placeholder) placeholder.remove();

                    renderMessage(msg, { animated: true });

                    if (wasNearBottom) {
                        scrollToBottom();
                    } else {
                        newMsgIndicator.classList.remove('hidden');
                        newMsgIndicator.classList.add('bump');
                        setTimeout(() => newMsgIndicator.classList.remove('bump'), 250);
                    }
                });
        }

        // Create or reuse "support room" for current customer
        async function createSupportConversationIfNeeded() {
            if (isSupportUser) return null;

            // If already exists in sidebar, reuse it (guard when no sidebar present for customers)
            const existing = dmList ? dmList.querySelector('.conversation-item') : null;
            if (existing && existing.dataset.conversationId) {
                return existing.dataset.conversationId;
            }

            if (!supportUserIds.length) {
                console.warn('No support users found to create support room.');
                return null;
            }

            try {
                const formData = new FormData();
                formData.append('type', 'group');
                formData.append('name', `Support - ${authUserName}`);

                supportUserIds.forEach(id => formData.append('member_ids[]', id));

                const { data } = await axios.post('/chats/conversations', formData);
                const convId   = data.conversation_id || data.id;

                if (!convId) {
                    console.error('No conversation id returned for support room.');
                    return null;
                }

                const payload = {
                    conversation_id: convId,
                    is_group: !!data.is_group,
                    title: data.title || data.name || 'Support Team',
                    last_message: data.last_message || '',
                    last_at: data.last_at || data.updated_at || null,
                    unread_count: data.unread_count ?? 0,
                    avatar_url: data.avatar_url || null
                };

                upsertConversationInSidebar(payload);

                // Try to select it in the sidebar UI; if sidebar not present (e.g., mobile or no DOM item), set as current and join immediately
                const li = dmList ? dmList.querySelector(`.conversation-item[data-conversation-id="${convId}"]`) : null;
                if (li) {
                    li.classList.add('conversation-item-active');
                    li.click();
                } else {
                    currentConversation = convId;
                    hiddenConvIdInput.value = convId;

                    chatTitle.textContent = payload.title || 'Support Team';
                    chatSubtitle.textContent = 'You are chatting with Support';
                    chatAvatar.className =
                        'h-9 w-9 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white text-xs font-semibold';
                    chatAvatar.innerHTML = '<i class="bi bi-headset"></i>';

                    clearMessages();
                    showMessageSkeleton();
                    enableChatForm(true);
                    await loadMessages(convId);
                    joinConversationChannel(convId);
                }

                return convId;
            } catch (err) {
                console.error('Failed to create support conversation', err);
                return null;
            }
        }

        // Sidebar click
        conversationsRoot.addEventListener('click', (e) => {
            const li = e.target.closest('.conversation-item');
            if (!li) return;

            const convId  = li.dataset.conversationId;
            const title   = li.dataset.conversationTitle;
            const isGroup = li.dataset.isGroup === '1';

            currentConversation     = convId;
            hiddenConvIdInput.value = convId;

            chatTitle.textContent = title || (isSupportUser ? 'Customer' : 'Support Team');
            chatSubtitle.textContent = isSupportUser
                ? 'Customer support conversation'
                : 'You are chatting with Support';

            if (isSupportUser) {
                chatAvatar.className =
                    'h-9 w-9 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center text-blue-600';
                chatAvatar.innerHTML = '<i class="bi bi-person-fill"></i>';
            } else {
                chatAvatar.className =
                    'h-9 w-9 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white text-xs font-semibold';
                chatAvatar.innerHTML = '<i class="bi bi-headset"></i>';
            }

            conversationsRoot.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('conversation-item-active');
            });
            li.classList.add('conversation-item-active');

            const badge = li.querySelector('.conv-unread-badge');
            if (badge) {
                badge.classList.add('hidden');
                badge.textContent = '';
            }

            axios.post(`/chats/${convId}/read`).catch(err => console.error(err));

            clearMessages();
            showMessageSkeleton();
            nextPageCursor = null;
            if (emptyState) emptyState.remove();
            enableChatForm(true);
            newMsgIndicator.classList.add('hidden');

            loadMessages(convId);
            joinConversationChannel(convId);
            if (typeof closeSidebarMobile === 'function') closeSidebarMobile();
        });

        // New messages indicator
        newMsgIndicator.addEventListener('click', () => {
            scrollToBottom();
            newMsgIndicator.classList.add('hidden');
        });

        // Scroll events
        messagesContainer.addEventListener('scroll', () => {
            if (isNearBottom()) {
                newMsgIndicator.classList.add('hidden');
            }

            if (!currentConversation) return;

            if (messagesContainer.scrollTop === 0 && nextPageCursor && !loadingMessages) {
                loadMessages(currentConversation, nextPageCursor);
            }
        });

        // Editor events
        editor.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit', { cancelable: true }));
            }
        });

        // Attachments
        btnAttach.addEventListener('click', () => fileInput.click());
        btnImage.addEventListener('click', () => imgInput.click());

        fileInput.addEventListener('change', (e) => {
            addFiles(Array.from(e.target.files || []));
            fileInput.value = '';
        });

        imgInput.addEventListener('change', (e) => {
            addFiles(Array.from(e.target.files || []));
            imgInput.value = '';
        });

        editor.addEventListener('paste', (e) => {
            const items = (e.clipboardData || {}).items || [];
            const files = [];
            for (const it of items) {
                if (it.kind === 'file') files.push(it.getAsFile());
            }
            if (files.length) {
                e.preventDefault();
                addFiles(files);
            }
        });

        editor.addEventListener('dragover', (e) => {
            const dt = e.dataTransfer;
            const hasFiles = dt && dt.types && Array.from(dt.types).includes('Files');
            if (hasFiles) {
                e.preventDefault();
                try { dt.dropEffect = 'copy'; } catch {}
            }
        });

        editor.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = Array.from((dt && dt.files) || []);
            if (files.length) {
                e.preventDefault();
                addFiles(files);
            }
        });

        function addFiles(files) {
            files.forEach(f => {
                if (f && f.size) pendingFiles.push(f);
            });
            refreshPreview();
        }

        function refreshPreview() {
            if (!pendingFiles.length) {
                pendingPreview.classList.add('hidden');
                pendingPreview.innerHTML = '';
                return;
            }
            const html = pendingFiles.map((f, i) => {
                const isImg  = f.type.startsWith('image/');
                const sizeKB = Math.ceil(f.size / 1024);
                return `
                    <div class="att-chip" data-index="${i}">
                        <i class="bi ${isImg ? 'bi-image' : 'bi-paperclip'}"></i>
                        <span class="max-w-48 truncate">${escapeHtml(f.name)} (${sizeKB} KB)</span>
                        <button type="button" class="text-red-600 hover:text-red-700" data-remove="${i}" title="Remove">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `;
            }).join('');
            pendingPreview.classList.remove('hidden');
            pendingPreview.innerHTML = html;
        }

        pendingPreview.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-remove]');
            if (!btn) return;
            const i = Number(btn.dataset.remove);
            if (!Number.isNaN(i)) {
                pendingFiles.splice(i, 1);
                refreshPreview();
            }
        });

        // Emoji picker
        btnEmoji.addEventListener('click', () => {
            emojiPicker.classList.toggle('hidden');
        });

        emojiPicker.addEventListener('click', (e) => {
            const btn = e.target.closest('.emoji-item');
            if (!btn) return;
            insertEmoji(btn.textContent || '');
        });

        document.addEventListener('click', (e) => {
            if (!emojiPicker.contains(e.target) && e.target !== btnEmoji) {
                emojiPicker.classList.add('hidden');
            }
        });

        function insertEmoji(emoji) {
            editor.focus();
            document.execCommand('insertText', false, emoji);
        }

        // Send message
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            let convId = hiddenConvIdInput.value;

            // For customers: if no conversation, create support room first
            if ((!convId || !currentConversation) && !isSupportUser) {
                try {
                    const newId = await createSupportConversationIfNeeded();
                    if (!newId) {
                        console.warn('Unable to create support room, aborting send.');
                        return;
                    }
                    convId = newId;
                    currentConversation     = newId;
                    hiddenConvIdInput.value = newId;

                    const li = dmList ? dmList.querySelector(
                        `.conversation-item[data-conversation-id="${newId}"]`
                    ) : null;
                    if (li) {
                        li.classList.add('conversation-item-active');
                        li.dispatchEvent(new Event('click', { bubbles: true }));
                    }
                } catch (err) {
                    console.error('Error creating support room before send', err);
                    return;
                }
            }

            if (!convId || !currentConversation) return;
            if (sending) return;

            const rawHtml   = editor.innerHTML;
            const source    = looksLikeHtml(rawHtml) ? rawHtml : linkify(rawHtml);
            const bodyHtml  = sanitizeAllowedHtml(source);
            const bodyEmpty = isEmptyHtml(bodyHtml);

            if (bodyEmpty && pendingFiles.length === 0) return;

            sending = true;
            toggleSending(true);

            try {
                const formData = new FormData();
                formData.append('type', 'text');
                formData.append('body', bodyEmpty ? '' : bodyHtml);
                formData.append('is_html', '1');

                pendingFiles.forEach((f) => formData.append('attachments[]', f, f.name));

                const { data: msg } = await axios.post(`/chats/${convId}/messages`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                editor.innerHTML = '';
                pendingFiles = [];
                refreshPreview();

                const wasNearBottom = isNearBottom();
                renderMessage(msg, { animated: true });

                if (wasNearBottom) {
                    scrollToBottom();
                }
            } catch (err) {
                console.error(err);
            } finally {
                sending = false;
                toggleSending(false);
            }
        });

        function toggleSending(on) {
            sendBtn.disabled = on;
        }

        // Search filter
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                conversationsRoot.querySelectorAll('.conversation-item').forEach(li => {
                    const title = li.querySelector('.conv-title')?.textContent.toLowerCase() ?? '';
                    const last  = li.querySelector('.conv-last-message')?.textContent.toLowerCase() ?? '';
                    li.classList.toggle('hidden', !(title.includes(q) || last.includes(q)));
                });
            });
        }

        // Echo ready
        function onEchoReady(callback) {
            if (window.Echo) {
                callback(window.Echo);
                return;
            }

            let attempts = 0;
            const maxAttempts = 50;

            const interval = setInterval(() => {
                if (window.Echo) {
                    clearInterval(interval);
                    callback(window.Echo);
                } else if (attempts++ > maxAttempts) {
                    clearInterval(interval);
                    console.error('Echo did not initialize in time');
                }
            }, 100);
        }

        // Mobile sidebar helpers (support users only)
        const mobileSidebarBackdrop = document.getElementById('mobile-sidebar-backdrop');
        const btnOpenSidebarMobile = document.getElementById('btn-open-sidebar-mobile');
        const sidebarEl = document.getElementById('sidebar');

        function openSidebarMobile() {
            if (!sidebarEl || !mobileSidebarBackdrop) return;
            sidebarEl.classList.remove('hidden');
            sidebarEl.classList.add('fixed','left-0','top-0','bottom-0','block','z-40','w-80','overflow-auto','rounded-none','shadow-lg');
            mobileSidebarBackdrop.classList.remove('hidden');
        }

        function closeSidebarMobile() {
            if (!sidebarEl || !mobileSidebarBackdrop) return;
            sidebarEl.classList.add('hidden');
            sidebarEl.classList.remove('fixed','left-0','top-0','bottom-0','block','z-40','w-80','overflow-auto','rounded-none','shadow-lg');
            mobileSidebarBackdrop.classList.add('hidden');
        }

        if (btnOpenSidebarMobile) {
            btnOpenSidebarMobile.addEventListener('click', openSidebarMobile);
        }
        if (mobileSidebarBackdrop) {
            mobileSidebarBackdrop.addEventListener('click', closeSidebarMobile);
        }

        // Sidebar upsert
        function upsertConversationInSidebar(payload) {
            const convId = payload.conversation_id;
            const list   = dmList;
            if (!list) return;

            let item = list.querySelector(`[data-conversation-id="${convId}"]`);

            if (!item) {
                item = document.createElement('li');
                item.className = 'conversation-item cursor-pointer transition-colors';
                item.dataset.conversationId    = convId;
                item.dataset.isGroup           = payload.is_group ? '1' : '0';
                item.dataset.conversationTitle = payload.title ?? (isSupportUser ? 'Customer' : 'Support Team');

                item.innerHTML = `
                    <div class="flex items-center justify-between px-3 py-2 hover:bg-gray-100/80">
                        <div class="flex items-center gap-2">
                            <div class="avatar-icon h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-[11px] font-semibold text-gray-700 shadow overflow-hidden"></div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1">
                                    <span class="font-semibold text-xs truncate max-w-[140px] conv-title"></span>
                                </div>
                                <div class="text-[11px] text-gray-500 truncate conv-last-message"></div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="text-[10px] text-gray-400 conv-last-at"></span>
                            <span class="conv-unread-badge hidden rounded-full bg-red-600 text-white text-[10px] px-2 py-0.5 shadow"></span>
                        </div>
                    </div>
                `;
            }

            item.dataset.conversationTitle = payload.title ?? (isSupportUser ? 'Customer' : 'Support Team');

            const titleEl   = item.querySelector('.conv-title');
            const lastMsgEl = item.querySelector('.conv-last-message');
            const lastAtEl  = item.querySelector('.conv-last-at');
            const badgeEl   = item.querySelector('.conv-unread-badge');
            const avatarEl  = item.querySelector('.avatar-icon');

            if (titleEl) titleEl.textContent = payload.title ?? (isSupportUser ? 'Customer' : 'Support Team');
            if (lastMsgEl) {
                const rawLast = payload.last_message ?? '';
                lastMsgEl.textContent = htmlToPlainText(rawLast);
            }

            if (lastAtEl) {
                lastAtEl.textContent = payload.last_at
                    ? new Date(payload.last_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    : '';
            }

            const unread = payload.unread_count ?? 0;
            if (unread > 0 && String(convId) !== String(currentConversation)) {
                badgeEl.textContent = unread;
                badgeEl.classList.remove('hidden');
            } else {
                badgeEl.classList.add('hidden');
            }

            if (avatarEl) {
                const url = payload.avatar_url || item.getAttribute('data-avatar-url') || null;
                avatarEl.className =
                    'avatar-icon h-8 w-8 rounded-full overflow-hidden flex items-center justify-center shadow';
                avatarEl.innerHTML = renderAvatar(url, payload.title ?? (isSupportUser ? 'Customer' : 'Support Team'));
            }

            list.prepend(item);
            item.classList.add('sidebar-item-updated');
            setTimeout(() => item.classList.remove('sidebar-item-updated'), 700);
        }

        // Subscribe to per-user sidebar events
        onEchoReady((Echo) => {
            Echo.private(`users.${authUserId}`)
                .listen('.conversation.updated', (e) => {
                    upsertConversationInSidebar(e);
                });
        });

        // Auto-open logic
        document.addEventListener('DOMContentLoaded', async function () {
            if (isSupportUser) {
                let targetLi = null;
                if (autoOpenConversationId) {
                    targetLi = dmList.querySelector(
                        `.conversation-item[data-conversation-id="${autoOpenConversationId}"]`
                    );
                }
                if (!targetLi) {
                    targetLi = dmList.querySelector('.conversation-item');
                }
                if (targetLi) {
                    targetLi.click();
                }
            } else {
                try {
                    const convId = await createSupportConversationIfNeeded();
                    let targetLi  = null;

                    if (dmList) {
                        if (convId) {
                            targetLi = dmList.querySelector(
                                `.conversation-item[data-conversation-id="${convId}"]`
                            );
                        }
                        if (!targetLi) {
                            targetLi = dmList.querySelector('.conversation-item');
                        }
                    }

                    if (targetLi) {
                        targetLi.click();
                    } else {
                        enableChatForm(true);
                    }
                } catch (err) {
                    console.error('Auto support room open failed', err);
                }
            }
        });
    </script>
</x-app-layout>
