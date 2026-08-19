
<div id="newGroupModal" class="hs-overlay ti-modal pointer-events-none hidden mt-6">
    <div class="hs-overlay ti-modal-box mt-0 lg:!max-w-2xl lg:w-full m-3 items-center justify-center">
        <div class="max-h-full w-100 overflow-hidden ti-modal-content">
            <div class="ti-modal-header">
                <h6 class="modal-title text-[1rem] font-semibold modal-title" id="form-header">
                    Create Group
                </h6>
                <button type="button" class="hs-dropdown-toggle ti-modal-close-btn" data-hs-overlay="#newGroupModal">
                    <span class="sr-only">Close</span>
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <form id="newGroupForm" autocomplete="off" class="bg-white shadow-md rounded-lg">
                @csrf
                <div class="ti-modal-body p-5 space-y-4">
                    <!-- Group name -->
                    <div>
                        <label class="text-sm font-medium">Group name</label>
                        <input id="groupName" name="name" type="text" placeholder="e.g., Design Sprint"
                               class="mt-1 w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    </div>

                    <!-- Search filter (client-side) -->
                    <div class="relative">
                        <i class="bi bi-search w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input id="memberSearch" type="text" placeholder="Search People..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    </div>

                    <!-- Member list -->
                    <div class="max-h-72 overflow-auto divide-y divide-gray-100" id="memberList">
                        @foreach(App\Models\User::select('id','name','role','profile_photo_path')->where('id','!=',auth()->id())->get() as $user)
                            <label class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50 cursor-pointer member-row" data-name="{{ Str::lower($user->name.' '.$user->role) }}">
                                <input type="checkbox" class="rounded border-gray-300 form-checkbox member-checkbox"
                                       name="member_ids[]" value="{{ $user->id }}">
                                {{-- <img src="{{ $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path)  : '/user.png' }}"
                                     onerror="this.src = '/user.png'" class="w-8 h-8 rounded-full"> --}}
                                <div class="text-md">
                                    <div class="font-semibold">{{ $user->name }}</div>
                                    <div class="text-md text-gray-500">{{ $user->role }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Error box -->
                    <div id="groupErrors" class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded p-2"></div>
                </div>

                <div class="ti-modal-footer p-4 border-t border-gray-200 flex items-center justify-end gap-2">
                    <button type="button" class="px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50" data-hs-overlay="#newGroupModal">
                        Cancel
                    </button>
                    <button type="button" id="createGroupBtn"
                            class="px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 disabled:opacity-60">
                        <i class="bi bi-people"></i>
                        <span class="btn-text">Create Group</span>
                        <span class="btn-spinner hidden animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    $(function () {
        const $modal = $('#newGroupModal');
        const $form = $('#newGroupForm');
        const $btn = $('#createGroupBtn');
        const $btnText = $btn.find('.btn-text');
        const $btnSpinner = $btn.find('.btn-spinner');
        const $errors = $('#groupErrors');

        // Client-side filter
        $('#memberSearch').on('input', function(){
            const q = this.value.trim().toLowerCase();
            $('#memberList .member-row').each(function(){
                const hay = $(this).data('name');
                $(this).toggle(hay.indexOf(q) !== -1);
            });
        });

        function setLoading(on) {
            $btn.prop('disabled', on);
            $btnSpinner.toggleClass('hidden', !on);
            $btnText.text(on ? 'Creating...' : 'Create Group');
        }

        function showErrors(list) {
            if (!list || !list.length) { $errors.addClass('hidden').empty(); return; }
            $errors.removeClass('hidden').html(
                '<ul class="list-disc ps-5">' + list.map(e => `<li>${e}</li>`).join('') + '</ul>'
            );
        }

        $('#createGroupBtn').on('click', function () {
            showErrors([]);

            const name = $('#groupName').val().trim();
            const memberIds = $('.member-checkbox:checked').map(function(){ return parseInt(this.value, 10); }).get();

            // Basic client-side checks
            const errs = [];
            if (!name) errs.push('Group name is required.');
            if (memberIds.length === 0) errs.push('Please select at least one member.');
            if (errs.length) { showErrors(errs); return; }

            setLoading(true);

            $.ajax({
                url: '/chats/conversations/group',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                contentType: 'application/json; charset=UTF-8',
                dataType: 'json',
                data: JSON.stringify({ name: name, member_ids: memberIds }),
                success: function (res) {
                    setLoading(false);

                    // Reset form
                    $form[0].reset();
                    $('.member-checkbox').prop('checked', false);
                    $('#memberSearch').val('').trigger('input');

                    // Close HS overlay
                    if (window.HSOverlay && $modal[0]) { window.HSOverlay.close($modal[0]); }

                    // Refresh your sidebar list (if you have a function). Fallback: reload.
                    if (typeof window.fetchConversations === 'function') {
                        window.fetchConversations();
                    } else {
                        location.reload();
                    }
                },
                error: function (xhr) {
                    setLoading(false);
                    let msgs = ['Something went wrong. Please try again.'];
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        msgs = [];
                        Object.values(xhr.responseJSON.errors).forEach(arr => { arr.forEach(m => msgs.push(m)); });
                    }
                    showErrors(msgs);
                }
            });
        });
    });
</script>
