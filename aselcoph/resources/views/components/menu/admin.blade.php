<li class="slide">
    <a href="/calendar" class="side-menu__item">
        <i class="w-6 h-4 side-menu__icon bi bi-calendar-event" style="color: #5D66F7"></i>
        <span class="side-menu__label">Calendar Activities</span>
    </a>
</li>
<li class="slide__category"><span class="category-name">Customer Relationsip</span></li>
<li class="slide">
    <a href="/consumer/list" class="side-menu__item">
        <i class="w-6 h-4 side-menu__icon bi bi-people" style="color: #5D66F7"></i>
        <span class="side-menu__label">List of Consumers</span>
    </a>
</li>
<li class="slide">
    <a href="/validation" class="side-menu__item">
        <i class="w-6 h-4 side-menu__icon bi bi-people" style="color: #5D66F7"></i>
        <span class="side-menu__label">
            Account Request
            @php
                $count = App\Models\AccountLink::whereNull('validated_by', '')->count();
            @endphp
            @if ($count)
                <span class="mx-2 translate-middle badge !rounded-full bg-danger">
                    {{ $count }}
                </span>
            @endif
        </span>
    </a>
</li>
{{-- <li class="slide__category"><span class="category-name">Content Management</span></li>
<li class="slide">
    <a href="/ublog" class="side-menu__item">
        <i class="w-6 h-4 side-menu__icon bi bi-newspaper" style="color: #5D66F7"></i>
        <span class="side-menu__label">List of Articles</span>
    </a>
</li>
<li class="slide">
    <a href="/ublog/new" class="side-menu__item">
        <i class="w-6 h-4 side-menu__icon bi bi-pencil-square" style="color: #5D66F7"></i>
        <span class="side-menu__label">Create New Article</span>
    </a>
</li> --}}
<li class="slide__category"><span class="category-name">Manage Services</span></li>

<li class="slide">
    <a href="/announcements" class="side-menu__item">
        <i class="w-6 h-4 side-menu__icon bi bi-megaphone" style="color: #5D66F7"></i>
        <span class="side-menu__label">Mobile Announcements</span>
    </a>
</li>

<li class="slide">
    <a href="/supp/chat" class="side-menu__item relative" data-unread-badge-anchor="support-messages">
        <i class="w-6 h-4 side-menu__icon bi bi-headset" style="color: #5D66F7"></i>
        <span class="side-menu__label">Customer Support</span>

        @php
            $uid = Auth::id();
            $unread = 0;

            if (Auth::user()->role == 'support' || Auth::user()->role == 'administrator') {
                $parts = \App\Models\SuppParticipant::query()
                    ->where('user_id', $uid)
                    ->get(['conversation_id', 'last_read_message_id']);

                foreach ($parts as $p) {
                    $q = \App\Models\SuppMessage::query()
                        ->where('conversation_id', $p->conversation_id)
                        ->where('user_id', '!=', $uid);

                    if ($p->last_read_message_id) {
                        $q->where('id', '>', $p->last_read_message_id);
                    }

                    $unread += $q->count();
                }
            }
        @endphp

        <span id="count_unread_msg" class="translate-middle badge !rounded-full bg-danger absolute top-0 end-0"
            style="{{ $unread > 0 ? '' : 'display:none' }}">
            {{ $unread > 9 ? '9+' : $unread }}
        </span>
    </a>
</li>

<li class="slide">
    <a href="/complaint" class="side-menu__item">
        <i class="w-6 h-4 side-menu__icon bi bi-hand-index" style="color: #5D66F7"></i>
        <span class="side-menu__label">Customer Complaint</span>
    </a>
</li>
<li class="slide">
    <a href="#" onclick="openUpdateSwal()" class="side-menu__item">
        <i class="w-6 h-4 side-menu__icon bi bi-graph-up-arrow" style="color: #5D66F7"></i>
        <span class="side-menu__label">Satisfaction Survey</span>
    </a>
</li>
</li>

<li class="slide__category"><span class="category-name">User Management</span></li>
<li class="slide">
    <a href="/users" class="side-menu__item">
        <i class="w-6 h-4 side-menu__icon bi bi-person-gear" style="color: #5D66F7"></i>
        <span class="side-menu__label">User Management</span>
    </a>
</li>

<script>
function openUpdateSwal() {

    // ✅ Safe blade-to-JS (handles quotes, etc.)
    const currentLink = @json(optional(\App\Models\Survery::find(1))->link);

    Swal.fire({
        title: 'Update Survey Link',
        html: `
            <div class="text-start">
                <label class="text-sm font-semibold mb-1">Survey Link</label>

                <div style="display:flex; gap:8px; align-items:center;">
                    <input id="swal-link-input"
                        class="swal2-input"
                        value="${currentLink || ''}"
                        style="flex:1; margin:0; height:42px;">

                    <button type="button" id="copy-btn"
                        class="swal2-styled"
                        style="background:#6c757d; padding:6px 10px; height:42px;">
                        📋
                    </button>
                </div>

                <small style="display:block; margin-top:8px; opacity:.7;">
                    Tip: Click 📋 to copy the link.
                </small>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText: 'Cancel',

        didOpen: () => {
            const copyBtn = document.getElementById('copy-btn');
            copyBtn.addEventListener('click', async () => {
                const input = document.getElementById('swal-link-input');
                const text = input.value || '';

                try {
                    await navigator.clipboard.writeText(text);
                    Swal.showValidationMessage('Copied to clipboard ✔');
                } catch (e) {
                    // fallback
                    input.select();
                    document.execCommand('copy');
                    Swal.showValidationMessage('Copied to clipboard ✔');
                }

                setTimeout(() => Swal.resetValidationMessage(), 1000);
            });
        },

        preConfirm: () => {
            const value = document.getElementById('swal-link-input').value.trim();
            if (!value) {
                Swal.showValidationMessage('Survey link is required');
                return false;
            }
            return value;
        }

    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: '/survey/update-link',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: {
                id: 1,
                link: result.value
            },
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Survey link updated successfully',
                    timer: 1400,
                    showConfirmButton: false
                });

                // ✅ simplest: refresh page or update a label if you have one
                setTimeout(() => window.location.reload(), 900);
            },
            error: function(xhr) {
                let msg = 'Failed to update survey link';
                if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                Swal.fire('Error', msg, 'error');
            }
        });
    });
}
</script>



{{-- <li class="slide__category"><span class="category-name">Administrator</span></li>
<li class="slide has-sub" id="profit-tracker-menu">
    <a href="javascript:void(0);" class="side-menu__item">
        <i class="ri-arrow-down-s-line side-menu__angle"></i>
        <i class="w-6 h-4 side-menu__icon bi bi-globe-americas" style="color: #5D66F7"></i>
        <span class="side-menu__label">Landing Page</span>
    </a>
    <ul class="slide-menu child1" style="padding-left: 10px">
        <li class="slide side-menu__label1">
            <a href="javascript:void(0)">Landing Page</a>
        </li>
        <li class="slide" id="income-tracking-menu">
            <a href="/ublog" class="side-menu__item">Manage Post</a>
        </li>
        <li class="slide" id="profit-tracker-menu-1">
            <a href="/pages" class="side-menu__item">Manage Pages</a>
        </li>
        <li class="slide" id="expense-tracking-menu">
            <a href="/file-manager/list" class="side-menu__item">Manage Resources</a>
        </li>
        <li class="slide" id="expense-tracking-menu">
            <a href="/menus" class="side-menu__item">Manage Menu</a>
        </li>
    </ul>
</li> --}}
