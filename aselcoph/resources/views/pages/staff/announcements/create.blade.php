<x-app-layout>
    <x-slot name="title">New Announcement</x-slot>
    <x-slot name="url_1">{"link": "/announcements", "text": "Announcements"}</x-slot>
    <x-slot name="url_2">{"link": "/announcements/create", "text": "Create"}</x-slot>
    <x-slot name="active">Compose</x-slot>
    <x-slot name="buttons">
        <a href="{{ route('announcements.index') }}" class="ti-btn ti-btn-soft-secondary !border-0 btn-wave me-0">
            Back to list
        </a>
    </x-slot>

    <div class="box">
        <div class="box-body">
            <p class="mb-3 text-sm text-muted">
                Choose an audience, then <strong>Save draft</strong> or <strong>Publish &amp; push</strong>.
                Meter targeting is for testing a specific consumer base by meter number.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('announcements.store') }}" id="announcement-form">
                @csrf

                <div class="mb-3">
                    <label class="form-label font-semibold">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" maxlength="180"
                        class="form-control" required placeholder="e.g. Scheduled interruption — Brgy. Sample">
                </div>

                <div class="mb-3">
                    <label class="form-label font-semibold">Message</label>
                    <textarea name="body" rows="5" class="form-control" required maxlength="5000"
                        placeholder="Short message shown in the mobile notification inbox">{{ old('body') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label font-semibold">Category</label>
                    <select name="category" class="form-control" required>
                        <option value="alert" @selected(old('category', 'alert') === 'alert')>Alert (general)</option>
                        <option value="service" @selected(old('category') === 'service')>Service / outage</option>
                        <option value="billing" @selected(old('category') === 'billing')>Billing</option>
                    </select>
                    <div class="text-xs text-muted mt-1">Respects each member’s mobile notification preferences.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-semibold">Audience</label>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2 p-3 border rounded">
                            <input type="radio" name="audience_type" value="all" class="mt-1"
                                @checked(old('audience_type', 'all') === 'all')>
                            <span>
                                <strong>All members</strong>
                                <div class="text-xs text-muted">Verified mobile members (excludes common staff roles).</div>
                            </span>
                        </label>
                        <label class="flex items-start gap-2 p-3 border rounded">
                            <input type="radio" name="audience_type" value="users" class="mt-1"
                                @checked(old('audience_type') === 'users')>
                            <span>
                                <strong>Specific users</strong>
                                <div class="text-xs text-muted">Pick one or more accounts from the user list.</div>
                            </span>
                        </label>
                        <label class="flex items-start gap-2 p-3 border rounded">
                            <input type="radio" name="audience_type" value="meter" class="mt-1"
                                @checked(old('audience_type') === 'meter')>
                            <span>
                                <strong>Meter number(s) — testing</strong>
                                <div class="text-xs text-muted">Resolve members linked to these meter / account numbers.</div>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="mb-3" id="audience-users" style="display:none">
                    <label class="form-label font-semibold">Select users</label>
                    <input type="search" id="audience-user-search" class="form-control mb-2"
                        placeholder="Search by name or email…" autocomplete="off">
                    <select name="audience_user_ids[]" id="audience-user-select" class="form-control" multiple size="10">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}"
                                data-label="{{ strtolower($user->name.' '.$user->email) }}"
                                @selected(collect(old('audience_user_ids', []))->contains($user->id))>
                                {{ $user->name }} — {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                    <div class="text-xs text-muted mt-1 flex justify-between gap-2 flex-wrap">
                        <span>Hold Ctrl/Cmd to select multiple.</span>
                        <span id="audience-user-filter-hint"></span>
                    </div>
                </div>

                <div class="mb-3" id="audience-meter" style="display:none">
                    <label class="form-label font-semibold">Meter number(s)</label>
                    <textarea name="meter_numbers" rows="3" class="form-control"
                        placeholder="One or more meter numbers, separated by comma or new line">{{ old('meter_numbers') }}</textarea>
                    <div class="text-xs text-muted mt-1">
                        Looks up <code>t_accounts_raw.meter_no</code> (and account number as fallback), then the linked member user.
                    </div>
                </div>

                <div class="mb-4 p-3 border rounded bg-light">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div>
                            <strong>Audience preview:</strong>
                            <span id="preview-count">—</span> member(s)
                        </div>
                        <button type="button" class="ti-btn ti-btn-sm ti-btn-soft-primary" id="btn-preview">
                            Refresh preview
                        </button>
                    </div>
                    <ul id="preview-list" class="mt-2 mb-0 text-sm"></ul>
                </div>

                <div class="flex gap-2 flex-wrap">
                    <button type="submit" name="action" value="draft" class="ti-btn ti-btn-soft-secondary">
                        Save draft
                    </button>
                    <button type="submit" name="action" value="publish" class="ti-btn ti-btn-primary text-white bg-primary"
                        onclick="return confirm('Publish now and send push notifications to the audience?')">
                        Publish &amp; push
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const usersBox = document.getElementById('audience-users');
            const meterBox = document.getElementById('audience-meter');
            const radios = document.querySelectorAll('input[name="audience_type"]');
            const previewCount = document.getElementById('preview-count');
            const previewList = document.getElementById('preview-list');
            const userSearch = document.getElementById('audience-user-search');
            const userSelect = document.getElementById('audience-user-select');
            const filterHint = document.getElementById('audience-user-filter-hint');

            function filterUsers() {
                const q = (userSearch.value || '').trim().toLowerCase();
                let visible = 0;
                userSelect.querySelectorAll('option').forEach((opt) => {
                    const match = !q || (opt.dataset.label || opt.textContent.toLowerCase()).includes(q);
                    // Keep already-selected users visible so they stay easy to deselect
                    const show = match || opt.selected;
                    opt.hidden = !show;
                    if (show) visible += 1;
                });
                const total = userSelect.options.length;
                filterHint.textContent = q
                    ? `Showing ${visible} of ${total}`
                    : `${total} user(s)`;
            }

            userSearch.addEventListener('input', filterUsers);
            filterUsers();

            function syncAudiencePanels() {
                const selected = document.querySelector('input[name="audience_type"]:checked')?.value || 'all';
                usersBox.style.display = selected === 'users' ? '' : 'none';
                meterBox.style.display = selected === 'meter' ? '' : 'none';
                if (selected === 'users') {
                    userSearch.focus();
                }
            }

            radios.forEach((radio) => radio.addEventListener('change', syncAudiencePanels));
            syncAudiencePanels();

            async function refreshPreview() {
                const selected = document.querySelector('input[name="audience_type"]:checked')?.value || 'all';
                const formData = new FormData();
                formData.append('audience_type', selected);
                formData.append('_token', '{{ csrf_token() }}');

                if (selected === 'users') {
                    document.querySelectorAll('#audience-user-select option:checked').forEach((opt) => {
                        formData.append('audience_user_ids[]', opt.value);
                    });
                }
                if (selected === 'meter') {
                    formData.append('meter_numbers', document.querySelector('textarea[name="meter_numbers"]').value || '');
                }

                previewCount.textContent = '…';
                previewList.innerHTML = '';

                try {
                    const res = await fetch('{{ route('announcements.preview') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });
                    const data = await res.json();
                    previewCount.textContent = data.count ?? 0;
                    (data.preview || []).forEach((row) => {
                        const li = document.createElement('li');
                        li.textContent = `${row.name} (${row.email})`;
                        previewList.appendChild(li);
                    });
                    if ((data.count || 0) > (data.preview || []).length) {
                        const more = document.createElement('li');
                        more.className = 'text-muted';
                        more.textContent = `…and ${(data.count || 0) - (data.preview || []).length} more`;
                        previewList.appendChild(more);
                    }
                } catch (e) {
                    previewCount.textContent = 'error';
                }
            }

            document.getElementById('btn-preview').addEventListener('click', refreshPreview);
            refreshPreview();
        })();
    </script>
</x-app-layout>
