<aside class="app-sidebar" id="sidebar">

    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="/dashboard" class="header-logo">
            <img src="/assets/logo_tag.png?e" alt="logo" class="desktop-logo" style="height: 50px">
            <img src="/assets/logo_favicon.png" alt="logo" class="toggle-logo">
        </a>
    </div>
    <!-- End::main-sidebar-header -->

    <!-- Start::main-sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">

        <!-- Start::nav -->
        <nav class="main-menu-container nav nav-pills flex-col sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                    viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>
            <ul class="main-menu">
                <li class="slide__category"><span class="category-name">Dashboard</span></li>
                <li class="slide">
                    <a href="/u/dashboard" class="side-menu__item">
                        <i class="w-6 h-4 side-menu__icon bi bi-layers" style="color: #5D66F7"></i>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>
                @if (Auth::user()->email == 'admin@aselco.ph')
                    @include('components.menu.admin')
                @elseif (Auth::user()->role == 'support')
                    @include('components.menu.agent')
                @elseif (Auth::user()->role == 'Content Manager')
                    @include('components.menu.cms')
                @else
                    <li class="slide__category"><span class="category-name">Manage Account</span></li>
                    <li class="slide">
                        <a href="/customer/registration" class="side-menu__item">
                            <i class="w-6 h-4 side-menu__icon bi bi-link-45deg" style="color: #5D66F7"></i>
                            <span class="side-menu__label">Link My Account</span>
                        </a>
                    </li>
                    <li class="slide__category"><span class="category-name">Customer Services</span></li>
                    <li class="slide">
                        <a href="/supp/chat" class="side-menu__item relative"
                            data-unread-badge-anchor="support-messages">

                            <i class="w-6 h-4 side-menu__icon bi bi-headset" style="color: #5D66F7"></i>
                            <span class="side-menu__label">Customer Support</span>

                            @php
                                $uid = Auth::id();
                                $role = strtolower(Auth::user()->role ?? '');
                                $isSupport = in_array($role, ['support', 'administrator']);

                                $unread = 0;

                                if ($isSupport) {
                                    // ✅ SUPPORT / ADMIN: sum unread across all conversations
                                    $parts = \App\Models\SuppParticipant::query()
                                        ->where('user_id', $uid)
                                        ->get(['conversation_id', 'last_read_message_id']);

                                    foreach ($parts as $p) {
                                        $q = \App\Models\SuppMessage::query()
                                            ->where('conversation_id', $p->conversation_id)
                                            ->where('user_id', '!=', $uid);

                                        if ($p->last_read_message_id) {
                                            $q->where('id', '>', $p->last_read_message_id);
                                        }

                                        $unread += $q->count();
                                    }
                                } else {
                                    // ✅ CUSTOMER: unread only from support in their own conversation
                                    $conv = \App\Models\SuppConversation::query()->where('customer_id', $uid)->first();

                                    if ($conv) {
                                        $p = \App\Models\SuppParticipant::query()
                                            ->where('conversation_id', $conv->id)
                                            ->where('user_id', $uid)
                                            ->first();

                                        $q = \App\Models\SuppMessage::query()
                                            ->where('conversation_id', $conv->id)
                                            ->where('user_id', '!=', $uid); // messages from support

                                        if ($p && $p->last_read_message_id) {
                                            $q->where('id', '>', $p->last_read_message_id);
                                        }

                                        $unread = $q->count();
                                    }
                                }
                            @endphp

                            <span id="count_unread_msg"
                                class="translate-middle badge !rounded-full bg-danger absolute top-0 end-0"
                                style="{{ $unread > 0 ? '' : 'display:none' }}">
                                {{ $unread > 9 ? '9+' : $unread }}
                            </span>
                        </a>
                    </li>
                    <li class="slide">
                        <a href="#" data-hs-overlay="#complaint" class="side-menu__item">
                            <i class="w-6 h-4 side-menu__icon bi bi-hand-index" style="color: #5D66F7"></i>
                            <span class="side-menu__label">Customer Complaint</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a href="{{ optional(\App\Models\Survery::find(1))->link }}" target="_blank" class="side-menu__item">
                            <i class="w-6 h-4 side-menu__icon bi bi-graph-up-arrow" style="color: #5D66F7"></i>
                            <span class="side-menu__label">Satisfaction Survey</span>
                        </a>
                    </li>
                @endif
                <li class="slide__category"><span class="category-name">Account Settings</span></li>
                <li class="slide">
                    <a href="/user/profile" class="side-menu__item">
                        <i class="w-6 h-4 side-menu__icon bi bi-person-gear" style="color: #5D66F7"></i>
                        <span class="side-menu__label">Profile Settings</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="side-menu__item">
                        <i class="w-6 h-4 side-menu__icon bi bi-power" style="color: #5D66F7"></i>
                        <span class="side-menu__label">Sign Out</span>
                    </a>
                </li>
            </ul>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg></div>
        </nav>
        <!-- End::nav -->

    </div>
    <!-- End::main-sidebar -->
    <style>
        .active-menu {
            background-color: #E4E9F7 !important;
            border-left: 4px solid #5D66F7 !important;
        }
    </style>
</aside>
