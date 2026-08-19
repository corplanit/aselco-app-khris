@include('pages.actions.table-mod')

<div class="table-responsive-n bg-white">
    <table id="clientTable" class="table table-sm min-w-full !border border-defaultborder dark:border-defaultborder/10">
        <thead>
            <tr class="border-b border-defaultborder dark:border-defaultborder/10">
                <th class="text-start" style="width: 5px">
                    <input type="checkbox" class="form-check-input mx-3" id="selectAll">
                </th>
                <th scope="col" class="text-start" style="width: 100px">Account No.</th>
                <th scope="col" class="text-start">Consumers</th>
                <th scope="col" class="text-start">Email Address</th>
                <th scope="col" class="text-start">Contact Number</th>
                <th scope="col" class="text-start" style="width: 100px">Status</th>
                <th scope="col" class="text-start" id="action_th">Actions</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    $(document).ready(function() {
        var table = $('#clientTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: "{{ route('api.consumer') }}",
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
                    render: function(data) {
                        return `<input type="checkbox" class="rowCheckbox form-check-input mx-3" value="${data}">`;
                    },
                    orderable: false
                },
                {
                    data: 'account_no',
                    name: 'account_no',
                    className: "text-start",

                },
                {
                    data: 'customer',
                    name: 'customer',
                    className: "text-start"
                },
                {
                    data: 'email',
                    render: function(data) {
                        return data ? data :
                            '<span class="text-muted">—</span>';
                    }
                },
                {
                    data: 'contact',
                    render: function(data) {
                        if (!data) {
                            return '<span class="text-muted">—</span>';
                        }

                        const formatted = data.replace(/^(\d{3})(\d{3})(\d{4})$/, '$1 $2 $3');

                        return `<span class="font-semibold text-gray-800">(+63) ${formatted}</span>`;
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        if (!data) return '<span class="text-muted">No status</span>';
                        let icon = data === 'Linked' ?
                            '<i class="ri-check-fill text-success text-[1rem]"></i>' :
                            '<i class="ri-close-fill text-danger text-[1rem]"></i>';
                        let color = data === 'Linked' ? 'text-success' : 'text-danger';
                        return `<span class="text-xs ${color}">${icon} ${data}</span>`;
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `<div class="hstack gap-1 text-[.9375rem]">
                        <center>

                            <span class="custom-tooltip">
                                <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-soft-warning bg-warning/20">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <span class="tooltip-text">Edit</span>
                            </span>

                            <span class="custom-tooltip">
                                <a onclick="remove_data(${row.id}, 'consumer')" href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-soft-danger bg-danger/10">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <span class="tooltip-text">Trash</span>
                            </span>

                        </center>
                    </div>`;
                    }
                }
            ],
            order: [
                [2, "asc"]
            ],
            rowCallback: function(row, data) {
                $(row).off('click').on('click', function(e) {
                    // Skip if the click came from the trash button
                    if ($(e.target).closest('a.ti-btn-soft-danger').length > 0) return;

                      $('#PopupInfo #popup-id').text((data.id || '').toString().trim());
                    $('#PopupInfo #d_account_no').val((data.account_no || '').toString()
                        .trim());
                    $('#PopupInfo #d_consumer').val((data.customer || '').toString()
                        .trim());
                    $('#PopupInfo #d_email').val((data.email || '').toString().trim());
                    $('#PopupInfo #d_mobile').val((data.contact || '').toString().trim());
                    $('#PopupInfo #pw_user_id').val((data.user_id || '').toString().trim());


                    // Reset sections
                    $('#PopupInfo #client_form').show();
                    $('#PopupInfo #pwd_content').removeClass('hidden');
                    $('#PopupInfo #check-status').addClass('hidden');
                    $('#PopupInfo #link-ui').addClass('hidden');  
                    $('#PopupInfo #pwd_msg').html('');

                    $('#PopupInfo #save-changes').show();
                    $('#PopupInfo #create-account').hide();
                    $('#PopupInfo #link-account').hide();
                    $('#PopupInfo #d_email').removeClass('!border !border-danger');
                    $('#PopupInfo #d_mobile').removeClass('!border !border-danger');

                    $('#PopupInfo #check-status').html('');

                    const tbody = $('#linkedAccountsTable tbody');
                    tbody.empty();

                    if (data.status === 'Inactive') {
                        $('#PopupInfo #d_email').addClass('!border !border-danger');
                        $('#PopupInfo #d_mobile').addClass('!border !border-danger');
                        $('#PopupInfo #save-changes').hide();
                        $('#PopupInfo #pwd_content').addClass(
                            'hidden'); // hide password panel
                        $('#PopupInfo #pwd_msg').html(
                            '<i class="text-danger">Sorry, this feature is not available for inactive accounts.</i>'
                        );

                        $('#PopupInfo #check-status').removeClass('hidden');

                        $('#PopupInfo #check-status').html(
                            '<i>This account number is not currently linked to any portal account.<br>' +
                            'You can create a new account or link to an existing one using the options below.</i>'
                        ).show();

                        $('#PopupInfo #create-account').show();
                        $('#PopupInfo #link-account').show();
                    }

                    // Load linked accounts...
                    if (data.user_id) {
                        $.get(`/accounts/user/${data.user_id}`, function(accounts) {
                            tbody.empty();
                            if (accounts.length > 0) {
                                accounts.forEach(acc => {
                                    const status = acc.status || 'Unknown';
                                    const isLinked = status === 'Linked';
                                    const row = `<tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-2 font-medium">${acc.account_no}</td>
                                        <td class="px-4 py-2">${acc.customer}</td>
                                        <td class="px-4 py-2">
                                            <span class="inline-block px-2 py-1 text-xs rounded-full ${
                                                isLinked ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'
                                            }">${status}</span>
                                        </td>
                                    </tr>`;
                                    tbody.append(row);
                                });
                            } else {
                                tbody.html(`
                                <tr>
                                    <td colspan="3" class="text-center text-gray-500 py-4">
                                        No linked accounts found.
                                    </td>
                                </tr>
                            `);
                            }
                        });
                    } else {
                        tbody.html(`
                            <tr>
                                <td colspan="3" class="text-center text-gray-500 py-4">
                                    No linked accounts found.
                                </td>
                            </tr>
                        `);
                    }

                    // Show the modal
                    const modal = document.querySelector('#PopupInfo');
                    if (modal) {
                        modal.setAttribute('data-hs-overlay', '#PopupInfo');
                        HSOverlay.open(modal);
                    }
                });
            },
            initComplete: function() {
                $("#customSearchWrapper").html($("#clientTable_filter"));
                $("#customLengthWrapper").html($("#clientTable_length"));
            }
        });
    });
</script>

<style>
    tr {
        cursor: pointer;
    }

    #action_th {
        width: 90px !important;
    }
</style>
