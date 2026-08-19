<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer Chat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
        rel="stylesheet">
    <style>
        .slide-up {
            animation: slideUp 0.3s ease-out forwards;
        }

        .slide-down {
            animation: slideDown 0.3s ease-out forwards;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(0);
                opacity: 1;
            }

            to {
                transform: translateY(100%);
                opacity: 0;
            }
        }

        #chat-list {
            max-height: 320px;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        #chat-panel {
            width: 22rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            border-radius: 1rem;
            overflow: hidden;
        }

        .message-bubble {
            padding: 0.75rem 1rem;
            border-radius: 1.25rem;
            max-width: 75%;
            font-size: 0.875rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        #chat-toggle {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            z-index: 9999;
        }

        #chat-toggle button {
            background-color: #2563eb;
            color: white;
            font-size: 1rem;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            border: none;
            cursor: pointer;
        }

        #chat-toggle button img {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 9999px;
        }

        #notification-badge {
            position: absolute;
            top: -0.5rem;
            left: -0.5rem;
            width: 0.75rem;
            height: 0.75rem;
            background-color: #ef4444;
            border-radius: 9999px;
            display: none;
        }
    </style>
</head>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('chat-toggle');
        const button = toggle.querySelector('button');
        const loader = document.getElementById('chat-button-loader');
        const label = document.getElementById('btn-name');

        loader.style.display = 'none';
        button.disabled = false;
        label.textContent = 'Chat with Us';

        // button.disabled = true;
        // loader.style.display = 'inline-block';
        // label.textContent = 'Connecting...';

        // setTimeout(() => {
        //     loader.style.display = 'none';
        //     button.disabled = false;
        //     label.textContent = 'Chat with Us';
        // }, 3000);
    });

    function toggleChatPanel() {
        const panel = document.getElementById("chat-panel");
        const label = document.getElementById("btn-name");
        if (!panel) return;

        if (panel.classList.contains("slide-up")) {
            panel.classList.remove("slide-up");
            panel.classList.add("slide-down");
            setTimeout(() => {
                panel.classList.add("hidden");
                panel.classList.remove("slide-down");
                label.textContent = 'Chat with Us';
            }, 300);
        } else {
            panel.classList.remove("hidden");
            panel.classList.add("slide-up");
            label.textContent = 'Chat with Us';
        }
    }
</script>

<body class="bg-gray-100">

    <div id="chat-toggle" style=" font-family: Rubik, sans-serif;">
        <button onclick="toggleChatPanel()">
            <img src="/v1/comment.png" alt="Chat">
            <span id="chat-button-loader" style="display:none; margin-left:5px;">
                <svg width="16" height="16" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg"
                    stroke="#fff">
                    <g fill="none" fill-rule="evenodd">
                        <g transform="translate(1 1)" stroke-width="2">
                            <circle stroke-opacity=".5" cx="18" cy="18" r="18" />
                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18"
                                    dur="1s" repeatCount="indefinite" />
                            </path>
                        </g>
                    </g>
                </svg>
            </span>
            <span id="btn-name"></span>
            <span id="notification-badge" style="display: none; color: rgb(238,69,68)"></span>
        </button>
    </div>

    <!-- Chat Panel -->
    <div id="chat-panel" class="fixed hidden bg-white flex flex-col z-50"
        style="right: 20px; bottom: 80px; font-family: Rubik, sans-serif;">
        <div class="bg-blue-600 text-white px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="/assets/logo_favicon.png" class="w-10 h-10 rounded-full">
                <div>
                    <div class="font-semibold text-sm">Customer Service</div>
                    <div class="text-xs opacity-80">We are here to help you out</div>
                </div>
            </div>
            <button onclick="toggleChatPanel()" class="text-white text-xl">&times;</button>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#2463EA" fill-opacity="1"
                d="M0,32L48,48C96,64,192,96,288,122.7C384,149,480,171,576,165.3C672,160,768,128,864,122.7C960,117,1056,139,1152,170.7C1248,203,1344,245,1392,266.7L1440,288L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z">
            </path>
        </svg>
        <!-- Quick Questions -->


        <!-- Messages -->
        <div id="chat-list" class="flex-1 px-4 py-3 text-sm bg-white space-y-3"
            style=" font-family: Rubik, sans-serif;"></div>
        <br>
        <div class="px-4 py-2 text-sm border-b bg-blue-50">
            <div class="flex justify-between items-center">
                <span class="font-medium text-gray-600">Quick Questions:</span>
                <button id="toggle-quick-questions" class="text-xs text-blue-600 hover:underline">Hide</button>
            </div>
            <div id="quick-questions" class="space-y-2 mt-2">
                <button onclick="sendPredefined('How can I view my electric bill?')"
                    class="w-full text-left px-3 py-2 bg-white hover:bg-gray-100 rounded-lg">How can I view my electric
                    bill?</button>
                <button onclick="sendPredefined('How often do I receive my bill?')"
                    class="w-full text-left px-3 py-2 bg-white hover:bg-gray-100 rounded-lg">How often do I receive my
                    bill?</button>
                <button onclick="sendPredefined('What is my current balance?')"
                    class="w-full text-left px-3 py-2 bg-white hover:bg-gray-100 rounded-lg">What is my current
                    balance?</button>
                <button onclick="sendPredefined('How do I update my contact information?')"
                    class="w-full text-left px-3 py-2 bg-white hover:bg-gray-100 rounded-lg">How do I update my contact
                    information?</button>
                <button onclick="sendPredefined('Can I pay online?')"
                    class="w-full text-left px-3 py-2 bg-white hover:bg-gray-100 rounded-lg">Can I pay online?</button>
            </div>
        </div>
        <!-- Input -->
        <form id="chat-form" class="p-3 border-t flex gap-2 bg-white" autocomplete="off">
            <input type="text" id="chat-input" placeholder="Write a reply.."
                class="flex-1 border rounded-full px-4 py-2 text-sm">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm">➤</button>
        </form>
    </div>

    <!-- Firebase Chat Logic -->
    <script type="module">
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js";
        import {
            getFirestore,
            collection,
            doc,
            setDoc,
            addDoc,
            serverTimestamp,
            query,
            orderBy,
            onSnapshot
        } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore.js";

        const firebaseConfig = {
            apiKey: "AIzaSyCmOw_Wxob7gW257laT-2XhKmGJLLsNNpg",
            authDomain: "aselco-ph-chat-app.firebaseapp.com",
            projectId: "aselco-ph-chat-app",
            storageBucket: "aselco-ph-chat-app.appspot.com",
            messagingSenderId: "824198674025",
            appId: "1:824198674025:web:e2756feadf0d022e3c14f8"
        };

        const app = initializeApp(firebaseConfig);
        const db = getFirestore(app);

        const customerId = '{{ Auth::user()->id }}';
        const supportId = 2;
        const conversationId = [customerId, supportId].sort((a, b) => a - b).join('_');
        const messagesRef = collection(db, `conversations/${conversationId}/messages`);
        //const notificationBadge = document.getElementById("notification-badge");
        const chatPanel = document.getElementById("chat-panel");

        // Ensure conversation exists
        await setDoc(doc(db, 'conversations', conversationId), {
            participants: [customerId, supportId],
            created_at: serverTimestamp()
        }, {
            merge: true
        });

        // Show messages
        onSnapshot(query(messagesRef, orderBy('timestamp', 'asc')), snapshot => {
            const chatBox = document.getElementById('chat-list');
            const quickSection = document.getElementById('quick-questions');
            const toggleQuickButton = document.getElementById('toggle-quick-questions');
            const chatPanelHidden = chatPanel.classList.contains('hidden');

            chatBox.innerHTML = '';
            let hasNewMessageFromSupport = false;

            // Hide quick questions once messages exist
            if (snapshot.size > 0 && !quickSection.classList.contains('manual-toggle')) {
                quickSection.classList.add('hidden');
                toggleQuickButton.textContent = 'Show';
            }

            snapshot.forEach(async doc => {
                const data = doc.data();
                const isMe = data.sender_id === customerId;
                const isUnread = !isMe && !data.read;
                const timeSent = data.timestamp?.toDate().toLocaleString('en-PH', {
                    hour: 'numeric',
                    minute: 'numeric',
                    hour12: true,
                    month: 'short',
                    day: 'numeric'
                });

                const msgDiv = document.createElement('div');
                msgDiv.className = `flex items-end gap-2 ${isMe ? 'justify-end' : 'justify-start'}`;
                msgDiv.innerHTML = `
    ${!isMe ? '<img src="/assets/logo_favicon.png" class="w-8 h-8 rounded-full">' : ''}
    <div class="${isMe ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-900'} message-bubble relative">
      ${data.message}
      
    </div>
  `;

                chatBox.appendChild(msgDiv);

                // If message is from support and unread, mark as read
                if (!isMe && !data.read && doc.ref) {
                    await setDoc(doc.ref, {
                        read: true
                    }, {
                        merge: true
                    });
                }
            });

            // Show/hide notification badge
            //notificationBadge.style.display = hasNewMessageFromSupport ? 'flex' : 'none';

            chatBox.scrollTop = chatBox.scrollHeight;
        });


        // Send message
        document.getElementById('chat-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('chat-input');
            const message = input.value.trim();
            if (!message) return;
            await addDoc(messagesRef, {
                sender_id: customerId,
                message: message,
                timestamp: serverTimestamp()
            });
            input.value = '';
        });

        // Quick Question Handler
        const autoReplies = {
            "How can I view my electric bill?": "You can view your electric bill by logging in to your customer portal and selecting 'Billing Details'.",
            "How often do I receive my bill?": "Bills are issued monthly around the same date each month.",
            "What is my current balance?": "Please log in to your account to view your current balance.",
            "How do I update my contact information?": "Go to your account settings and update your contact details.",
            "Can I pay online?": "Yes, online payment is available via the customer portal."
        };

        window.sendPredefined = async function(msg) {
            await addDoc(messagesRef, {
                sender_id: customerId,
                message: msg,
                timestamp: serverTimestamp()
            });
            if (autoReplies[msg]) {
                await addDoc(messagesRef, {
                    sender_id: supportId,
                    message: autoReplies[msg],
                    timestamp: serverTimestamp()
                });
            }
        };

        // Chat panel toggle
        window.toggleChatPanel = function() {
            const panel = document.getElementById('chat-panel');
            const badge = document.getElementById('notification-badge');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                panel.classList.add('slide-up');
                badge.classList.add('hidden');
                badge.textContent = '0';
            } else {
                panel.classList.add('slide-down');
                setTimeout(() => {
                    panel.classList.add('hidden');
                    panel.classList.remove('slide-up', 'slide-down');
                }, 300);
            }
        };

        // Toggle quick questions
        const toggleQuickBtn = document.getElementById('toggle-quick-questions');
        const quickSection = document.getElementById('quick-questions');

        toggleQuickBtn.addEventListener('click', (e) => {
            // Don't do anything if hidden
            if (toggleQuickBtn.classList.contains('hidden')) return;

            if (quickSection.classList.contains('hidden')) {
                quickSection.classList.remove('hidden');
                toggleQuickBtn.textContent = 'Hide';
            } else {
                quickSection.classList.add('hidden');
                toggleQuickBtn.textContent = 'Show';
            }
        });
    </script>

</body>

</html>
