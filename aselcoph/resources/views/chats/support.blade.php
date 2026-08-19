<x-app-layout>

    <x-slot name="title">Manage</x-slot>
    <x-slot name="url_1">{"link": "/", "text": "Manage"}</x-slot>
    <x-slot name="active">Manage</x-slot>
    <x-slot name="buttons"></x-slot>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12">
            <div class="box custom-box">
                <div class="box-header">
                    <div class="box-body">

                        <div class="flex">
                            <!-- Sidebar -->
                            <aside class="w-full xl:w-1/3 max-w-sm bg-white border-r px-4 py-5 overflow-y-auto">
                                <h3 class="text-base font-medium mb-4">Customer Conversations</h3>
                                <ul id="conversation-list" class="space-y-2 text-sm"></ul>
                            </aside>

                            <!-- Chat Section -->
                            <main class="flex-1 bg-white flex flex-col rounded-lg overflow-hidden"
                                style="height: 600px;">
                                <div class="bg-blue-600 text-white p-4 flex items-center gap-3" id="chat-with">
                                    <img src="/assets/user.webp" id="chat-avatar" class="w-8 h-8 rounded-full hidden" />
                                    <span id="chat-title">Select a conversation</span>
                                </div>
                                <div id="chat-list" class="flex-1 p-4 space-y-2 overflow-y-auto bg-gray-50">
                                    <div id="chat-placeholder" class="text-center text-sm text-gray-500 mt-20">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="h-20 w-20 md:h-24 md:w-24 text-primary/30 mx-auto mb-4 md:mb-6">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                        <p class="font-semibold text-base">No Conversation Selected</p>
                                        <p class="text-xs mt-1">Select a conversation from the sidebar to start
                                            messaging, or create a new one.</p>
                                    </div>
                                </div>
                                <form id="chat-form" class="border-t p-4 flex gap-2 hidden">
                                    <input type="text" id="chat-input" placeholder="Type your reply"
                                        class="flex-1 border rounded px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Send</button>
                                </form>
                            </main>
                        </div>

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
                            const supportId = {{ Auth::user()->id }}; // Blade variable for support user ID
                            let selectedCustomerId = null;
                            let unsubscribe = null;

                            const conversationList = document.getElementById('conversation-list');
                            const chatBox = document.getElementById('chat-list');
                            const chatForm = document.getElementById('chat-form');
                            const chatInput = document.getElementById('chat-input');
                            const chatWith = document.getElementById('chat-title');
                            const chatAvatar = document.getElementById('chat-avatar');

                            function loadConversations() {
                                const convoRef = collection(db, 'conversations');

                                onSnapshot(convoRef, (snapshot) => {
                                    conversationList.innerHTML = '';

                                    snapshot.forEach(convo => {
                                        const convoId = convo.id;
                                        const [id1, id2] = convoId.split('_').map(Number);

                                        if (id1 !== supportId && id2 !== supportId) return;

                                        const customerId = id1 === supportId ? id2 : id1;

                                        const li = document.createElement('li');
                                        li.className =
                                            'cursor-pointer flex items-center gap-2 p-2 rounded hover:bg-blue-50 border';

                                        // Default before fetch
                                        li.innerHTML = `
                                            <img src="/assets/user.webp" class="w-6 h-6 rounded-full" />
                                            <span>Customer ${customerId}</span>
                                        `;
                                        fetch(`/api/user-name/${customerId}`)
                                            .then(res => res.json())
                                            .then(data => {
                                                const name = data.name ?? `Customer ${customerId}`;
                                                const avatar = data.avatar ?? '/assets/user.webp';
                                                li.innerHTML = `
                                                    <img src="${avatar}" class="w-6 h-6 rounded-full" />
                                                    <span>${name}</span>
                                                `;
                                            });

                                        li.onclick = () => openConversation(customerId);
                                        conversationList.appendChild(li);
                                    });

                                });
                            }

                            function formatDate(timestamp) {
                                const date = timestamp?.toDate?.();
                                if (!date) return '';
                                return new Intl.DateTimeFormat('en-US', {
                                    month: 'short',
                                    day: 'numeric',
                                    year: 'numeric',
                                    hour: 'numeric',
                                    minute: '2-digit',
                                    hour12: true
                                }).format(date);
                            }

                            async function openConversation(customerId) {
                                selectedCustomerId = customerId;
                                chatWith.textContent = 'Loading conversation...';
                                chatAvatar.classList.remove('hidden');
                                chatAvatar.src = '/assets/user.webp'; // fallback default

                                fetch(`/api/user-name/${customerId}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        chatWith.textContent = `Chatting with ${data.name ?? 'Customer ' + customerId}`;
                                        chatAvatar.src = data.avatar ?? '/assets/user.webp';
                                    })
                                    .catch(() => {
                                        chatWith.textContent = `Chatting with Customer ${customerId}`;
                                    });

                                chatAvatar.classList.remove('hidden');
                                chatBox.innerHTML = '';
                                chatForm.classList.remove('hidden');

                                if (unsubscribe) unsubscribe();

                                const conversationId = [customerId, supportId].sort((a, b) => a - b).join('_');
                                const messagesRef = collection(db, `conversations/${conversationId}/messages`);
                                const q = query(messagesRef, orderBy('timestamp', 'asc'));
                                    
                                unsubscribe = onSnapshot(q, snapshot => {
                                    chatBox.innerHTML = '';
                                    snapshot.forEach(doc => {
                                        const data = doc.data();
                                        const isSupport = data.sender_id === supportId;
                                        const timestampStr = formatDate(data.timestamp);

                                        const msgDiv = document.createElement('div');
                                        msgDiv.className =
                                            `flex items-end gap-2 mb-1 ${isSupport ? 'justify-end' : 'justify-start'}`;
                                        msgDiv.innerHTML = `
                                            ${!isSupport ? `` : ''}
                                            <div class="${isSupport ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900'} px-4 py-2 rounded-lg max-w-[75%] text-sm">
                                                <div>${data.message}</div>
                                                <div class="text-[10px] mt-1 text-right opacity-70">${timestampStr}</div>
                                            </div>
                                        `;
                                        chatBox.appendChild(msgDiv);
                                    });

                                    chatBox.scrollTop = chatBox.scrollHeight;
                                });

                                chatForm.onsubmit = async (e) => {
                                    e.preventDefault();
                                    const message = chatInput.value.trim();
                                    if (!message) return;

                                    await addDoc(collection(db, `conversations/${conversationId}/messages`), {
                                        sender_id: supportId,
                                        message: message,
                                        timestamp: serverTimestamp()
                                    });

                                    chatInput.value = '';
                                };
                            }

                            // Start listening to conversations
                            loadConversations();
                        </script>

                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>

</html>
