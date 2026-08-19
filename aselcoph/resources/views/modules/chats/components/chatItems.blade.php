<div id="chat-messages" class="flex-1 overflow-y-auto p-6 space-y-3"></div>

<script>
    function scrollChatToBottom() {
        const container = document.getElementById('chat-messages');
        container.scrollTop = container.scrollHeight;
    }

    // Initial scroll on page load
    scrollChatToBottom();

    // Scroll whenever new message is added
    const chatContainer = document.getElementById('chat-messages');
    const observer = new MutationObserver(() => {
        scrollChatToBottom();
    });
    observer.observe(chatContainer, { childList: true, subtree: true });
</script>

@include('modules.chats.hook.conversation-hook') 
@include('modules.chats.hook.pre-loading')