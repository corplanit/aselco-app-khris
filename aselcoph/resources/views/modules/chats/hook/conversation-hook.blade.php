<style>
    /* ===== BUBBLES ===== */
    .bubble {
        position: relative;
        max-width: 46rem;
        padding: 10px 14px;
        line-height: 1.55;
        word-wrap: break-word;
    }

    .bubble.ai {
        background: #EEF2FF;
        /* indigo-50 */
        color: #0F172A;
        /* slate-900 */
    }

    .bubble.me {
        background: #2563EB;
        /* blue-600 */
        color: #fff;
    }

    .bubble.me::after {
        content: "";
        position: absolute;
        right: -6px;
        bottom: 10px;
        width: 12px;
        height: 12px;
        background: #2563EB;
        border-bottom-right-radius: 12px;
        transform: rotate(45deg);
        box-shadow: 1px -1px 0 rgba(0, 0, 0, 0.04);
    }

    .stamp {
        font-size: .70rem;
        color: rgb(148 163 184);
    }

    /* slate-400 */

    .day-sep {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin: .5rem 0 1rem;
        justify-content: center;
        color: rgb(148 163 184);
        font-size: .75rem;
    }

    .day-sep .line {
        flex: 1;
        height: 1px;
        background: rgb(226 232 240);
    }

    /* slate-200 */

    /* ===== TYPING INDICATOR ===== */
    .bubble.typing {
        background: #EEF2FF;
        color: #0F172A;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .dots {
        display: inline-flex;
        gap: 4px;
        align-items: center;
    }

    .dot {
        width: 6px;
        height: 6px;
        border-radius: 9999px;
        background: #94A3B8;
        /* slate-400 */
        animation: dot 900ms infinite ease-in-out;
    }

    .dot:nth-child(2) {
        animation-delay: .12s;
    }

    .dot:nth-child(3) {
        animation-delay: .24s;
    }

    @keyframes dot {

        0%,
        60%,
        100% {
            transform: translateY(0);
            opacity: .55;
        }

        30% {
            transform: translateY(-3px);
            opacity: 1;
        }
    }

    /* ===== MESSAGE POP (fade up) ===== */
    @media (prefers-reduced-motion: no-preference) {
        .msg-pop {
            animation: msgPop .28s cubic-bezier(.22, .61, .36, 1) both;
        }
    }

    @keyframes msgPop {
        from {
            opacity: 0;
            transform: translateY(8px) scale(.98);
            filter: saturate(92%);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
            filter: saturate(100%);
        }
    }

    /* Optional: avatar soft fade */
    .avatar-pop {
        animation: avatarPop .22s ease-out both;
    }

    @keyframes avatarPop {
        from {
            opacity: 0;
            transform: translateY(6px) scale(.96);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
</style>


<script>
    const chat = document.getElementById('chat-messages');

    const timeNow = () =>
        new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });

    const scrollToBottom = () => {
        chat.scrollTop = chat.scrollHeight;
    };

    const reinitPreline = () => {
        if (window.HSStaticMethods?.autoInit) window.HSStaticMethods.autoInit();
    };

    // --- Create typing rows (incoming/outgoing) ---
    function createTypingRowLeft({
        avatar = 'https://i.pravatar.cc/100?img=5',
        name = ''
    } = {}) {
        const wrap = document.createElement('div');
        wrap.className = 'pl-12 pr-16';
        wrap.innerHTML = `
        <div class="flex items-end gap-3">
          <img src="${avatar}" class="w-8 h-8 rounded-full object-cover -mb-1 avatar-pop" alt="Typing">
          <div class="min-w-0">
            ${name ? `<div class="text-xs font-semibold text-gray-700 mb-1">${name}</div>` : ''}
            <div class="bubble typing">
              <span class="dots">
                <span class="dot"></span><span class="dot"></span><span class="dot"></span>
              </span>
            </div>
            <div class="mt-1 flex items-center gap-2 text-xs">
              <span class="stamp">${timeNow()}</span>
            </div>
          </div>
        </div>`;
        return wrap;
    }

    function createTypingRowRight({
        avatar = 'https://i.pravatar.cc/100?img=64'
    } = {}) {
        const wrap = document.createElement('div');
        wrap.className = 'pl-16 pr-12';
        wrap.innerHTML = `
        <div class="flex items-end gap-3 justify-end">
          <div class="min-w-0">
            <div class="bubble typing bg-blue-50 text-slate-700">
              <span class="dots">
                <span class="dot"></span><span class="dot"></span><span class="dot"></span>
              </span>
            </div>
            <div class="mt-1 flex items-center gap-2 justify-end text-xs">
              <span class="stamp">${timeNow()}</span>
            </div>
          </div>
          <img src="${avatar}" class="w-8 h-8 rounded-full object-cover -mb-1 avatar-pop" alt="Me typing">
        </div>`;
        return wrap;
    }

    // --- Create final message rows ---
    function createIncomingRow({
        text,
        type = 'text', // 'system' | 'message'
        time = timeNow(),
        avatar = 'https://i.pravatar.cc/100?img=5',
        name = ''
    } = {}) {
        const wrap = document.createElement('div');

        // sanitize/linkify once
        const safeBody = window.sanitizeAllowedHtml(
            window.looksLikeHtml(text) ? (text || '') : window.linkify(text || '')
        );

        if (type === 'system') {
            // centered muted system notice
            wrap.className = 'flex justify-center my-4';
            wrap.innerHTML = `
            <div class="bg-gray-100 text-gray-500 text-xs px-3 py-1 rounded-lg italic">
              ${safeBody} <span class="ml-1 opacity-70">${time}</span>
            </div>
          `;
            return wrap;
        }

        // default incoming message
        wrap.className = 'pl-12 pr-16 msg-pop';
        wrap.innerHTML = `
          <div class="flex items-end gap-3">
            <img src="${avatar}" class="w-8 h-8 rounded-full object-cover -mb-1 avatar-pop" alt="User" onerror="this.src='/user.png'">
            <div class="min-w-0">
              ${name ? `<div class="text-xs font-semibold text-gray-700 mb-1">${name}</div>` : ''}
              <div class="bubble ai"></div>
              <div class="mt-1 flex items-center gap-2 text-xs">
                <span class="stamp">${time}</span>
                <!-- dropdown omitted for brevity -->
              </div>
            </div>
          </div>
        `;

        wrap.querySelector('.bubble.ai').innerHTML = safeBody;
        return wrap;
    }


    function createOutgoingRow({
        text,
        time = timeNow(),
        read = true,
        avatar = 'https://i.pravatar.cc/100?img=64'
    } = {}) {
        const wrap = document.createElement('div');
        wrap.className = 'pl-16 pr-12 msg-pop';
        wrap.innerHTML = `
          <div class="flex items-end gap-3 justify-end">
            <div class="min-w-0">
              <div class="bubble me"></div>
              <div class="mt-1 flex items-center gap-2 justify-end text-xs">
                <span class="stamp">${time}${read ? ' · Read' : ''}</span>
                <!-- dropdown omitted for brevity -->
              </div>
            </div>
            <img src="${avatar}" class="w-8 h-8 rounded-full object-cover -mb-1 avatar-pop" alt="Me">
          </div>`;
        // ⬇ render rich text
        wrap.querySelector('.bubble.me').innerHTML = window.sanitizeAllowedHtml(
            window.looksLikeHtml(text) ? (text || '') : window.linkify(text || '')
        );
        return wrap;
    }


    // --- Public API: typing → message with fade-up ---
    function addIncomingWithTyping({
        text,
        type,
        typingMs,
        avatar,
        name
    }) {
        // create typing bubble
        const typing = createTypingRowLeft({
            avatar,
            name
        });
        chat.appendChild(typing);
        scrollToBottom();

        // compute typing duration if not provided
        const ms = typingMs ?? clamp((text?.length ?? 0) * 25 + 400, 600, 2200);

        setTimeout(() => {
            typing.remove();
            const row = createIncomingRow({
                text,
                type,
                avatar,
                name,
                time: timeNow()
            });
            chat.appendChild(row);
            reinitPreline();
            scrollToBottom();
        }, ms);
    }

    function addOutgoingWithTyping({
        text,
        typingMs,
        avatar,
        read = true
    }) {
        const typing = createTypingRowRight({
            avatar
        });
        chat.appendChild(typing);
        scrollToBottom();

        const ms = typingMs ?? clamp((text?.length ?? 0) * 22 + 360, 550, 2000);

        setTimeout(() => {
            typing.remove();
            const row = createOutgoingRow({
                text,
                avatar,
                read,
                time: timeNow()
            });
            chat.appendChild(row);
            reinitPreline();
            scrollToBottom();
        }, ms);
    }

    // Utils
    function clamp(v, min, max) {
        return Math.max(min, Math.min(max, v));
    }

    // --- Demo Buttons ---
    // document.getElementById('demo-in').addEventListener('click', () => {
    //     addIncomingWithTyping({
    //         text: "Got it! I'll update the schedule and ping you when it's ready.",
    //         avatar: 'https://i.pravatar.cc/100?img=22',
    //         name: window.ACTIVE_CONV_ID // set for group chats
    //     });
    // });

    // document.getElementById('demo-me').addEventListener('click', () => {
    //     addOutgoingWithTyping({
    //         text: "Perfect, thanks! I’ll review after lunch and confirm. 👍",
    //         avatar: 'https://i.pravatar.cc/100?img=64',
    //         read: true
    //     });
    // });
</script>
