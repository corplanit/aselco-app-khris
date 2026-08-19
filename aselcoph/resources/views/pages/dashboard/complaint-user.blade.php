@include('pages.actions.table-mod')

<div class="box-body p-0">
    <div class="dt-scroller">
        <table id="complaintTable"
            class="table table-sm min-w-full !border border-defaultborder dark:border-defaultborder/10">
            <thead>
                <tr class="border-b border-defaultborder dark:border-defaultborder/10">
                    <th class="px-6 py-2 border" style="width: 10px;"><span class="mx-2">#</span></th>
                    <th class="px-6 py-2 border"><span class="mx-2">Account Number</span></th>
                    <th class="px-4 py-2 border">Full Name</th>
                    <th class="px-4 py-2 border">Contact</th>
                    <th class="px-4 py-2 border">Complaint</th>
                    <th class="px-4 py-2 border">Status</th>
                    <th class="px-4 py-2 border" style="width: 140px">Attachment</th>
                    <th class="px-4 py-2 border">Submitted At</th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="dt-swipe-hint sm:hidden text-xs text-gray-500 mt-2 px-2">
        Tip: swipe left/right to view all columns.
    </div>
</div>

    <script>
        $(document).ready(function() {
            var table = $('#complaintTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('api.complaints') }}",
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
                        data: 'status',
                        render: function(data) {
                            let color = 'gray';
                            if (data === 'Pending') color = 'yellow';
                            else if (data === 'In Progress') color = 'blue';
                            else if (data === 'Resolved') color = 'green';
                            else if (data === 'Rejected') color = 'red';

                            return `<span class="px-2 py-1 text-xs rounded-full bg-${color}-100 text-${color}-800">${data}</span>`;
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
    </script>

<style>
  .dt-scroller{
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 12px;
  }

  /* Force a minimum width so mobile can swipe horizontally */
  #complaintTable{
    min-width: 980px;
  }

  /* Make text readable on phones */
  @media (max-width: 640px){
    #complaintTable th, #complaintTable td{
      white-space: nowrap;
      font-size: 12px;
      padding: 8px 10px !important;
    }
  }
</style>

