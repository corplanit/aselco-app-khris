{{-- resources/views/file-manager/partials/file-list.blade.php --}}

@include('pages.actions.table-mod')

<div class="table-responsive">
    <table id="fileListTable" class="table table-sm w-full border border-defaultborder dark:border-defaultborder/10">
        <thead>
            <tr class="border-b border-defaultborder dark:border-defaultborder/10">
                <th class="text-start w-5">
                    <input type="checkbox" class="form-check-input mx-3" id="selectAll">
                </th>
                <th class="text-start">File Name</th>
                <th class="text-start">Uploaded By</th>
                <th class="text-start text-muted">Type</th>
                <th class="text-start w-24">Size</th>
                <th class="text-start">Date Uploaded</th>
                <th class="text-start w-[190px]">Actions</th>
            </tr>
        </thead>
    </table>
</div>

@push('scripts')
<script>
    $(document).on('click', '#fileListTable tbody tr', function(e) {
        if (!$(e.target).closest('button, input[type="checkbox"], a').length) {
            window.open($(this).data('href'), '_blank');
        }
    });

    $(document).ready(function () {
        $('#fileListTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route('api.filemanager.files') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-Token': '{{ csrf_token() }}'
                },
                data: {
                    id: "{{ request()->query('f') }}"
                }
            },
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search files...",
            },
            columns: [
                {
                    data: 'id',
                    render: data => `<input type="checkbox" class="rowCheckbox form-check-input mx-3" value="${data}">`,
                    orderable: false
                },
                { data: 'name', className: 'text-start' },
                {
                    data: 'uploaded',
                    className: 'text-start',
                    render: data => `<img src="/user.png" alt="${data}" class="avatar avatar-sm mb-0 mx-1"> ${data}`
                },
                { data: 'format', className: 'text-start text-muted' },
                { data: 'size', className: 'text-start' },
                { data: 'created_at', className: 'text-start' },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                        <div class="hstack gap-1 text-[.9375rem] text-center">
                            <span class="custom-tooltip">
                                <a onclick="rename_ff(${row.id}, 'File', '${row.name}')" data-hs-overlay="#rename-files-folder" class="ti-btn ti-btn-sm ti-btn-soft-warning bg-warning/20"><i class="bi bi-pencil-square"></i></a>
                                <span class="tooltip-text">Rename</span>
                            </span>
                            <span class="custom-tooltip">
                                <a data-hs-overlay="#move-files-folder" class="ti-btn ti-btn-sm ti-btn-soft-secondary bg-secondary/10"><i class="bi bi-arrows-move"></i></a>
                                <span class="tooltip-text">Move</span>
                            </span>
                            <span class="custom-tooltip">
                                <a href="/download-file/${row.google_drive_id}" class="ti-btn ti-btn-sm ti-btn-soft-success bg-success/10"><i class="bi bi-download"></i></a>
                                <span class="tooltip-text">Download</span>
                            </span>
                            <span class="custom-tooltip">
                                <a href="/file-manager/preview/${row.google_drive_id}" target="_blank" class="ti-btn ti-btn-sm ti-btn-soft-info bg-info/10"><i class="bi bi-eye"></i></a>
                                <span class="tooltip-text">Preview</span>
                            </span>
                            <span class="custom-tooltip">
                                <a onclick="remove_data(${row.id}, 'file-manager')" class="ti-btn ti-btn-sm ti-btn-soft-danger bg-danger/10"><i class="bi bi-trash"></i></a>
                                <span class="tooltip-text">Delete</span>
                            </span>
                        </div>`;
                    }
                }
            ],
            order: [[1, 'asc']],
            rowCallback: function(row, data) {
                $(row).attr('data-href', `/file-manager/preview/${data.google_drive_id}`);
            },
            initComplete: function() {
                $("#customSearchWrapper").html($("#fileListTable_filter"));
                $("#customLengthWrapper").html($("#fileListTable_length"));
            }
        });
    });
</script>

<style>
    .custom-tooltip {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }
    .custom-tooltip .tooltip-text {
        visibility: hidden;
        background-color: #222;
        color: #fff;
        font-size: 12px;
        border-radius: 4px;
        padding: 4px 8px;
        position: absolute;
        z-index: 100;
        bottom: 120%;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.2s;
        white-space: nowrap;
    }
    .custom-tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }
</style>
@endpush
