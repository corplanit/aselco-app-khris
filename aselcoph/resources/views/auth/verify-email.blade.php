<x-app-layout>
    <x-slot name="title">Email Verification</x-slot>
    <x-slot name="url_1">{"link": "/dashboard", "text": "Dashboard"}</x-slot>
    <x-slot name="active">Verify Email</x-slot>
    <x-slot name="buttons">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="ti-btn ti-btn-light text-dark bg-white !border-0 btn-wave me-0">
                <i class="bi bi-box-arrow-right me-1"></i>Log Out
            </button>
        </form>
    </x-slot>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12">
            <div class="box custom-box">
                <div class="box-body p-5 main-content-card">

                    <h2 class="text-xl font-semibold mb-4">Verify Your Email Address</h2>

                    <p class="text-gray-700 mb-4">
                        Before continuing, please check your email for a verification link.
                        If you didn't receive the email, you can request another one below.
                    </p>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success text-green-800 bg-green-100 border border-green-300 mb-4 p-3 rounded">
                            A new verification link has been sent to your email address.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}" class="mb-4">
                        @csrf
                        <button type="submit" class="ti-btn ti-btn-primary">
                            <i class="bi bi-envelope-plus me-1"></i> Resend Verification Email
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
