<!-- Deps -->
@include('modules.chats.hook.styles')
<div class="flex h-screen bg-white" style="max-height: 700px">
    @include('modules.chats.components.sidebar')
    <main class="flex-1 flex flex-col relative bg-white"
        style="background: linear-gradient(to bottom right, #fff, #ffffff, #F2F7FF);">
        <!-- Header with user details + actions -->
        <header class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white">
            <div class="flex items-center gap-3">
                <img id="convAvatar" src="/user.png" class="w-10 h-10 rounded-full object-cover" alt="User">
                <div>
                    <div id="convTitle" class="text-base font-semibold leading-tight">—</div>
                    <div id="convSubtitle" class="text-xs text-gray-500 flex items-center gap-2">—</div>
                </div>
            </div>
            <!-- actions unchanged -->
            <div class="flex items-center gap-2 hidden" id="header-action">
                <button class="px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center gap-2"
                    title="Call">
                    <i data-lucide="calendar" class="w-4 h-4"></i> Schedules
                </button>
                <button id="meeting-btn"
                    onclick="(function(){
                        const h = document.getElementById('header-call');
                        if (h) { h.classList.add('show'); h.classList.remove('hidden'); }

                        document.getElementById('joinBtnLate')?.classList.remove('hidden');
                        document.getElementById('meeting-btn')?.classList.add('hidden');
                    })()"
                    class="px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center gap-2"
                    title="Video call">
                    <i data-lucide="video" class="w-4 h-4"></i>
                    <span id="meeting-started" class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    Meeting
                </button>

                <button id="joinBtnLate" data-room="1" data-name="{{ auth()->user()->name }}"
                    data-avatar="{{ auth()->user()->profile_photo_path
                        ? asset('storage/' . auth()->user()->profile_photo_path)
                        : asset('/assets/raw/watermark.svg') }}"
                    class="px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center gap-2 pulse-red-light hidden">
                    <i data-lucide="video" class="w-4 h-4"></i>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 pulse-red-light"></span>
                    <span class="text-sm">Join Meeting (<span id="count_join">1</span>)</span>
                </button>

                <button id="endBtn" onclick="meetEnd()"
                    class="px-3 py-2 rounded-lg border border-red-200 text-danger hover:bg-red-200 flex items-center gap-1">
                    <i data-lucide="video-off" class="w-4 h-4"></i>
                    <span class="text-sm">End Meeting</span>
                </button>


                <button data-hs-overlay="#chatDetails" onclick="trigger_member()"
                    class="px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center gap-2"
                    title="User details">
                    <i data-lucide="info" class="w-4 h-4"></i> Details
                </button>
            </div>
        </header>
        @include('modules.chats.components.callHeader')
        @include('modules.chats.components.chatItems')
        @include('modules.chats.components.inputMessage')
    </main>
</div>

<script>
    function meetEnd() {
        const room = window.ACTIVE_CONV_ID;

        const $header_opt = $('#header-action');
        const $meetingBtn = $('#meeting-btn');
        const $meetingStarted = $('#meeting-started');
        const joinBtn = document.getElementById('joinBtnLate');
        const endBtn = document.getElementById('endBtn');
        const meetBtn = document.getElementById('meeting-btn');
        
        $.post({
                url: `/chats/conversations/${room}/meet/end`,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                },
                data: {
                    meet_join: room,
                    user_id: {{ Auth::user()->id }}
                },
            })
            .done(function(res) {
                $meetingBtn[0]?.classList.remove('pulse-red-light');
                $meetingStarted[0]?.classList.remove('pulse-red-light');
                $meetingStarted[0]?.classList.remove('add');
                meetBtn.classList.remove('hidden');
                joinBtn.classList.add('hidden');
                endBtn.classList.add('hidden');
            })
            .fail(function(xhr) {
                console.error("Error fetching info:", xhr.responseText);
            });
    }
</script>

@include('modules.chats.components.newDirectChat')
@include('modules.chats.components.newGroupChat')

@include('modules.chats.components.details')

<script>
    // Render icons (once)
    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());

    // Optional: filter sidebar list
    const q = document.getElementById('sidebarSearch');
    const list = document.getElementById('convList');
    if (q && list) {
        q.addEventListener('input', e => {
            const v = e.target.value.toLowerCase();
            list.querySelectorAll('.conv').forEach(item => {
                const txt = item.textContent.toLowerCase();
                item.style.display = txt.includes(v) ? '' : 'none';
            });
        });
    }
</script>
