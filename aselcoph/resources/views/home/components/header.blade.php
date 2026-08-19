<header class="th-header header-layout4 header-absolute">
    <div class="sticky-wrapper">
        <!-- Main Menu Area -->
        <div class="menu-area" style="background-color: #fff;">
            <div class="container-fluid p-0">
                <div class="row align-items-center justify-content-between">
                    <!-- Logo -->
                    <div class="col-auto">
                        <div class="header-logo p-0">
                            <a class="icon-masking" href="/">
                                <img src="/assets/logo_tag.png" alt="Coop Logo" style="max-height: 70px;">
                            </a>
                        </div>
                    </div>

                    <!-- Main Navigation -->
                    <div class="col-auto">
                        <nav class="main-menu style2 d-none d-lg-inline-block">
                            <ul>
                                @foreach (App\Models\Menu::get() as $menu)
                                    @php
                                        $submenuCount = App\Models\MenuItem::where('menu_id', $menu->id)->count();
                                    @endphp

                                    @if ($menu->name == 'Resources')
                                        <li class="menu-item-has-children">
                                            <a href="#" class="menu-link"
                                                style="color:#000">{{ $menu->name }}</a>

                                            <ul class="sub-menu">
                                                @foreach (App\Models\FileManager::where('is_folder', 1)->where('isDeleted', 0)->whereNull('parent_id')->get() as $folder)
                                                    @php
                                                        $sub_folder = App\Models\FileManager::where('is_folder', 1)
                                                            ->where('isDeleted', 0)
                                                            ->where('parent_id', $folder->link);
                                                    @endphp
                                                    <li
                                                        class="{{ $sub_folder->count() ? 'menu-item-has-children' : '' }}">
                                                        <a href="/storage/{{ $folder->link ?? '#' }}">{{ $folder->name }}</a>

                                                        {{-- If this folder has subfolders, show them recursively --}}

                                                        @if ($sub_folder->count())
                                                            <ul class="sub-menu">
                                                                @foreach ($sub_folder->where('is_folder', 1)->where('isDeleted', 0)->get() as $sub)
                                                                    <li>
                                                                        <a href="#">{{ $sub->name }}</a>
                                                                        @if ($sub->children()->count())
                                                                            <ul class="sub-menu">
                                                                                @foreach ($sub->children()->where('is_folder', 1)->where('isDeleted', 0)->get() as $subsub)
                                                                                    <li><a
                                                                                            href="/storage/{{ $subsub->link ?? '#' }}">{{ $subsub->name }}</a>
                                                                                    </li>
                                                                                @endforeach
                                                                            </ul>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>

                                        </li>
                                    @else
                                        <li class="{{ $submenuCount > 0 ? 'menu-item-has-children' : '' }}">
                                            <a href="#" style="color: #000">{{ $menu->name }} &nbsp;</a>

                                            @if ($submenuCount > 0)
                                                <ul class="sub-menu">
                                                    @foreach (App\Models\MenuItem::where('menu_id', $menu->id)->orderBy('order', 'ASC')->get() as $submenu)
                                                        <li><a
                                                                href="/page/51/{{ Str::slug($submenu->label) }}">{{ $submenu->label }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </nav>


                        <button type="button" class="th-menu-toggle d-block d-lg-none">
                            <i class="far fa-bars"></i>
                        </button>
                    </div>

                    <!-- Header Button -->
                    <div class="col-auto d-none d-lg-inline-block">
                        <div class="header-button">
                            <!-- Trigger Modal -->
                            <a href="#" class="th-btn style1 d-none d-xl-block" data-bs-toggle="modal"
                                data-bs-target="#loginModal">Member Portal</a>
                            <a href="#" class="icon-btn sideMenuTogglerX">
                                <i class="fa-light fa-grid"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="loginModalLabel">Member Portal Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">❌</button>
            </div>

            <div class="modal-body">
                @if (session('status'))
                    <div class="mb-3 text-green-600 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-3 text-red-600 text-sm">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif


                <form action="{{ route('login') }}" method="POST" autocomplete="off">
                    @csrf
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address : <span
                                class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="Enter your email address..." required>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password : <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Enter your password..." required>
                    </div>

                    <!-- Remember Me & Forgot -->
                    <div class="d-flex justify-content-between align-items-center mb-3 mx-0">
                        <div class="form-check px-2 pt-2">
                            <input class="form-check-input" checked type="checkbox" name="remember" id="remember_me">
                            <label class="form-check-label" for="remember_me">
                                Remember me
                            </label>
                        </div>
                        <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot
                            Password?</a>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn"
                            style="background-color: #70D614; color: #000; border-radius: 20px;">
                            <strong>Sign In</strong>
                        </button>
                    </div>
                </form>
            </div>

            <div class="modal-footerx border-0 text-center w-100">
                <center> <small class="text-muted">Need help? <a href="#" class="text-dark">Contact
                            support</a></small></center>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="registerModalLabel">Create a Member Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">❌</button>
            </div>

            <div class="modal-body">
                @if (session('status'))
                    <div class="mb-3 text-green-600 text-sm" style="color: red">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-3 text-red-600 text-sm" style="color: red">
                        @foreach ($errors->all() as $error)
                            <div style="color: red">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" autocomplete="off">
                    @csrf

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name : <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name"
                            placeholder="Enter full name" value="{{ old('name') }}" required autofocus>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address : <span
                                class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="Enter email address" value="{{ old('email') }}" required>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password : <span
                                class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Create password" required>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password : <span
                                class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation"
                            name="password_confirmation" placeholder="Confirm password" required>
                    </div>

                    <!-- Terms & Policy -->
                    @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                        <div class="d-flex justify-content-between align-items-center mb-3 mx-0">
                            <div class="form-check px-2 pt-2">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms"
                                    required>
                                <label class="form-check-label text-sm" for="terms">
                                    I agree to the <a href="{{ route('terms.show') }}" target="_blank">Terms of
                                        Service</a> and <a href="{{ route('policy.show') }}" target="_blank">Privacy
                                        Policy</a>
                                </label>
                            </div>
                        </div>
                    @endif

                    <!-- Register Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn"
                            style="background-color: #70D614; color: #000; border-radius: 20px;">
                            <strong>Register</strong>
                        </button>
                    </div>
                </form>
            </div>

            <div class="modal-footerx border-0 text-center w-100">
                <center>
                    <small class="text-muted">
                        Already registered?
                        <a href="#" class="text-dark" data-bs-toggle="modal" data-bs-target="#loginModal"
                            data-bs-dismiss="modal">Login here</a>
                    </small>
                </center>
            </div>
        </div>
    </div>
</div>
