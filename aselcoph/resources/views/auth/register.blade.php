<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register — ASELCO | Serbisyong Mapahiyumon</title>
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

<body style="background-image: url('/assets/home/img/bg/hero_bg_4.png?t')">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-6xl w-full grid grid-cols-1 md:grid-cols-2 gap-20 px-4">

            {{-- Left: Register Form --}}
            <div class="flex flex-col justify-center">


                <form method="POST" action="{{ route('register') }}" autocomplete="on"
                    class="mt-8 space-y-6 bg-white p-8 rounded-lg pt-2 shadow">
                    @csrf

                     <div class="text-start justify-start">
                        <img class="mx-auto h-24 w-auto" src="/assets/logo_tag.png" alt="Logo" />
                    </div>
                    <hr class="pt-0">

                    <h2 class="text-3xl !m-0 !p-0 !mt-4 font-extrabold text-gray-900">Create an Account</h2>
                    <p class="!mt-2 mb-0 text-gray-600">
                        Join Aselco Inc. and start using your workspace.
                    </p>
                    <hr  class="!mt-4 pt-0">

                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="flex items-start p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg" role="alert">
                            <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Name --}}

                    <div class="!mt-3">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                            autocomplete="name"
                            placeholder="John Doe"
                            class="mt-1 block w-full px-3 py-2 h-12 border bg-gray-50 border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div class="!mt-3">
                        <label for="contact_no" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input id="contact_no" type="text" name="contact_no" value="{{ old('contact_no') }}" required
                            autocomplete="contact_no"
                            placeholder="+63"
                            class="mt-1 block w-full px-3 py-2 h-12 border bg-gray-50 border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    {{-- Email --}}
                    <div class="!mt-3">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autocomplete="username" placeholder="name@company.com"
                            class="mt-1 block w-full px-3 py-2 h-12 border bg-gray-50 border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    {{-- Password --}}
                    <div class="!mt-3 mb-0">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" required
                                autocomplete="new-password" placeholder="*************"
                                class="mt-1 block w-full px-3 py-2 pr-10 h-12 border bg-gray-50 border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <span onclick="togglePassword('password', 'togglePasswordIcon')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                                <i id="togglePasswordIcon"
                                    class="fa-regular fa-eye text-gray-400 hover:text-gray-600"></i>
                            </span>
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="!mt-3">
                        <label for="password_confirmation"
                            class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <div class="relative">
                            <input id="password_confirmation" type="password" name="password_confirmation" required
                                autocomplete="new-password" placeholder="*************"
                                class="mt-1 block w-full px-3 py-2 pr-10 h-12 border bg-gray-50 border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <span onclick="togglePassword('password_confirmation', 'toggleConfirmPasswordIcon')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                                <i id="toggleConfirmPasswordIcon"
                                    class="fa-regular fa-eye text-gray-400 hover:text-gray-600"></i>
                            </span>
                        </div>
                    </div>

                    {{-- Terms & Privacy (Jetstream feature) --}}
                    @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                        <div class="mt-2">
                            <label for="terms" class="flex items-start cursor-pointer">
                                <input id="terms" name="terms" type="checkbox" required
                                    class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 bg-gray-50 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-600">
                                    {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                            'terms_of_service' =>
                                                '<a target="_blank" href="'.route('terms.show').'"
                                                    class="underline text-sm text-blue-600 hover:text-blue-800">'.
                                                    __('Terms of Service').'</a>',
                                            'privacy_policy' =>
                                                '<a target="_blank" href="'.route('policy.show').'"
                                                    class="underline text-sm text-blue-600 hover:text-blue-800">'.
                                                    __('Privacy Policy').'</a>',
                                    ]) !!}
                                </span>
                            </label>
                        </div>
                    @endif

                    {{-- Submit --}}
                    <div class="pt-2">
                        <button type="submit"style="background-color: #70D614; color: #000; border-radius: 20px;"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            style="letter-spacing: 0.5px; font-weight: semibold;">
                            Create your account
                        </button>
                    </div>

                    {{-- Already Registered --}}
                    <p class="mt-6 text-center text-sm text-gray-600">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                            Sign in
                        </a>
                    </p>
                </form>
            </div>

            {{-- Right: Illustration --}}
            <div class="hidden md:flex items-center justify-center pt-12">
                 <div class="hero-img jump">
                    <img src="/assets/home/landing/hero_4_1-1.png" alt="Hero Image">
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    </script>
</body>

</html>
