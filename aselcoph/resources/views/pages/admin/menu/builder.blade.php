<x-app-layout>
    <x-slot name="title">Menu Builder — {{ $menu->name }}</x-slot>
    <x-slot name="url_1">{"link":"{{ route('menus.index') }}", "text":"Menus"}</x-slot>
    <x-slot name="active">Builder</x-slot>
    <x-slot name="buttons">
        <a href="{{ route('menus.index') }}" class="ti-btn ti-btn-light text-dark bg-white !border-0 btn-wave me-0">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        <button id="saveTreeBtn" class="ti-btn ti-btn-primary btn-wave">
            <i class="bi bi-check2-circle me-1"></i>Save Order
        </button>
    </x-slot>

    <div class="grid grid-cols-12 gap-6">
        {{-- Left: Add Custom Link --}}
        <div class="xl:col-span-4 col-span-12">
            <div class="box custom-box">
                <div class="box-header p-4 border-b">
                    <h2 class="text-sm font-semibold text-gray-800">Add Custom Link</h2>
                </div>
                <div class="box-body p-4 space-y-3">
                    <form id="addItemForm" class="space-y-3">
                        @csrf
                        <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Label</label>
                            <input name="label" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Home" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">URL</label>
                            <input name="custom_url" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="https://..." required>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-medium text-gray-700">Open in new tab</label>
                            <input type="checkbox" name="target_blank" class="ti-form-checkbox">
                        </div>
                        <button class="ti-btn ti-btn-primary btn-wave w-full" type="submit">
                            <i class="bi bi-plus-lg me-1"></i>Add to Menu
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: Sortable Tree --}}
        <div class="xl:col-span-8 col-span-12">
            <div class="box custom-box">
                <div class="box-header p-4 border-b flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-800">Menu Structure</h2>
                    <div class="flex gap-2">
                        <button id="expandAllBtn" class="ti-btn ti-btn-light btn-wave">Expand all</button>
                        <button id="collapseAllBtn" class="ti-btn ti-btn-light btn-wave">Collapse all</button>
                    </div>
                </div>

                <div class="box-body p-4">
                    <p class="text-xs text-gray-500 mb-3">
                        Drag items to reorder. Drop onto another item to nest. Click an item to edit label/link.
                    </p>

                    <ul id="menuRoot"
                        class="wp-like-list space-y-2"
                        data-parent-id="">
                        @include('pages.admin.menu.partials.tree', ['items' => $menu->itemsWithChildren])
                    </ul>

                    <div id="savingState" class="hidden mt-3 text-xs text-gray-500">Saving…</div>
                    <div id="savedState" class="hidden mt-3 text-xs text-emerald-600">Saved!</div>
                </div>
            </div>
        </div>
    </div>

    {{-- SortableJS CDN (vanilla) --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <style>
        /* WordPress-like card rows */
        .menu-item-card { @apply rounded-lg border border-gray-200 bg-white shadow-sm; }
        .menu-item-header { @apply flex items-center gap-3 px-3 py-2 cursor-pointer; }
        .drag-handle { @apply text-gray-400 hover:text-gray-600 cursor-grab; }
        .menu-item-body { @apply border-t border-gray-100 px-3 py-3 hidden; }
        .menu-children { @apply ml-6 mt-2 space-y-2; }
        .menu-item-collapsed .menu-item-body { display: none; }
        .menu-item-expanded .menu-item-body { display: block; }
    </style>

    <script>
    (function(){
        const csrf = '{{ csrf_token() }}';
        const menuId = {{ $menu->id }};
        const root = document.getElementById('menuRoot');
        const saveBtn = document.getElementById('saveTreeBtn');
        const saving = document.getElementById('savingState');
        const saved  = document.getElementById('savedState');

        // Toggle expand/collapse per item
        document.addEventListener('click', (e) => {
            const header = e.target.closest('.menu-item-header');
            if (header && !e.target.closest('button, input, a, select, label')) {
                const card = header.closest('li');
                card.classList.toggle('menu-item-expanded');
            }
        });

        // Expand/Collapse all
        document.getElementById('expandAllBtn')?.addEventListener('click', () => {
            document.querySelectorAll('#menuRoot > li, #menuRoot li li').forEach(li => li.classList.add('menu-item-expanded'));
        });
        document.getElementById('collapseAllBtn')?.addEventListener('click', () => {
            document.querySelectorAll('#menuRoot > li, #menuRoot li li').forEach(li => li.classList.remove('menu-item-expanded'));
        });

        // Initialize Sortable recursively
        function makeSortable(ul){
            new Sortable(ul, {
                group: 'menu',
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'opacity-50',
                onAdd: onChange,
                onUpdate: onChange,
                onSort: onChange
            });

            // ensure all nested ULs inside have Sortable too
            ul.querySelectorAll(':scope > li > ul').forEach(childUL => makeSortable(childUL));
        }

        function onChange(){
            // show saved hint but do NOT auto-save yet; WordPress usually requires click Save.
        }

        makeSortable(root);

        // Serialize tree
        function serializeTree() {
            const out = [];

            function walk(ul, parentId) {
                const children = Array.from(ul.children);
                children.forEach((li, idx) => {
                    const id = parseInt(li.dataset.id, 10);
                    out.push({ id, parent_id: parentId || null, order: idx });

                    const sub = li.querySelector(':scope > ul');
                    if (sub) walk(sub, id);
                });
            }

            walk(root, null);
            return out;
        }

        async function saveTree(){
            saving.classList.remove('hidden');
            saved.classList.add('hidden');
            const items = serializeTree();

            const res = await fetch('{{ route('menu-items.saveTree') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    menu_id: menuId,
                    items
                })
            });
            saving.classList.add('hidden');
            if (res.ok) {
                saved.classList.remove('hidden');
                setTimeout(()=>saved.classList.add('hidden'), 1500);
            } else {
                const msg = await res.text();
                alert('Failed to save order:\n' + msg);
            }
        }

        saveBtn?.addEventListener('click', saveTree);

        // Add custom link
        document.getElementById('addItemForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.currentTarget;
            const fd = new FormData(form);
            const payload = {
                menu_id: fd.get('menu_id'),
                parent_id: null,
                label: fd.get('label'),
                link_type: 'url',
                custom_url: fd.get('custom_url'),
                target: fd.get('target_blank') ? '_blank' : '_self',
                order: document.querySelectorAll('#menuRoot > li').length
            };

            const res = await fetch('{{ route('menu-items.store') }}', {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                // Reload to simplify state sync (safe + simple)
                location.reload();
            } else {
                const msg = await res.text();
                alert('Failed to add item:\n' + msg);
            }
        });

        // Inline edit per item
        document.addEventListener('submit', async (e) => {
            const form = e.target.closest('.edit-item-form');
            if (!form) return;

            e.preventDefault();
            const id = form.dataset.id;
            const fd = new FormData(form);

            const linkType = fd.get('link_type');
            const body = {
                label: fd.get('label'),
                icon: fd.get('icon') || null,
                link_type: linkType,
                target: fd.get('target'),
                is_active: fd.get('is_active') ? true : false,
                // Only send relevant link fields
                custom_url: linkType === 'url' ? (fd.get('custom_url') || null) : null,
                route_name: linkType === 'route' ? (fd.get('route_name') || null) : null,
            };

            // route params (optional JSON)
            const rp = fd.get('route_params');
            if (linkType === 'route' && rp) {
                try { body.route_params = JSON.parse(rp); } catch (e) { alert('Route params must be valid JSON.'); return; }
            }

            const res = await fetch(`{{ url('menu-items') }}/${id}`, {
                method: 'PATCH',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(body)
            });

            if (!res.ok) {
                const msg = await res.text();
                alert('Failed to update:\n' + msg);
                return;
            }
            // show small tick
            form.querySelector('.save-ok')?.classList.remove('hidden');
            setTimeout(()=>form.querySelector('.save-ok')?.classList.add('hidden'), 1200);
        });

        // Delete item
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-action="delete-item"]');
            if (!btn) return;
            if (!confirm('Remove this menu item?')) return;

            const id = btn.dataset.id;
            const res = await fetch(`{{ url('menu-items') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf }
            });
            if (res.ok) {
                // remove node
                const li = document.querySelector(`li[data-id="${id}"]`);
                li?.remove();
            } else {
                const msg = await res.text();
                alert('Failed to delete:\n' + msg);
            }
        });

    })();
    </script>
</x-app-layout>
