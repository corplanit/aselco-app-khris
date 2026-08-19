<!-- Put this once in your layout head -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="newChatModal" class="hs-overlay ti-modal pointer-events-none hidden mt-6">
    <div class="hs-overlay ti-modal-box mt-0 lg:!max-w-2xl lg:w-full m-3 items-center justify-center">
        <div class="max-h-full w-100 overflow-hidden ti-modal-content">
            <div class="ti-modal-header">
                <h6 class="modal-title text-[1rem] font-semibold">Start Direct Message</h6>
                <button type="button" class="hs-dropdown-toggle ti-modal-close-btn" data-hs-overlay="#newChatModal">
                    <span class="sr-only">Close</span>
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <div class="bg-white shadow-md rounded-lg">
                <div class="ti-modal-body p-5 space-y-4">
                    <!-- Search -->
                    <div class="relative">
                        <i class="bi bi-search w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input id="dmSearch" type="text" placeholder="Search users..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    </div>

                    <!-- User list -->
                    <div class="max-h-72 overflow-auto divide-y divide-gray-100" id="dmUserList">
                        @foreach(App\Models\User::select('id','name','role','profile_photo_path')
                                ->where('id','!=',auth()->id())->orderBy('name')->get() as $user)
                            <button type="button"
                                    class="w-full text-left flex items-center gap-3 px-2 py-2 hover:bg-gray-50 dm-user"
                                    data-user-id="{{ $user->id }}"
                                    data-name="{{ Str::lower($user->name.' '.$user->role) }}">
                                {{-- <img src="{{ $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path) : '/user.png' }}"
                                     onerror="this.src='/user.png'" class="w-8 h-8 rounded-full"> --}}
                                <div class="text-md">
                                    <div class="font-semibold">{{ $user->name }}</div>
                                    <div class="text-md text-gray-500">{{ $user->role }}</div>
                                </div>
                                <span class="ml-auto hidden spinner animate-spin w-4 h-4 border-2 border-gray-400 border-t-transparent rounded-full"></span>
                            </button>
                        @endforeach
                    </div>

                    <!-- Error box -->
                    <div id="dmErrors" class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded p-2"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $(function () {
        const $modal   = $('#newChatModal');
        const $errors  = $('#dmErrors');
        const csrf     = $('meta[name="csrf-token"]').attr('content');

        // Client-side filter
        $('#dmSearch').on('input', function(){
            const q = this.value.trim().toLowerCase();
            $('#dmUserList .dm-user').each(function(){
                const hay = $(this).data('name') || '';
                $(this).toggle(hay.indexOf(q) !== -1);
            });
        });

        function showErrors(msgs) {
            if (!msgs || !msgs.length) { $errors.addClass('hidden').empty(); return; }
            $errors.removeClass('hidden').html(
                '<ul class="list-disc ps-5">' + msgs.map(m => `<li>${m}</li>`).join('') + '</ul>'
            );
        }

        // Start DM on click
        $('#dmUserList').on('click', '.dm-user', function(){
            const $btn    = $(this);
            const userId  = parseInt($btn.data('user-id'), 10);
            const $spin   = $btn.find('.spinner');

            showErrors([]);

            if (!userId || isNaN(userId)) {
                showErrors(['Invalid user selected.']);
                return;
            }

            // Loading state for the clicked row
            $spin.removeClass('hidden');
            $btn.prop('disabled', true);

            $.ajax({
                url: '/chats/conversations/dm',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                contentType: 'application/json; charset=UTF-8',
                dataType: 'json',
                data: JSON.stringify({ user_id: userId }),
                success: function (res) {
                    // Close HS overlay
                    if (window.HSOverlay && $modal[0]) { window.HSOverlay.close($modal[0]); }

                    // Refresh sidebar if available, else reload to show the new/existing DM
                    if (typeof window.fetchConversations === 'function') {
                        window.fetchConversations();
                    } else {
                        location.reload();
                    }

                    // Optionally, dispatch an event so the message pane can open that conversation
                    // window.dispatchEvent(new CustomEvent('open-conversation', { detail: { id: res.id } }));
                },
                error: function (xhr) {
                    let msgs = ['Unable to start direct chat. Please try again.'];
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        msgs = [];
                        Object.values(xhr.responseJSON.errors).forEach(arr => arr.forEach(m => msgs.push(m)));
                    }
                    showErrors(msgs);
                },
                complete: function () {
                    $spin.addClass('hidden');
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
