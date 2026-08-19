@include('pages.actions.table-mod')

<div class="box-body p-0">
    <div class="table-responsive-n">
        <table id="complaintTable"
            class="table table-sm min-w-full !border border-defaultborder dark:border-defaultborder/10">
            <thead>
                <tr class="border-b border-defaultborder dark:border-defaultborder/10">
                    <th class="px-6 py-2 border" style="width: 10px;"><span class="mx-2">#</span></th>
                    <th class="px-6 py-2 border"><span class="mx-2">Account Number</span></th>
                    <th class="px-4 py-2 border">Full Name</th>
                    <th class="px-4 py-2 border">Contact</th>
                    <th class="px-4 py-2 border">Complaint</th>
                    <th class="px-4 py-2 border" style="width: 140px">Status</th>
                    <th class="px-4 py-2 border" style="width: 140px">Attachment</th>
                    <th class="px-4 py-2 border">Submitted At</th>
                </tr>
            </thead>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            var table = $('#complaintTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('api.complaints.admin') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-Token': '{{ csrf_token() }}'
                    },
                    data: {
                        id: "{{ request()->query('f') }}"
                    },
                    beforeSend: function() {
                        $("#customLoader").show();
                    },
                    complete: function() {
                        $("#customLoader").hide();
                    }
                },
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search here...",
                },
                columns: [{
                        data: 'id',
                        render: function(data, type, row, meta) {
                            return `<span class="mx-2">${meta.row + 1}.</span>`;
                        }
                    },
                    {
                        data: 'account_number'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'contact'
                    },
                    {
                        data: 'complaint',
                        render: function(data) {
                            return `<div class="truncate w-[250px] overflow-hidden" title="${data}">${data}</div>`;
                        }
                    },
                    {
                        data: 'attachment',
                        orderable: false,
                        render: function(data) {
                            return data ? `
                                <a href="/storage/${data}" target="_blank" class="ti-btn ti-btn-outline-light !rounded-full btn-wave waves-effect waves-light">
                                    <span class="bi bi-card-image"></span> Attachment
                                </a>` : `
                                 <a href="#" class="ti-btn ti-btn-light !rounded-full btn-wave waves-effect waves-light">
                                     No Attachment
                                </a>
                                `;
                        }
                    },
                    {
                        data: 'status',
                        orderable: false,
                        render: function(data, type, row) {
                            return `
                            <select class="ti-form-select ti-form-xs status-dropdown p-1 px-3" 
                                    data-id="${row.id}" 
                                    style="min-width: 120px">
                                <option value="Pending" ${data === 'Pending' ? 'selected' : ''}>Pending</option>
                                <option value="In Progress" ${data === 'In Progress' ? 'selected' : ''}>In Progress</option>
                                <option value="Resolved" ${data === 'Resolved' ? 'selected' : ''}>Resolved</option>
                                <option value="Rejected" ${data === 'Rejected' ? 'selected' : ''}>Rejected</option>
                            </select>
                        `;
                        }
                    },
                    {
                        data: 'created_at'
                    }
                ],
                order: [
                    [2, "desc"]
                ],
                rowCallback: function(row, data) {
                    $(row).attr('data-href', `/relationship/list/details/${data.id}`);
                },
                initComplete: function() {
                    $("#customSearchWrapper").html($("#complaintTable_filter"));
                    $("#customLengthWrapper").html($("#complaintTable_length"));
                }
            });
        });

        // Delegate event since selects are dynamically loaded
        $('#complaintTable').on('change', '.status-dropdown', function() {
            const select = $(this);
            const id = select.data('id');
            const newStatus = select.val();

            Swal.fire({
                title: "Update Status?",
                text: `Are you sure you want to set this complaint to "${newStatus}"?`,
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#aaa",
                confirmButtonText: "Yes, update it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('complaints.update') }}",
                        type: "POST",
                        headers: {
                            "X-CSRF-Token": '{{ csrf_token() }}'
                        },
                        data: {
                            id: id,
                            status: newStatus
                        },
                        success: function(res) {
                            Swal.fire("Updated!", res.message, "success");
                        },
                        error: function(xhr) {
                            Swal.fire("Error", "Failed to update status", "error");
                        }
                    });
                } else {
                    // Reset to previous value if cancelled
                    table.ajax.reload(null, false);
                }
            });
        });
    </script>
</div>
