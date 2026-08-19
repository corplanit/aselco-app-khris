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

    @php
        $blog = App\Models\postModel::where('post_id', $id)->first();
    @endphp


    @include('home.blog.breadcumb')
    @include('home.blog.details')

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
