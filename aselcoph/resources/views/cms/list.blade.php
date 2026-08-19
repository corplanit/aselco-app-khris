<x-app-layout>

    <x-slot name="title">Manage Articles</x-slot>
    <x-slot name="url_1">{"link": "/", "text": "Manage"}</x-slot>
    <x-slot name="url_2">{"link": "/", "text": "Articles"}</x-slot>
    <x-slot name="active">List of Articles</x-slot>
    <x-slot name="buttons"></x-slot>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12">
            <div class="box custom-box">
                <div class="box-header">
                    <div class="box-body">
                        @include('pages.actions.table-mod')

                        <div class="table-responsive-n">
                            <table id="blogTable"
                                class="table table-sm min-w-full !border border-defaultborder dark:border-defaultborder/10">
                                <thead>
                                    <tr class="border-b border-defaultborder dark:border-defaultborder/10">
                                        <th class="text-start" style="width: 5px">
                                            <input type="checkbox" class="form-check-input mx-3" id="selectAll">
                                        </th>
                                        <th class="text-start">Title</th>
                                        <th class="text-start">Menu</th>
                                        <th class="text-start">Posted By</th>
                                        <th class="text-start">Status</th>
                                        <th class="text-start">Date Posted</th>
                                        <th class="text-start">Actions</th>
                                    </tr>
                                </thead>
                            </table>

                            <script>
                                $(document).ready(function() {
                                    $('#blogTable').DataTable({
                                        processing: true,
                                        serverSide: true,
                                        ajax: {
                                            url: "{{ route('api.blogs.list') }}",
                                            type: 'GET',
                                        },
                                        language: {
                                            search: "_INPUT_",
                                            searchPlaceholder: "Search blog...",
                                        },
                                        columns: [{
                                                data: 'post_id',
                                                name: 'post_id',
                                                orderable: false,
                                                searchable: false,
                                                render: function(data) {
                                                    return `<input type="checkbox" class="rowCheckbox form-check-input mx-3" value="${data}">`;
                                                }
                                            },
                                            {
                                                data: 'post_title',
                                                name: 'post_title',
                                                className: 'text-start'
                                            },
                                            {
                                                data: 'post_menu',
                                                name: 'post_menu',
                                                className: 'text-start text-capitalize'
                                            },
                                            {
                                                data: 'author_name',
                                                name: 'author_name',
                                                className: 'text-start'
                                            },
                                            {
                                                data: 'post_isActive',
                                                name: 'post_isActive',
                                                className: 'text-start',
                                                render: function(status) {
                                                    return status === 'on' ?
                                                        '<span class="text-green-600">Active</span>' :
                                                        '<span class="text-red-500">Inactive</span>';
                                                }
                                            },
                                            {
                                                data: 'created_at',
                                                name: 'created_at',
                                                className: 'text-start'
                                            },
                                            {
                                                data: 'post_id',
                                                name: 'actions',
                                                orderable: false,
                                                searchable: false,
                                                className: 'text-start',
                                                render: function(id, type, row) {
                                                    return `
                            <div class="hstack gap-1 text-[.9375rem]">
                                <span class="custom-tooltip">
                                    <a href="/ublog/edit/${row.post_id}" class="ti-btn ti-btn-sm bg-primary/10">
                                        <i class="bi bi-pencil-square text-primary"></i> 
                                    </a>
                                    <span class="tooltip-text">Edit</span>
                                </span>
                                <span class="custom-tooltip">
                                    <a href="/blog/${row.post_id}/preview" target="_blank" class="ti-btn ti-btn-sm bg-info/10">
                                        <i class="bi bi-eye text-info"></i> 
                                    </a>
                                    <span class="tooltip-text">View</span>
                                </span>
                                <span class="custom-tooltip">
                                    <a onclick="remove_data(${row.post_id}, 'post')" href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-soft-danger bg-danger/10">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <span class="tooltip-text">Delete</span>
                                </span>
                            </div>`;
                                                }
                                            }
                                        ],
                                        order: [
                                            [5, 'desc'] // Sort by 'created_at'
                                        ],
                                        initComplete: function() {
                                            $("#customSearchWrapper").html($("#blogTable_filter"));
                                            $("#customLengthWrapper").html($("#blogTable_length"));
                                        }
                                    });
                                });
                            </script>


                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
