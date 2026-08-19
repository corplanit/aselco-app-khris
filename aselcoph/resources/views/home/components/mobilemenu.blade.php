<div class="th-menu-wrapper">
    <div class="th-menu-area text-center">
        <button class="th-menu-toggle"><i class="fal fa-times"></i></button>
        <div class="mobile-logo p-4 pt-3 pb-3">
            <a href="home-hr-management.html"><img src="/assets/logo.png" alt="Sassa"></a>
        </div>

        <div class="th-mobile-menu">
            <ul>
                <li><a href="/">Home</a></li>
                <li class="menu-item-has-children">
                    <a href="#">About Us</a>
                    <ul class="sub-menu">
                        <li><a href="/pages/about-us">Our Story</a></li>
                        <li><a href="#">Leadership</a></li>
                        <li><a href="#">Service Coverage</a></li>
                    </ul>
                </li>
                <li class="menu-item-has-children">
                    <a href="#">Member Services</a>
                    <ul class="sub-menu">
                        <li><a href="#">Billing & Payment</a></li>
                        <li><a href="#">Report an Outage</a></li>
                        <li><a href="#">New Connection</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </li>
                <li class="menu-item-has-children">
                    <a href="#">Programs</a>
                    <ul class="sub-menu">
                        <li><a href="#">Sustainability</a></li>
                        <li><a href="#">Community Engagement</a></li>
                        <li><a href="#">Events & Projects</a></li>
                    </ul>
                </li>
                <li class="menu-item-has-children">
                    <a href="#">News</a>
                    <ul class="sub-menu">
                        <li><a href="#">Latest News</a></li>
                        <li><a href="#">News Details</a></li>
                    </ul>
                </li>
                <li><a href="#">Contact</a></li>
            </ul>

            <div class="mobile-auth-buttons mt-4 p-4 pt-0 pb-0">
                <a href="#" class="th-btn d-block mb-2 mobile-login-btn" data-bs-toggle="modal"
                    data-bs-target="#loginModal" style="background-color: #70D614; color: #000; border-radius: 20px;">
                    <strong>Member Login</strong>
                </a>
                <a href="#" class="th-btn d-block mobile-register-btn" data-bs-toggle="modal"
                    data-bs-target="#registerModal"
                    style="border: 1px solid #70D614; background-color: #000; color: #70D614; border-radius: 20px;">
                    <strong>Create Account</strong>
                </a>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const closeMenu = () => {
            const menuToggleBtn = document.querySelector(".th-menu-toggle");
            if (menuToggleBtn) menuToggleBtn.click(); // Simulates closing the menu
        };

        document.querySelector(".mobile-login-btn")?.addEventListener("click", closeMenu);
        document.querySelector(".mobile-register-btn")?.addEventListener("click", closeMenu);
    });
</script>
