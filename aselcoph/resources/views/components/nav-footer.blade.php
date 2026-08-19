<footer
    class="mt-auto py-4 bg-white dark:bg-bodybg text-center border-t border-defaultborder dark:border-defaultborder/10">
    <div class="container-fluid">
        <span class="text-textmuted dark:text-textmuted/50">
            Copyright © <span id="year"></span>
            <a href="https://aselco.ph/" class="text-dark font-medium">Agusan del Sur Electric Cooperative, Inc. | San Francisco</a>.
            All rights reserved.
        </span>
    </div>
</footer>

<div class="hs-overlay ti-modal hidden" id="header-responsive-search" tabindex="-1"
    aria-labelledby="header-responsive-search" aria-hidden="true">
    <div class="ti-modal-box">
        <div class="ti-modal-dialog">
            <div class="ti-modal-content">
                <div class="ti-modal-body">
                    <div class="input-group">
                        <input type="text" class="form-control border-end-0 !border-s"
                            placeholder="Search Anything ..." aria-label="Search Anything ..."
                            aria-describedby="button-addon2">
                        <button class="ti-btn ti-btn-primary !m-0" type="button" id="button-addon2"><i
                                class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function remove_data(id, type) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/delete',
                    type: 'post',
                    headers: {
                        'X-CSRF-Token': '{{ csrf_token() }}'
                    },
                    data: {
                        id: id,
                        type: type
                    },
                    success: function(response) {
                        Swal.fire({
                            title: "Deleted!",
                            text: "Your record has been deleted.",
                            icon: "success"
                        });
                        setTimeout(() => {
                            window.location.href = response;
                        }, 2000);
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: "Error!",
                            text: "There was a problem deleting your record. " + error,
                            icon: "error"
                        });
                    }
                });
            }
        });
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const currentURL = window.location.pathname;

        const menuItems = document.querySelectorAll(".side-menu__item");

        menuItems.forEach(item => {
            const href = item.getAttribute("href");

            // Skip invalid links
            if (!href || href === "#" || href.startsWith("javascript")) return;

            // Match full URL or partial (you can tweak this logic)
            if (currentURL === href || currentURL.startsWith(href)) {
                // Add submenu highlight
                item.classList.add("active-menu");

                // If this item is inside a submenu, open its parent
                const submenu = item.closest("ul.slide-menu");
                if (submenu) {
                    submenu.style.display = "block";

                    const parentLi = submenu.closest("li.slide.has-sub");
                    if (parentLi) {
                        parentLi.classList.add("open");

                        const parentLink = parentLi.querySelector("> a.side-menu__item");
                        if (parentLink) {
                            parentLink.classList.add("active-parent-menu");
                        }
                    }
                }
            }
        });
    });
</script>


<style>
    /* Initial state of the .custom-box (hidden and shifted down) */
    .custom-box {
        opacity: 0;
        /* Initially hidden */
        transform: translateY(20px);
        /* Initially moved down */
        animation: fadeUp 0.6s ease-out forwards;
        /* Trigger fade-up animation */
    }

    /* Define the fade-up animation */
    @keyframes fadeUp {
        0% {
            opacity: 0;
            transform: translateY(20px);
            /* Starts down */
        }

        100% {
            opacity: 1;
            transform: translateY(0);
            /* Ends at normal position */
        }
    }
</style>

<style>
    .active-menu {
        background-color: #EEF0FE !important;
        color: #5C66F6 !important;
        border-radius: 5px;
        padding: 10px 15px 10px 20px;
        margin-right: 5px;
        transition: 0.3s ease;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        position: relative;
    }
    .slide.has-sub .slide-menu .active-menu {
        background-color: #EEF0FE !important;
    }
    .active-parent-menu {
        background-color: #FFBC58 !important;
        color: #5C66F6 !important;
        font-weight: 500;
        border-radius: 5px;
        padding: 10px 15px 10px 20px;
        margin-right: 5px;
        position: relative;
    }

    .active-parent-menu::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 5px;
        background-color: #FFBC58 !important;
        border-radius: 5px 0 0 5px;
    }
</style>


@if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Saved Successfully',
            // text: '{{ session('success') }}',
            confirmButtonText: 'OK'
        });
    </script>
@endif

@if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{ session('error') }}',
            confirmButtonText: 'Try Again'
        });
    </script>
@endif
