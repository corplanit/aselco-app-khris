<div id="mobileConversationView" class="mobile-conversation-view" style="display: none;">
    <div class="mobile-conversation-wrapper bg-white" style="height: 100vh; display: flex; flex-direction: column;">
        <div class="mobile-conv-header bg-white border-b border-defaultborder dark:border-defaultborder/10">
            <div class="flex items-center gap-3 p-4">
                <button onclick="closeMobileConversation()" class="p-2 -ml-2">
                    <i class="ri-arrow-left-line text-xl"></i>
                </button>
                
                <div id="mobileConvAvatarContainer">
                    <img id="mobileConvAvatar" src="/user.png" class="w-10 h-10 rounded-full object-cover" alt="User">
                </div>
                
                <div class="flex-1 min-w-0">
                    <div id="mobileConvTitle" class="font-semibold text-base truncate">—</div>
                    <div id="mobileConvSubtitle" class="text-xs text-textmuted dark:text-textmuted/50 truncate">—</div>
                </div>
                
                <button onclick="openMobileConvDetails()" class="p-2">
                    <i class="ri-more-2-fill text-xl"></i>
                </button>
            </div>
            
            <div id="mobileHeaderActions" class="flex items-center gap-2 px-4 pb-3 hidden">
                <button onclick="openMobileConvDetails()" class="flex-1 py-2 px-3 border border-defaultborder rounded-md flex items-center justify-center gap-2 text-sm">
                    <i class="ri-calendar-line"></i> Schedules
                </button>
                <button id="mobile-meeting-btn" onclick="showMobileMeetingUI()" class="flex-1 py-2 px-3 border border-defaultborder rounded-md flex items-center justify-center gap-2 text-sm">
                    <i class="ri-vidicon-line"></i> 
                    <span id="mobile-meeting-indicator" class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    Meeting
                </button>
            </div>
        </div>

        <div id="mobile-chat-messages" class="flex-1 px-4 py-4" style="background: linear-gradient(to bottom, #ffffff 0%, #f8faff 100%); overflow-y: auto; -webkit-overflow-scrolling: touch;">
            <!-- Messages will be loaded here -->
        </div>

        <div class="mobile-input-area bg-white border-t border-defaultborder dark:border-defaultborder/10 p-4">
            <div class="flex items-end gap-2">
                <button id="mobile-btn-attach" type="button" class="p-2 rounded hover:bg-gray-100">
                    <i class="ri-attachment-2 text-xl text-gray-600"></i>
                </button>
                
                <button id="mobile-btn-image" type="button" class="p-2 rounded hover:bg-gray-100">
                    <i class="ri-image-line text-xl text-gray-600"></i>
                </button>
                
                <input id="mobile-file-input" type="file" multiple class="hidden">
                <input id="mobile-img-input" type="file" accept="image/*" multiple class="hidden">
                
                <div id="mobile-editor" 
                     class="flex-1 min-h-[36px] max-h-32 overflow-y-auto px-3 py-2 border border-defaultborder rounded-lg outline-none text-sm"
                     contenteditable="true" 
                     role="textbox" 
                     aria-multiline="true"
                     data-placeholder="Write a message..."></div>
                
                <button id="mobile-btn-send" class="p-2 rounded-lg bg-primary text-white">
                    <i class="ri-send-plane-fill text-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.mobile-conversation-view {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1000;
    background: white;
}

#mobile-editor:empty:before {
    content: attr(data-placeholder);
    color: #9ca3af;
}

#mobile-editor:focus:before {
    content: '';
}

.mobile-conversation-open .footer-nav-area,
.mobile-conversation-open #footerNav,
.mobile-conversation-open .header-area {
    display: none !important;
}

.mobile-conversation-open {
    overflow: hidden;
}

.bubble {
    position: relative;
    max-width: 46rem;
    padding: 10px 14px;
    line-height: 1.55;
    word-wrap: break-word;
    border-radius: 12px;
}

.bubble.ai {
    background: #EEF2FF;
    color: #0F172A;
}

.bubble.me {
    background: #2563EB;
    color: #fff;
}

.stamp {
    font-size: .70rem;
    color: rgb(148 163 184);
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

<script>
let mobileActiveConvId = null;

function openMobileConversation(convId) {
    mobileActiveConvId = convId;
    const view = document.getElementById('mobileConversationView');
    const listView = document.querySelector('.mobile-chat-wrapper');
    
    if (view && listView) {
        view.style.display = 'block';
        listView.style.display = 'none';
        document.body.style.overflow = 'hidden';
        document.body.classList.add('mobile-conversation-open');

        localStorage.setItem('mobileActiveConvId', convId);
        
        loadMobileConversationData(convId);
    }
}

function loadMobileConversationData(convId) {
    const skeletonHTML = `
        <div class="mobile-message-skeleton">
            ${Array(5).fill(0).map((_, i) => {
                const isRight = i % 3 === 0;
                return `
                    <div class="flex ${isRight ? 'justify-end' : 'justify-start'} mb-4 px-4">
                        ${!isRight ? '<div class="w-8 h-8 bg-gray-200 rounded-full mr-2 animate-pulse"></div>' : ''}
                        <div class="max-w-[70%]">
                            ${!isRight ? '<div class="h-3 bg-gray-200 rounded mb-2 animate-pulse" style="width: 80px;"></div>' : ''}
                            <div class="bg-gray-200 rounded-lg p-3 animate-pulse">
                                <div class="h-3 bg-gray-300 rounded mb-2" style="width: ${120 + Math.random() * 100}px;"></div>
                                <div class="h-3 bg-gray-300 rounded" style="width: ${80 + Math.random() * 80}px;"></div>
                            </div>
                            <div class="h-2 bg-gray-200 rounded mt-1 animate-pulse" style="width: 60px;"></div>
                        </div>
                        ${isRight ? '<div class="w-8 h-8 bg-gray-200 rounded-full ml-2 animate-pulse"></div>' : ''}
                    </div>
                `;
            }).join('')}
        </div>
    `;
    $('#mobile-chat-messages').html(skeletonHTML);
    
    $.ajax({
        url: `/chats/conversations/${convId}/messages`,
        method: 'GET',
        success: function(response) {
            console.log('Mobile messages response:', response);
            const data = Array.isArray(response) ? response : (response.data || []);
            $('#mobile-chat-messages').empty();
            
            if (data && data.length > 0) {
                const messages = [...data].reverse();
                messages.forEach(msg => {
                    const isSystem = msg.type === 'system';
                    
                    if (isSystem) {
                        const systemMsg = `
                            <div class="text-center my-3">
                                <span class="text-xs text-gray-400 italic">${msg.body || ''}</span>
                            </div>
                        `;
                        $('#mobile-chat-messages').append(systemMsg);
                    } else {
                        const isOwn = msg.user_id == {{ auth()->id() }};
                        const userName = msg.user?.name || msg.user_name || 'User';
                        const userAvatar = msg.user?.profile_photo_path ? `/storage/${msg.user.profile_photo_path}` : '/user.png';
                        
                        const bubble = `
                            <div class="${isOwn ? 'pl-12 pr-16' : 'pl-12 pr-16'} mb-4">
                                <div class="flex items-end gap-3 ${isOwn ? 'justify-end' : ''}">
                                    ${!isOwn ? `<img src="${userAvatar}" onerror="this.src='/user.png'" class="w-8 h-8 rounded-full object-cover -mb-1" alt="${userName}">` : ''}
                                    <div class="min-w-0">
                                        ${!isOwn ? `<div class="text-xs font-semibold text-gray-700 mb-1">${userName}</div>` : ''}
                                        <div class="bubble ${isOwn ? 'me' : 'ai'}">
                                            <div>${msg.body || ''}</div>
                                        </div>
                                        <div class="mt-1 flex items-center gap-2 text-xs">
                                            <span class="stamp">${formatMessageTime(msg.created_at)}</span>
                                        </div>
                                    </div>
                                    ${isOwn ? `<img src="${userAvatar}" onerror="this.src='/user.png'" class="w-8 h-8 rounded-full object-cover -mb-1" alt="You">` : ''}
                                </div>
                            </div>
                        `;
                        $('#mobile-chat-messages').append(bubble);
                    }
                });
            } else {
                $('#mobile-chat-messages').html('<div class="text-center py-8 text-gray-400">No messages yet</div>');
            }
            
            scrollMobileChatToBottom();
            loadConversationMeta(convId);
        },
        error: function(xhr) {
            console.error('Failed to load messages:', xhr.status, xhr.responseText);
            $('#mobile-chat-messages').html('<div class="text-center py-8 text-red-500">Failed to load messages. Check console.</div>');
        }
    });
}

function formatMessageTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function loadConversationMeta(convId) {
    $.getJSON('/chats/conversations', function(res) {
        const conversations = Array.isArray(res) ? res : (res.data || []);
        const conv = conversations.find(c => c.id == convId);
        if (conv) {
            let title = 'Conversation';
            if (conv.type === 'group') {
                title = conv.name || 'Group';
                $('#mobileHeaderActions').removeClass('hidden');
            } else if (conv.type === 'dm' && conv.users) {
                const peer = conv.users.find(u => u.id != {{ auth()->id() }});
                title = peer ? peer.name : 'User';
                $('#mobileHeaderActions').addClass('hidden');
            }
            
            $('#mobileConvTitle').text(title);
            
            if (conv.type === 'group') {
                const memberCount = conv.users ? conv.users.length : 0;
                if (memberCount > 0) {
                    $('#mobileConvSubtitle').html(`${memberCount} member${memberCount !== 1 ? 's' : ''}`);
                } else {
                    $('#mobileConvSubtitle').html('Group conversation');
                }
            } else {
                $('#mobileConvSubtitle').html('Online');
            }
            
            if (conv.type === 'group') {
                const initials = (conv.name || 'GC').substring(0, 2).toUpperCase();
                $('#mobileConvAvatarContainer').html(`
                    <span class="avatar avatar-md bg-gray-200 text-gray-600 rounded-full flex items-center justify-center" style="width: 40px; height: 40px;">
                        <span class="font-semibold">${initials}</span>
                    </span>
                `);
            } else if (conv.users) {
                const peer = conv.users.find(u => u.id != {{ auth()->id() }});
                const avatar = peer?.profile_photo_path ? `/storage/${peer.profile_photo_path}` : '/user.png';
                $('#mobileConvAvatarContainer').html(`
                    <img src="${avatar}" onerror="this.src='/user.png'" class="w-10 h-10 rounded-full object-cover" alt="${title}">
                `);
            }
        }
    });
}

function closeMobileConversation() {
    const view = document.getElementById('mobileConversationView');
    const listView = document.querySelector('.mobile-chat-wrapper');
    
    if (view && listView) {
        view.style.display = 'none';
        listView.style.display = 'block';
        document.body.style.overflow = '';
        document.body.classList.remove('mobile-conversation-open');
        mobileActiveConvId = null;
        
        localStorage.removeItem('mobileActiveConvId');
    }
}

function scrollMobileChatToBottom() {
    const container = document.getElementById('mobile-chat-messages');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function openMobileConvDetails() {
    const detailsBtn = document.querySelector('[data-hs-overlay="#offcanvasDetails"]');
    if (detailsBtn) {
        detailsBtn.click();
    } else {
        alert('later');
    }
}

function showMobileMeetingUI() {
    const isMobile = window.innerWidth <= 768;
    const joinBtn = document.getElementById('joinBtnLate');
    
    if (isMobile && joinBtn) {
        const room = window.ACTIVE_CONVERSATION || 'SupportRoom';
        const name = joinBtn.dataset.name || '{{ auth()->user()->name }}';
        const email = joinBtn.dataset.email || '{{ auth()->user()->email }}';
        const avatar = joinBtn.dataset.avatar || '{{ auth()->user()->profile_photo_path ? asset("storage/" . auth()->user()->profile_photo_path) : asset("/assets/raw/watermark.svg") }}';
        
        const url = typeof buildJitsiUrl === 'function' 
            ? buildJitsiUrl(room, name, email, avatar)
            : `https://meet.iosbiz.com/${encodeURIComponent(room)}#userInfo.displayName=${encodeURIComponent(name)}`;
        
        window.open(url, '_blank');
        
        joinBtn.click();
    } else {
        const headerCall = document.getElementById('header-call');
        if (headerCall) {
            headerCall.classList.add('show');
            headerCall.classList.remove('hidden');
        }
        
        if (joinBtn) {
            joinBtn.classList.remove('hidden');
            joinBtn.click();
        }
    }
    
    const meetingBtn = document.getElementById('mobile-meeting-btn');
    if (meetingBtn) {
        meetingBtn.classList.add('hidden');
    }
}

$(document).ready(function() {
    const savedConvId = localStorage.getItem('mobileActiveConvId');
    if (savedConvId) {
        setTimeout(() => {
            openMobileConversation(parseInt(savedConvId));
        }, 300);
    }
    
    $('#mobile-btn-send').click(function() {
        const editor = document.getElementById('mobile-editor');
        const message = editor.innerText.trim();
        
        if (message && mobileActiveConvId) {
            $.ajax({
                url: `/chats/conversations/${mobileActiveConvId}/messages`,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
                data: {body: message},
                success: function() {
                    editor.innerText = '';
                    loadMobileConversationData(mobileActiveConvId);
                },
                error: function() {
                    alert('Failed to send message');
                }
            });
        }
    });
    
    $('#mobile-btn-attach').click(() => $('#mobile-file-input').click());
    $('#mobile-btn-image').click(() => $('#mobile-img-input').click());
    
    $('#mobile-editor').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $('#mobile-btn-send').click();
        }
    });
});
</script>
