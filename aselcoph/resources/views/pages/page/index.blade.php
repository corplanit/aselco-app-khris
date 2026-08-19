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
                        @include('pages.actions.table-mod')

                        <div class="table-responsive-n bg-white">
                            <table id="pagesTable"
                                class="table table-sm min-w-full !border border-defaultborder dark:border-defaultborder/10">
                                <thead>
                                    <tr class="border-b border-defaultborder dark:border-defaultborder/10">
                                        <th class="text-start" style="width: 5px">
                                            <input type="checkbox" class="form-check-input mx-3" id="selectAll">
                                        </th>
                                        <th class="text-start" style="width: 320px">Label</th>
                                        <th class="text-start">Slug</th>
                                        <th class="text-start" style="width: 90px">Type</th>
                                        <th class="text-start" style="width: 100px">Target</th>
                                        <th class="text-start" style="width: 160px">Menu</th>
                                        <th class="text-start" id="action_th">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <script>
                            $(document).ready(function() {
                                const table = $('#pagesTable').DataTable({
                                    processing: true,
                                    serverSide: false, // client-side (API returns plain array)
                                    ajax: {
                                        url: "{{ route('api.pages') }}",
                                        type: 'POST',
                                        headers: {
                                            'X-CSRF-Token': '{{ csrf_token() }}'
                                        },
                                        dataSrc: '', // because API returns an array
                                        beforeSend: function() {
                                            $("#customLoader")?.show();
                                        },
                                        complete: function() {
                                            $("#customLoader")?.hide();
                                        }
                                    },
                                    language: {
                                        search: "_INPUT_",
                                        searchPlaceholder: "Search pages...",
                                    },
                                    pageLength: 25,
                                    order: [
                                        [2, 'asc']
                                    ], // by 'order' asc
                                    columns: [
                                        // Select
                                        {
                                            data: 'id',
                                            orderable: false,
                                            searchable: false,
                                            className: "text-start",
                                            render: function(id) {
                                                return `<input type="checkbox" class="rowCheckbox form-check-input mx-3" value="${id}">`;
                                            }
                                        },
                                        // Label (+ icon hint if you use one)
                                        {
                                            data: 'label',
                                            className: "text-start fw-semibold",
                                            render: function(label, type, row) {
                                                const icon = row.icon ? `<i class="${row.icon} me-1 text-muted"></i>` :
                                                    '';
                                                return `<span class="inline-flex items-center gap-2">${icon}${_.escape(label)}</span>`;
                                            }
                                        },
                                        // Link / Route (mono + truncate)
                                        {
                                            data: 'link',
                                            className: "text-start",
                                            render: function(val, type, row) {
                                                const text = (val || '').toString();
                                                const pretty = _.escape(text);
                                                return `<span class="font-mono text-[12px] text-gray-700 d-inline-block text-truncate" style="max-width:420px" title="${pretty}">/${pretty || '<span class="text-muted">—</span>'}</span>`;
                                            }
                                        },
                                        // Type badge
                                        {
                                            data: 'link_type',
                                            className: "text-start",
                                            render: function(val) {
                                                if (val === 'route') {
                                                    return `<span class="inline-flex px-2 py-0.5 rounded text-xs bg-indigo-50 text-indigo-700 border border-indigo-200">Route</span>`;
                                                }
                                                return `<span class="inline-flex px-2 py-0.5 rounded text-xs bg-sky-50 text-sky-700 border border-sky-200">URL</span>`;
                                            }
                                        },
                                        // Target badge
                                        {
                                            data: 'target',
                                            className: "text-start",
                                            render: function(val) {
                                                if (val === '_blank') {
                                                    return `<span class="inline-flex px-2 py-0.5 rounded text-xs bg-amber-50 text-amber-700 border border-amber-200">New Tab</span>`;
                                                }
                                                return `<span class="inline-flex px-2 py-0.5 rounded text-xs bg-gray-50 text-gray-700 border border-gray-200">Same Tab</span>`;
                                            }
                                        },
                                        // Menu name
                                        {
                                            data: 'menu',
                                            className: "text-start",
                                            render: (v) => v ? _.escape(v) : '<span class="text-muted">—</span>'
                                        },
                                        // Actions
                                        {
                                            data: 'id',
                                            orderable: false,
                                            searchable: false,
                                            className: "text-start",
                                            render: function(id, type, row) {
                                                return `
                                                <div class="hstack gap-1 text-[.9375rem]">
                                                <span class="custom-tooltip">
                                                    <a href="{{ url('/menu-items') }}/${id}/edit" class="ti-btn ti-btn-sm ti-btn-soft-warning bg-warning/20">
                                                    <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <span class="tooltip-text">Edit</span>
                                                </span>

                                                <span class="custom-tooltip">
                                                    <form action="{{ url('/menu-items') }}/${id}" method="POST" onsubmit="return confirm('Delete this page?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="ti-btn ti-btn-sm ti-btn-soft-danger bg-danger/10">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    </form>
                                                    <span class="tooltip-text">Trash</span>
                                                </span>
                                                </div>`;
                                            }
                                        },
                                    ],
                                    initComplete: function() {
                                        $("#customSearchWrapper").html($("#pagesTable_filter"));
                                        $("#customLengthWrapper").html($("#pagesTable_length"));
                                    }
                                });

                                // Select all
                                $('#selectAll').on('change', function() {
                                    const checked = this.checked;
                                    $('.rowCheckbox').prop('checked', checked);
                                });
                            });
                        </script>

                        <style>
                            tr {
                                cursor: pointer;
                            }

                            #action_th {
                                width: 120px !important;
                            }
                        </style>

                        {{-- lodash (for _.escape). Remove if you prefer a tiny custom escaper. --}}
                        <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>


                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
