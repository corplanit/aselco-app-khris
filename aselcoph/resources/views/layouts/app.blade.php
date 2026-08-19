<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="/assets/logo_favicon.png" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('components.nav-link')

    @livewireStyles
</head>

<body>

    <div class="page">
        @include('components.nav-top')
        @include('components.nav-sidemenu')

        <div class="main-content app-content">
            <div class="container-fluid">
                @include('components.nav-breadcrumbs')
                @auth
                    @if (!Auth::user()->hasVerifiedEmail())
                        <div class="grid grid-cols-12 gap-6">
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-body">
                                        <i class="bi bi-info-circle px-1"></i> Email verification required.
                                        <hr class="mb-3 mt-3">
                                        <div class="custom-box">
                                            <div class="alert alert-danger border mt-4 p-4 rounded bg-red-100 text-red-800">
                                                <strong>Email verification required.</strong><br>
                                                Please check your email for a verification link.

                                                <form method="POST" action="{{ route('verification.send') }}"
                                                    class="inline-block mt-2">
                                                    @csrf
                                                    <button type="submit"
                                                        class="underline text-blue-600 hover:text-blue-800 text-sm">
                                                        Click here to resend verification email
                                                    </button>
                                                </form>

                                                @if (session('status') == 'verification-link-sent')
                                                    <p class="text-sm text-green-600 mt-2">A new verification link has been
                                                        sent to your email
                                                        address.</p>
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{ $slot }}
                    @endif
                @endauth
            </div>
        </div>

        {{-- @if (Auth::user()->id != 2)
            @include('chats.index')
        @endif --}}

        @include('complaint.form')
        @include('components.nav-footer')

    </div>


    @stack('modals')
    @include('components.nav-footer-link')

    @livewireScripts

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        (function() {
            const badgeId = 'count_unread_msg';
            const endpoint = '/supp/chat/unread/total';

            function clampDisplay(n) {
                n = parseInt(n || 0, 10) || 0;
                if (n <= 0) return '';
                return n > 9 ? '9+' : String(n);
            }

            function ensureBadge() {
                let el = document.getElementById(badgeId);
                if (el) return el;

                const anchor =
                    document.querySelector('[data-unread-badge-anchor="support-messages"]') ||
                    document.querySelector('a[href="/supp/chat"]');

                if (!anchor) return null;

                anchor.classList.add('relative');

                el = document.createElement('span');
                el.id = badgeId;
                el.className = 'translate-middle badge !rounded-full bg-danger absolute top-0 end-0';
                el.style.display = 'none';
                anchor.appendChild(el);

                return el;
            }

            function setBadgeCount(n) {
                const el = ensureBadge();
                if (!el) return;

                const txt = clampDisplay(n);
                if (!txt) {
                    el.textContent = '';
                    el.style.display = 'none';
                    return;
                }

                el.textContent = txt;
                el.style.display = '';
            }

            async function pollTotalUnread() {
                if (document.hidden) return;
                if (!window.axios) return;

                try {
                    const {
                        data
                    } = await window.axios.get(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    setBadgeCount(data?.total ?? 0);
                } catch (e) {
                    // quiet
                }
            }

            // ✅ make callable anywhere (e.g. after mark-read)
            window.pollTotalUnreadNow = pollTotalUnread;

            function startPolling() {
                ensureBadge();
                pollTotalUnread();

                // avoid duplicate timers
                if (window.__unreadBadgeTimer) clearInterval(window.__unreadBadgeTimer);

                window.__unreadBadgeTimer = setInterval(pollTotalUnread, 3000);
            }

            function waitForAxiosThenStart() {
                let tries = 0;
                const t = setInterval(() => {
                    if (window.axios) {
                        clearInterval(t);
                        startPolling();
                    } else if (++tries > 40) {
                        // ~4 seconds max wait
                        clearInterval(t);
                    }
                }, 100);
            }

            document.addEventListener('DOMContentLoaded', () => {
                // if axios already exists start immediately, else wait
                if (window.axios) startPolling();
                else waitForAxiosThenStart();
            });

            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) pollTotalUnread();
            });
        })();
    </script>


</body>

</html>
