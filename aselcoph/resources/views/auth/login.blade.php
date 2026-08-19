<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In — ASELCO | Serbisyong Mapahiyumon</title>
    <link rel="icon" href="/assets/raw/new.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap');

        body {
            font-family: "Rubik", sans-serif;
            font-optical-sizing: auto;
            font-style: normal;
        }
    </style>
</head>

<body class="" style="background-image: url('/assets/home/img/bg/hero_bg_4.png?t')">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-6xl w-full grid grid-cols-1 md:grid-cols-2 gap-20 px-4">


            <div class="flex flex-col justify-center">

                <form action="{{ route('login') }}" method="POST" autocomplete="on"
                    class="mt-8 space-y-6 bg-white p-8 rounded-lg pt-2 shadow">
                    @csrf

                    <div class="text-start justify-start">
                        <img class="mx-auto h-24 w-auto" src="/assets/logo_tag.png" alt="Logo" />
                    </div>
                    <hr>
                    <h2 class=" text-3xl !m-0 !p-0 !mt-4 font-extrabold text-gray-900">Welcome Back</h2>

                    <p class="!mt-2 text-gray-600">To begin your session, kindly log in to your account.</p>
                    <hr>

                    @if ($errors->any())
                        <div class="flex items-center p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg"
                            role="alert">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            @foreach ($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" name="email" id="email" required placeholder="name@company.com"
                            class="mt-1 block w-full px-3 py-2 h-12 border bg-gray-50 border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div class="mt-0">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required placeholder="*************"
                                class="mt-1 block w-full px-3 py-2 pr-10 border h-12 bg-gray-50 border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

                            <span onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                                <i id="togglePasswordIcon"
                                    class="fa-regular fa-eye text-gray-400 hover:text-gray-600"></i>

                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" checked
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 bg-gray-50 !border-gray-50 rounded form-check-input">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="{{ route('password.request') }}"
                            class="text-sm text-blue-600 hover:text-blue-500 font-medium">Forgot password?</a>
                    </div>

                    <div>
                        <button type="submit" style="background-color: #70D614; color: #000; border-radius: 20px;"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            style="letter-spacing: 0.5px; font-weight: semibold;">
                            Sign in to your account
                        </button>
                    </div>

                    <div class="!mt-3 grid grid-cols-1 gap-3">
                        <a href="{{ route('google.login') }}" style="border-radius: 20px;" class="w-full inline-flex justify-center items-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="h-5 w-5 mr-2" alt="Google">
                            Continue with Google Account
                        </a>
                        
                    </div>

                    <p class="mt-6 text-center text-sm text-gray-600">
                        Not a member?
                        <a href="/register" class="font-medium text-blue-600 hover:text-blue-500">
                            Register now
                        </a>
                    </p>
                </form>
            </div>


            <div class="hidden md:flex items-center justify-center pt-12">
                 <div class="hero-img jump">
                    <img src="/assets/home/landing/hero_4_1-1.png" alt="Hero Image">
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    </script>
</body>

</html>
