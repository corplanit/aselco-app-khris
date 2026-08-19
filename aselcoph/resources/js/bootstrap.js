// --------------------
// Axios Setup
// --------------------
import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios = axios;

// --------------------
// Laravel Echo + Pusher
// --------------------
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Production-ready Echo instance
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,                    // Your Pusher key
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'ap1',   // Default cluster
    forceTLS: (import.meta.env.VITE_PUSHER_USETLS ?? 'true') === 'true',
    encrypted: true,                                             // Always encrypt
    authEndpoint: '/broadcasting/auth',                          // Private channel auth
    auth: {
        headers: {
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content'),
        },
    },
    // Optional: fallback transports for older browsers
    wsHost: window.location.hostname,
    wsPort: 6001,
    wssPort: 6001,
    disableStats: true,
});

// --------------------
// Debug Logging (optional, remove in production)
// --------------------
console.log('Echo initialized:', window.Echo);

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ Pusher connected:', window.Echo.connector.pusher.connection.socket_id);
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('❌ Pusher connection error:', err);
});

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.warn('⚠️ Pusher disconnected');
});

// --------------------
// Helper: Subscribe to private chat channels
// --------------------
window.subscribeToConversation = (conversationId, callback) => {
    return window.Echo.private(`supp.conversations.${conversationId}`)
        .listen('supp.message.sent', callback);
};

window.subscribeToUser = (userId, callback) => {
    return window.Echo.private(`supp.users.${userId}`)
        .listen('supp.conversation.updated', callback);
};

