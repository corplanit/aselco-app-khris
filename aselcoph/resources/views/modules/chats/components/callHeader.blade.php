
<div id="header-call" class="mx-auto w-[45%] flex items-center justify-between gap-3 px-4 py-2 mt-3 mb-3 hidden
            bg-gray-50 border border-gray-200 rounded-lg shadow-sm">

    <!-- Left: Meeting Info -->
    <div class="flex items-center gap-3 min-w-0">
        <i data-lucide="phone-call" class="w-6 h-6 text-green-600 shrink-0"></i>
        <div class="min-w-0">
            <!-- Meeting Title -->
            <div class="font-semibold text-base text-gray-800 truncate">
                Project Standup Meeting
            </div>
            <!-- Status Indicator -->
            <div class="flex items-center gap-2 text-xs text-gray-600">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-300 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <div class="text-xs text-gray-500">
                    Auto-closing in <span id="call-countdown">30</span>s
                </div>
            </div>
        </div>
    </div>


    <style>
        /* Keep your existing animation */
        #header-call.show {
            display: flex;
            animation: fadeDown 0.3s ease-out forwards;
        }
        @keyframes fadeDown {
            0% { opacity: 0; transform: translateY(-10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>


    <!-- Right: Actions -->
    <div class="flex items-center gap-2 shrink-0">

        <button id="joinBtn" data-room="{{ encrypt('kent') }}" data-name="{{ auth()->user()->name }}"
                data-avatar="{{ auth()->user()->profile_photo_path
                ? asset('storage/' . auth()->user()->profile_photo_path)
                : asset('/assets/raw/watermark.svg') }}"
                class="px-3 py-1 rounded-md border border-green-200 bg-green-100 text-green-700 hover:bg-green-200 flex items-center gap-1">
            <i data-lucide="phone" class="w-4 h-4"></i>
            <span class="text-sm">Join</span>
        </button>

        <!-- End Button -->
        <button id="btn-end-call"
                onclick="document.getElementById('header-call').classList.remove('show'); document.getElementById('header-call').classList.add('hidden')"
                class="px-3 py-1 rounded-md border border-red-200 bg-red-100 text-red-700 hover:bg-red-200 flex items-center gap-1">
            <i data-lucide="phone-off" class="w-4 h-4"></i>
            <span class="text-sm">Close</span>
        </button>
    </div>

    @include('modules.chats.hook.meet-hook')

</div>
