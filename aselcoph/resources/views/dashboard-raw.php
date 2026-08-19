  {{-- <div class="custom-box">

                        @include('pages.actions.table-mod')

                        <div class="table-responsive-n">
                            <table id="billingTable"
                                class="table table-sm min-w-full !border border-defaultborder dark:border-defaultborder/10">
                                <thead class="">
                                    <tr class="border-b border-defaultborder dark:border-defaultborder/10">
                                        <th class="px-6 py-2 border" style="width: 10px;"><span class="mx-2">#</span>
                                        </th>
                                        <th class="px-6 py-2 border"><span class="mx-2">Account Number</span></th>
                                        <th class="px-4 py-2 border">Stablishment / Owner Name</th>
                                        <th class="px-4 py-2 border">Billing Date</th>
                                        <th class="px-4 py-2 border">Status</th>
                                        <th class="px-4 py-2 border">Amount</th>
                                        <th class="px-4 py-2 border">Uploaded By</th>
                                        <th class="px-4 py-2 border">Uploaded At</th>
                                        <th scope="col" class="text-start" id="action_th">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <script>
                            $(document).ready(function() {
                                $('#billingTable').DataTable({
                                    processing: true,
                                    serverSide: false,
                                    ajax: {
                                        url: "{{ route('api.bill') }}",
  type: 'POST',
  headers: {
  'X-CSRF-Token': '{{ csrf_token() }}'
  }
  },
  columns: [{
  data: 'id',
  name: 'id',
  render: function(data, type, row, meta) {
  return `<span class="mx-2">${meta.row + 1}.</span>`;
  },
  },
  {
  data: 'account_number',
  name: 'account_number',
  render: function(data) {
  return `<span class="mx-2">${data}</span>`;
  },
  },
  {
  data: 'owner_name',
  name: 'owner_name'
  },
  {
  data: 'billing_date',
  name: 'billing_date'
  },
  {
  data: 'status',
  name: 'status',
  render: function(data) {
  let badgeClass = '';
  let label = data;

  switch (data) {
  case 'Paid':
  badgeClass = 'bg-success/10 text-success';
  break;
  case 'Rejected':
  badgeClass = 'bg-danger/10 text-danger';
  break;
  case 'Pending':
  default:
  badgeClass = 'bg-warning/10 text-warning';
  label = 'Pending'; // capitalize if needed
  break;
  }

  return `<span class="badge ${badgeClass}">${label}</span>`;
  },
  },
  {
  data: 'amount',
  name: 'amount',
  render: function(data) {
  return '₱ ' + data;
  }
  },
  // {
  // data: 'file_path',
  // render: function(data) {
  // return `<a href="/storage/${data}" target="_blank">View PDF</a>`;
  // }
  // },
  {
  data: 'uploaded_by',
  name: 'uploaded_by'
  },
  {
  data: 'created_at',
  name: 'created_at'
  },
  {
  data: 'id',
  orderable: false,
  searchable: false,
  render: function(data, type, row) {
  return `<div class="hstack gap-1 text-[.9375rem]">
      <center>

          <span class="custom-tooltip">
              <a href="/storage/${row.file_path}" download="Billing-${row.account_number}-${row.billing_date}" class="ti-btn ti-btn-sm ti-btn-soft-success bg-success/10">
                  <i class="bi bi-download"></i>
              </a>
              <span class="tooltip-text">Download</span>
          </span>

          <span class="custom-tooltip">
              <a href="/storage/${row.file_path}" target="_blank" class="ti-btn ti-btn-sm ti-btn-soft-info bg-info/10">
                  <i class="bi bi-eye"></i>
              </a>
              <span class="tooltip-text">Preview</span>
          </span>

      </center>
  </div>`;
  }
  }
  ],
  initComplete: function() {
  $("#customSearchWrapper").html($("#clientTable_filter"));
  $("#customLengthWrapper").html($("#clientTable_length"));
  }
  });
  });
  </script>


  <style>
      #action_th {
          width: 100px !important;
      }
  </style>
  <style>
      .custom-tooltip {
          position: relative;
          display: inline-block;
          cursor: pointer;
      }

      .custom-tooltip .tooltip-text {
          visibility: hidden;
          background-color: #222;
          /* Tooltip background */
          color: #fff;
          /* Tooltip text color */
          font-family: 'Arial', sans-serif;
          font-size: 12px;
          text-align: center;
          border-radius: 4px;
          padding: 4px 8px;
          position: absolute;
          z-index: 100;
          bottom: 120%;
          left: 50%;
          transform: translateX(-50%);
          opacity: 0;
          transition: opacity 0.2s ease-in-out;
          white-space: nowrap;
      }

      .custom-tooltip:hover .tooltip-text {
          visibility: visible;
          opacity: 1;
      }
  </style>

  </div> --}}

  {{-- <div class="xl:col-span-12 col-span-12">


            <div class="box">
                <div class="box-body">
                    <i class="bi bi-link-45deg px-1"></i> Link Accounts here.
                    <hr class="mb-3 mt-3">
                    <div class="custom-box">
                        <div class="table-responsive">
                            <table class="table whitespace-nowrap min-w-full">
                                <thead>
                                    <tr class="border-b border-defaultborder">
                                        <th scope="col" class="text-start">Account No.</th>
                                        <th scope="col" class="text-start">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $accounts = App\Models\AccountLink::where('user_id', Auth::user()->id)->get();
                                    @endphp
                                    @foreach ($accounts as $linked)
                                        <tr
                                            class="table-{{ $linked->validated_at ? 'info' : 'warning' }} border-b border-defaultborder">
  <th scope="row" class="text-start">#{{ $linked->account_number }}</th>
  <td><span
          class="badge bg-{{ $linked->validated_at ? 'info' : 'warning' }} text-white">{{ $linked->validated_at ? 'Linked' : 'Pending' }}</span>
  </td>
  </tr>
  @endforeach
  </tbody>
  </table>
  </div>
  </div>
  </div>
  </div>
  </div> --}}