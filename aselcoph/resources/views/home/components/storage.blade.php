<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agusan del Sur Electric Cooperative, Inc</title>
    <link rel="icon" type="image/png" href="/assets/logo_favicon.png">
    <link rel="manifest" href="/assets/home/img/favicons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/assets/logo_favicon.png">
    <meta name="theme-color" content="#ffffff">

    <!--==============================
 Google Fonts
 ============================== -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!--==============================
 All CSS File
 ============================== -->
    <!-- Bootstrap -->
    <link rel="stylesheet" href="/assets/home/css/bootstrap.min.css">
    <!-- Fontawesome Icon -->
    <link rel="stylesheet" href="/assets/home/css/fontawesome.min.css">
    <!-- Magnific Popup -->
    <link rel="stylesheet" href="/assets/home/css/magnific-popup.min.css">
    <!-- Swiper Slider -->
    <link rel="stylesheet" href="/assets/home/css/swiper-bundle.min.css">
    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="/assets/home/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Mogra&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
        rel="stylesheet">
</head>

<body class="theme-lima">

    @include('home.components.preloader')
    @include('home.components.sidemenu')
    @include('home.components.mobilemenu')
    @include('home.components.header')



    <section class="th-blog-wrapper blog-details space-top space-extra-bottom mt-5">
        <div class="container">
            <div class="row">
                <div class="col-xxl-12 col-lg-12">
                    <div class="th-blog blog-single bg-white">
                        @php
                            $files = App\Models\FileManager::where('is_folder', 0)->where('isDeleted', 0)->where('parent_id', $link)->get();
                        @endphp

                        <h3 class="text-1xl">{{ App\Models\FileManager::where('is_folder', 1)->where('isDeleted', 0)->where('link', $link)->first()->name }}</h3>
                        <hr>
                        @foreach ($files as $file)
                            
                        
                        <div class="col-md-3">
                            <div class="folder-item border p-2" style="border-radius: 10px; cursor: pointer; font-size: 14px" onclick="window.open('/{{ $file->path }}', '_target')">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 287.82 384" width="40"
                                    height="40">
                                    <path fill="#e62335"
                                        d="M652.52 792.45h192s50.06.1 49-59.26 0-210.58 0-210.58H785.16l-1-114.16H652.52s-45.94 1.52-46.52 53 0 284.71 0 284.71 4.19 44.97 46.52 46.29z"
                                        transform="translate(-605.74 -408.45)"></path>
                                    <path fill="#ee656c" d="M178.39 0L287.82 114.16 179.42 114.16 178.39 0z"></path>
                                    <path fill="#fff"
                                        d="M661.75 618.5h13.85V692h-13.85zm6.32 31.64h23.51A8.3 8.3 0 00696 649a7.86 7.86 0 003-3.21 10.35 10.35 0 001-4.79 10.79 10.79 0 00-1-4.83 7.66 7.66 0 00-3-3.17 8.41 8.41 0 00-4.42-1.14h-23.51V618.5h23.15a25.23 25.23 0 0112.11 2.8 19.93 19.93 0 018.11 7.91 25.68 25.68 0 010 23.63 19.84 19.84 0 01-8.11 7.87 25.46 25.46 0 01-12.11 2.78h-23.15zM725.19 618.5H739V692h-13.81zm7 60.15h17.64q6.83 0 10.57-3.29t3.74-9.3v-21.63q0-6-3.74-9.3t-10.57-3.29h-17.66V618.5h17.34a36.29 36.29 0 0115.69 3.08 21.75 21.75 0 019.88 9 28.19 28.19 0 013.39 14.25v20.83a28.16 28.16 0 01-3.26 13.85 22.22 22.22 0 01-9.78 9.2q-6.52 3.28-16 3.29h-17.26zM792.12 618.5H806V692h-13.88zm5 0h43.28v13.34h-43.23zm0 31h37.66v13.35h-37.61z"
                                        transform="translate(-605.74 -408.45)"></path>
                                </svg>
                                <div class="folder-text">
                                   {{ $file->name }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .folder-item {
            display: flex;
            /* Keep icon + text in one line */
            align-items: center;
            gap: 10px;
            /* Space between icon and text */
            max-width: 300px;
            /* Control container width */
        }

        .folder-text {
            display: inline-block;
            white-space: nowrap;
            /* Prevent line breaks */
            overflow: hidden;
            /* Hide overflowing text */
            text-overflow: ellipsis;
            /* Add "..." */
            flex: 1;
            /* Take remaining space */
        }
    </style>


    @include('home.components.footer')

    <!-- Scroll To Top -->
    <div class="scroll-top">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"
                style="transition: stroke-dashoffset 10ms linear 0s; stroke-dasharray: 307.919, 307.919; stroke-dashoffset: 307.919;">
            </path>
        </svg>
    </div>

    <!--==============================
    All Js File
============================== -->
    <!-- Jquery -->
    <script src="/assets/home/js/vendor/jquery-3.7.1.min.js"></script>
    <!-- Swiper Slider -->
    <script src="/assets/home/js/swiper-bundle.min.js"></script>
    <!-- Bootstrap -->
    <script src="/assets/home/js/bootstrap.min.js"></script>
    <!-- Magnific Popup -->
    <script src="/assets/home/js/jquery.magnific-popup.min.js"></script>
    <!-- Counter Up -->
    <script src="/assets/home/js/jquery.counterup.min.js"></script>
    <!-- Circle Progress -->
    <script src="/assets/home/js/circle-progress.js"></script>
    <!-- Range Slider -->
    <script src="/assets/home/js/jquery-ui.min.js"></script>
    <!-- Imagesloadedr -->
    <script src="/assets/home/js/imagesloaded.pkgd.min.js"></script>
    <!-- isotope -->
    <script src="/assets/home/js/isotope.pkgd.min.js"></script>
    <!-- Tilt.jquery -->
    <script src="/assets/home/js/tilt.jquery.min.js"></script>
    <!-- Nice-select -->
    <script src="/assets/home/js/nice-select.min.js"></script>
    <!-- wow -->
    <script src="/assets/home/js/wow.min.js"></script>

    <!-- Main Js File -->
    <script src="/assets/home/js/main.js"></script>

</body>

</html>
