<div id="mobileConvListContainer">
    <div id="mobileDmList" class="px-0 py-4">
        <div class="flex items-center justify-between mb-3 px-4">
            <p class="text-xs text-textmuted dark:text-textmuted/50 font-medium uppercase mb-0">DIRECT MESSAGES</p>
            <span id="mobileDmCount" class="text-xs text-textmuted dark:text-textmuted/50">0</span>
        </div>
        <div id="mobileDmItems"></div>
    </div>

    <div id="mobileGroupList" class="px-0 py-4 border-t border-defaultborder dark:border-defaultborder/10">
        <div id="mobileGroupListHeader" class="flex items-center justify-between mb-3 px-4">
            <p id="mobileGroupListTitle" class="text-xs text-textmuted dark:text-textmuted/50 font-medium uppercase mb-0">GROUPS</p>
            <span id="mobileGroupCount" class="text-xs text-textmuted dark:text-textmuted/50">0</span>
        </div>
        <div id="mobileGroupItems"></div>
    </div>
</div>

<style>
.mobile-conv-skeleton {
    animation: mobile-skeleton-pulse 1.5s ease-in-out infinite;
}

@keyframes mobile-skeleton-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.mobile-conv-item {
    transition: background-color 0.2s ease;
}

.mobile-conv-item:active {
    background-color: #f3f4f6;
}

.mobile-conv-item.unread {
    background-color: #eff6ff;
}
</style>

<script>
window.AUTH_ID = window.AUTH_ID || {{ auth()->id() }};

let isFirstLoad = true;
let allConversations = [];  
let currentFilter = 'all';  

function loadMobileConversations() {
    // Only show skeleton on first load
    if (isFirstLoad) {
        showMobileConvSkeleton();
    }
    
    $.getJSON('/chats/conversations', function(res) {
        console.log('Mobile conversations loaded:', res);
        const conversations = Array.isArray(res) ? res : (res.data || []);
        console.log('Parsed conversations:', conversations);
        

        allConversations = conversations;
        
        if (isFirstLoad) {
            hideMobileConvSkeleton();
            isFirstLoad = false;
        }
        renderMobileConversations(conversations);
    }).fail(function(xhr) {
        console.error('Failed to load mobile conversations:', xhr.status, xhr.responseText);
        hideMobileConvSkeleton();
        $('#mobileDmItems').html('<div class="text-center py-4 px-4 text-sm text-gray-500">Failed to load. Check console.</div>');
    });
}

// Filter switching function
function switchMobileFilter(filter) {
    currentFilter = filter;
    
    $('.mobile-filter-tab').removeClass('active');
    $(`.mobile-filter-tab[data-filter="${filter}"]`).addClass('active');
    
    renderMobileConversations(allConversations);
}

window.switchMobileFilter = switchMobileFilter;

function showMobileConvSkeleton() {
    const skeletonHtml = Array(3).fill(0).map(() => `
        <div class="mobile-conv-skeleton px-4 py-3">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                <div class="flex-1 min-w-0">
                    <div class="h-4 bg-gray-200 rounded mb-2" style="width: 60%;"></div>
                    <div class="h-3 bg-gray-200 rounded" style="width: 80%;"></div>
                </div>
                <div class="h-3 bg-gray-200 rounded" style="width: 60px;"></div>
            </div>
        </div>
    `).join('');
    
    $('#mobileDmItems').html(skeletonHtml);
    $('#mobileGroupItems').html(skeletonHtml);
}

function hideMobileConvSkeleton() {
    $('.mobile-conv-skeleton').remove();
}

function getDmPeerName(conv) {
    if (!conv.users || conv.users.length < 2) return 'User';
    const peer = conv.users.find(u => u.id != window.AUTH_ID);
    return peer ? peer.name : 'User';
}

function formatMobileTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h ago`;
    
    const options = { month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function renderMobileConversations(conversations) {
    const dms = conversations.filter(c => c.type === 'dm');
    const groups = conversations.filter(c => c.type === 'group');
    
    // Update counts
    $('#mobileDmCount').text(dms.length);
    $('#mobileGroupCount').text(groups.length);
    
    // Handle different filter views
    if (currentFilter === 'groups') {
        // Hide DMs section, show only groups
        $('#mobileDmList').hide();
        $('#mobileGroupList').show().removeClass('border-t');
        
        // Reset header to "GROUPS"
        $('#mobileGroupListTitle').text('GROUPS');
        $('#mobileGroupCount').text(groups.length);
        
        if (groups.length === 0) {
            $('#mobileGroupItems').html('<div class="text-center py-4 px-4 text-sm text-textmuted">No groups yet.</div>');
        } else {
            $('#mobileGroupItems').html(groups.map(c => createMobileConvItem(c)).join(''));
        }
    } else if (currentFilter === 'unread') {
        // Show unread (mixed - no sections)
        $('#mobileDmList').hide();
        $('#mobileGroupList').show().removeClass('border-t');
        $('#mobileGroupListTitle').text('UNREAD');
        
        const unreadConversations = conversations.filter(c => c.unread_count > 0);
        $('#mobileGroupCount').text(unreadConversations.length);
        
        const sortedUnread = [...unreadConversations].sort((a, b) => {
            const aTime = (a.messages && a.messages.length) ? new Date(a.messages[0].created_at) : new Date(0);
            const bTime = (b.messages && b.messages.length) ? new Date(b.messages[0].created_at) : new Date(0);
            return bTime - aTime;
        });
        
        if (sortedUnread.length === 0) {
            $('#mobileGroupItems').html('<div class="text-center py-4 px-4 text-sm text-textmuted">No unread conversations.</div>');
        } else {
            $('#mobileGroupItems').html(sortedUnread.map(c => createMobileConvItem(c)).join(''));
        }
    } else {
        // Show all (mixed - no sections)
        $('#mobileDmList').hide();
        $('#mobileGroupList').show().removeClass('border-t');
        $('#mobileGroupListTitle').text('CONVERSATIONS');
        $('#mobileGroupCount').text(conversations.length);
        
        const sortedConversations = [...conversations].sort((a, b) => {
            const aTime = (a.messages && a.messages.length) ? new Date(a.messages[0].created_at) : new Date(0);
            const bTime = (b.messages && b.messages.length) ? new Date(b.messages[0].created_at) : new Date(0);
            return bTime - aTime;
        });
        
        if (sortedConversations.length === 0) {
            $('#mobileGroupItems').html('<div class="text-center py-4 px-4 text-sm text-textmuted">No conversations yet.</div>');
        } else {
            $('#mobileGroupItems').html(sortedConversations.map(c => createMobileConvItem(c)).join(''));
        }
    }
}

function createMobileConvItem(conv) {
    const isUnread = conv.unread_count > 0;
    const avatar = conv.avatar || '/user.png';
    
    let title = 'Conversation';
    if (conv.type === 'group') {
        title = conv.name || 'Group';
    } else if (conv.type === 'dm') {
        title = getDmPeerName(conv) || 'User';
    }
    
    const latestMsg = (conv.messages && conv.messages.length) ? conv.messages[0] : null;
    const lastMessage = latestMsg ? (latestMsg.body || '') : '';
    const time = latestMsg ? formatMobileTime(latestMsg.created_at) : '';
    const initials = conv.name ? conv.name.substring(0, 2).toUpperCase() : 'GC';
    
    return `
        <div class="mobile-conv-item ${isUnread ? 'unread' : ''} px-4 py-3 cursor-pointer" 
             onclick="openMobileConversation(${conv.id})" 
             data-conv-id="${conv.id}">
            <div class="flex items-start gap-3"> 
                ${conv.type === 'group' ? 
                    `<span class="avatar avatar-md bg-blue-200 text-blue-600 flex-shrink-0 flex items-center justify-center" style="width: 48px; height: 48px; border-radius: 50%;">
                        <span class="font-semibold">${initials}</span>
                    </span>` :
                    `<img src="${avatar}" 
                         onerror="this.src='/user.png'" 
                         class="w-12 h-12 rounded-full object-cover flex-shrink-0" 
                         alt="${title}">`
                }
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between mb-1">
                        <p class="font-semibold text-sm mb-0 truncate ${isUnread ? 'text-primary' : ''}">${title}</p>
                        <span class="text-xs text-textmuted dark:text-textmuted/50 whitespace-nowrap ml-2">${time}</span>
                    </div>
                    <p class="text-xs text-textmuted dark:text-textmuted/50 mb-0 truncate">${lastMessage}</p>
                    ${isUnread ? `<span class="inline-block mt-1 px-2 py-0.5 text-xs bg-primary text-white rounded-full">${conv.unread_count} new</span>` : ''}
                </div>
            </div>
        </div>
    `;
}

$(document).ready(function() {
    if (typeof $ !== 'undefined') {
        setTimeout(function() {
            loadMobileConversations();
        }, 100);
        
        setInterval(function() {
            if (typeof mobileActiveConvId !== 'undefined' && !mobileActiveConvId) {
                $.getJSON('/chats/conversations', function(res) {
                    const conversations = Array.isArray(res) ? res : (res.data || []);
                    allConversations = conversations;
                    renderMobileConversations(conversations);
                    updateChatBadge(conversations);
                });
            }
        }, 10000);
    }
    
    // Update chat badge with unseen message count
    window.updateChatBadge = function(conversations) {
        const unseenConversations = getUnseenConversations(conversations);
        const badge = document.getElementById('chatBadge');
        
        if (badge && unseenConversations.length > 0) {
            badge.textContent = unseenConversations.length;
            badge.style.display = 'inline-block';
        } else if (badge) {
            badge.style.display = 'none';
        }
    }
    
    function getUnseenConversations(conversations) {
        const seenConvIds = JSON.parse(localStorage.getItem('seenConversations') || '[]');
        return conversations.filter(conv => {
            const hasUnread = conv.unread_count && conv.unread_count > 0;
            const isSeen = seenConvIds.includes(conv.id);
            return hasUnread && !isSeen;
        });
    }
    
    window.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        if (currentPath === '/chats/message') {
            if (typeof allConversations !== 'undefined') {
                const seenConvIds = JSON.parse(localStorage.getItem('seenConversations') || '[]');
                allConversations.forEach(conv => {
                    if (!seenConvIds.includes(conv.id)) {
                        seenConvIds.push(conv.id);
                    }
                });
                localStorage.setItem('seenConversations', JSON.stringify(seenConvIds));
                updateChatBadge(allConversations);
            }
        }
    });
});
</script>
