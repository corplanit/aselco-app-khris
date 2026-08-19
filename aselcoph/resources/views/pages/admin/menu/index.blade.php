<x-app-layout>
    <x-slot name="title">Manage Menus</x-slot>
    <x-slot name="url_1">{"link": "{{ route('menus.index') }}", "text": "Manage Menus"}</x-slot>
    <x-slot name="active">List of Menus</x-slot>
    <x-slot name="buttons">
        <a href="{{ route('menus.create') }}" class="ti-btn text-white !border-0 btn-wave me-0"
            style="background-color: #2563eb">
            <i class="bi bi-plus-circle me-1"></i>
            <span class="mx-1" style="font-weight: 400">Create Menu</span>
        </a>
    </x-slot>
    {{-- <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12">
            <div class="box">
                <div class="box-body p-5 main-content-card">
                    <div class="overflow-x-auto">
                        
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12">
            <div class="box shadow-none border">
                <div class="box-body">
                    <i class="bi bi-info-circle px-1"></i>
                    <span>You can manage the _____ here.</span>
                    <hr class="mb-3 mt-3">
                    <div class="custom-box">

                        <table class="min-w-full text-sm">
                            <thead class="text-left text-dark bg-gray-200">
                                <tr class="border">
                                    <th class="p-2">Name</th>
                                    <th class="p-2">Key</th>
                                    <th class="p-2">Items</th>
                                    <th class="p-2 text-center" width="180">Actions</th>
                                </tr>
                            </thead>

                            <tbody id="menusTable" class="divide-y divide-gray-100">
                                @forelse($menus as $m)
                                    @php
                                        // Ensure items collection is present even if not eager-loaded
                                        $rootItems = $m->itemsWithChildren ?? $m->itemsWithChildren()->get();

                                        $countItems = function ($items) use (&$countItems) {
                                            $sum = 0;
                                            foreach ($items as $it) {
                                                $sum++;
                                                if ($it->children && $it->children->count()) {
                                                    $sum += $countItems($it->children);
                                                }
                                            }
                                            return $sum;
                                        };
                                        $totalItems = $countItems($rootItems);

                                        // Small recursive renderer for a compact preview tree
                                        $miniTree = function ($items) use (&$miniTree) {
                                            echo '<ul class="ml-4 mt-2 space-y-1 border-l border-gray-200 pl-3">';

                                            foreach ($items as $it) {
                                                $hasChildren = $it->children && $it->children->count();
                                                $childrenCount = $hasChildren ? $it->children->count() : 0;

                                                echo '<li class="group leading-5">';

                                                // Row
                                                echo '<div class="flex items-start gap-2 text-gray-800 rounded-md px-2 py-1.5 transition-colors hover:bg-gray-50">';

                                                // Caret (shown only if has children)
                                                if ($hasChildren) {
                                                    echo '<button type="button"
                                                        class="mt-0.5 inline-flex size-5 items-center justify-center rounded hover:bg-gray-100"
                                                        data-mini-toggle="' .
                                                                                            e($it->id) .
                                                                                            '"
                                                        aria-label="Toggle children">
                                                    <svg class="w-4 h-4 text-gray-400 transition-transform"
                                                        data-mini-caret="' .
                                                                                            e($it->id) .
                                                                                            '"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7" />
                                                    </svg>
                                                </button>';
                                                } else {
                                                    echo '<span class="mt-0.5 inline-block size-5"></span>';
                                                }

                                                // Bullet
                                                echo '<span class="mt-1 inline-block w-1.5 h-1.5 rounded-full bg-gray-300 group-hover:bg-gray-400"></span>';

                                                // Label + badges
                                                echo '<div class="min-w-0 flex-1">';

                                                echo '<div class="flex flex-wrap items-center gap-1.5">
            <span class="font-medium text-gray-800">' .
                                                    e($it->label) .
                                                    '</span>';

                                                // Link type badge
                                                if ($it->link_type === 'route') {
                                                    echo '<span class="inline-flex items-center rounded px-1 py-0.5 text-[10px] font-medium bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200">Route</span>';
                                                } else {
                                                   
                                                }

                                                // Target badge
                                                if ($it->target === '_blank') {
                                                    echo '<span class="inline-flex items-center rounded px-1 py-0.5 text-[10px] font-medium bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200">New Tab</span>';
                                                }

                                                // Visibility badge
                                                if (!$it->is_active) {
                                                    echo '<span class="inline-flex items-center rounded px-1 py-0.5 text-[10px] font-medium bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-200">Hidden</span>';
                                                }

                                                // Children count (only if has children)
                                                if ($hasChildren) {
                                                    echo '<span class="ml-1 inline-flex items-center rounded px-1 py-0.5 text-[10px] font-medium bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-200">' .
                                                        $childrenCount .
                                                        ' ' .
                                                        \Illuminate\Support\Str::plural('item', $childrenCount) .
                                                        '</span>';
                                                }

                                                echo '</div>'; // badges row

                                                // Link preview (mono, truncated, with tooltip)
                                                $linkText =
                                                    $it->link_type === 'route'
                                                        ? $it->route_name ?? $it->href
                                                        : $it->href;
                                                echo '<div class="text-[11px] text-gray-500 truncate font-mono leading-4"
               title="' .
                                                    e($linkText) .
                                                    '">' .
                                                    e($linkText) .
                                                    '</div>';

                                                echo '</div>'; // min-w-0

                                                echo '</div>'; // row

                                                // Children (collapsible)
                                                if ($hasChildren) {
                                                    echo '<div id="mini-expand-' .
                                                        e($it->id) .
                                                        '" class="ml-5 mt-1 hidden">';
                                                    $miniTree($it->children);
                                                    echo '</div>';
                                                }

                                                echo '</li>';
                                            }

                                            echo '</ul>';
                                        };
                                    @endphp

                                    <!-- MAIN ROW -->
                                    <tr class="hover:bg-gray-50" data-row="{{ $m->id }}">
                                        <td class="p-3 border font-medium text-gray-900">
                                            <button type="button" class="inline-flex items-center gap-2 group"
                                                data-toggle="{{ $m->id }}">
                                                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-transform"
                                                    data-caret="{{ $m->id }}" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="m9 5 7 7-7 7" />
                                                </svg>
                                                <span>{{ $m->name }}</span>
                                                <span
                                                    class="ml-2 inline-flex items-center rounded px-2 py-0.5 text-[10px] font-medium bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-200">
                                                    {{ $totalItems }}
                                                    {{ \Illuminate\Support\Str::plural('item', $totalItems) }}
                                                </span>
                                            </button>
                                        </td>
                                        <td class="p-3 border">
                                            <span
                                                class="inline-flex items-center rounded px-2 py-0.5 text-[11px] font-mono bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200">
                                                {{ $m->key }}
                                            </span>
                                        </td>
                                        <td class="p-3 border text-gray-600">
                                            Sub-Menu: {{ $rootItems->count() }}
                                        </td>
                                        <td class="px-3 border space-x-2">
                                            <a href="{{ route('menus.builder', $m) }}"
                                                class="ti-btn ti-btn-outline-info btn-wave text-dark w-full">
                                                <i class="bi bi-sliders me-1"></i> Manage Items
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- EXPANDABLE CHILD ROW -->
                                    <tr id="expand-{{ $m->id }}" class="hidden bg-white">
                                        <td colspan="4" class="py-3">
                                            @if ($rootItems->count())
                                                <div class="rounded-xl border border-gray-200 p-3">
                                                    <div class=" text-dark flex items-center gap-2 mb-2">
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                                        </svg>
                                                       Sub-Menu Preview
                                                    </div>
                                                    {!! $miniTree($rootItems) !!}
                                                </div>
                                            @else
                                                <div
                                                    class="rounded-xl border-2 border-dashed border-gray-300 p-6 text-center">
                                                    <div class="text-sm font-medium text-gray-700">No items yet</div>
                                                    <div class="text-xs text-gray-500 mt-1">Click “Manage Items” to add
                                                        your first link.</div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-6 text-gray-500" colspan="4">No menus yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <script>
                            (function() {
                                // expand/collapse per menu
                                document.addEventListener('click', (e) => {
                                    const btn = e.target.closest('[data-toggle]');
                                    if (!btn) return;
                                    const id = btn.getAttribute('data-toggle');
                                    const row = document.getElementById('expand-' + id);
                                    const caret = document.querySelector('[data-caret="' + id + '"]');
                                    row.classList.toggle('hidden');
                                    // rotate caret
                                    if (caret) {
                                        caret.style.transform = row.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
                                    }
                                });

                                // filter menus by name/key
                                const input = document.getElementById('menusFilter');
                                const table = document.getElementById('menusTable');
                                input?.addEventListener('input', () => {
                                    const q = input.value.toLowerCase().trim();
                                    table.querySelectorAll('tr[data-row]').forEach(tr => {
                                        const id = tr.getAttribute('data-row');
                                        const name = tr.querySelector('td:nth-child(1)')?.innerText.toLowerCase() || '';
                                        const key = tr.querySelector('td:nth-child(2)')?.innerText.toLowerCase() || '';
                                        const show = name.includes(q) || key.includes(q);
                                        tr.style.display = show ? '' : 'none';
                                        // also hide its expanded child if the main row is hidden
                                        const exp = document.getElementById('expand-' + id);
                                        if (!show) exp?.classList.add('hidden');
                                    });
                                });
                            })();
                        </script>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('change', (e) => {
            if (e.target.name === 'link_type') {
                const form = e.target.closest('form');
                if (!form) return;
                const isUrl = e.target.value === 'url';
                form.querySelector('.link-url')?.style.setProperty('display', isUrl ? '' : 'none');
                form.querySelector('.link-route')?.style.setProperty('display', isUrl ? 'none' : '');
            }
        });
    </script>
</x-app-layout>
