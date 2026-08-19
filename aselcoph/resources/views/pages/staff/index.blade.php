<x-app-layout>

    <x-slot name="title">Manage Billing Uploads</x-slot>
    <x-slot name="url_1">{"link": "/", "text": "Manage"}</x-slot>
    <x-slot name="url_2">{"link": "/", "text": "Billing"}</x-slot>
    <x-slot name="active">Uploads</x-slot>
    <x-slot name="buttons">
        <button class="ti-btn ti-btn-primary text-white bg-primary !border-0 btn-wave me-0"
            data-hs-overlay="#create-contact">
            <i class="bi bi-upload me-1"></i>Upload Billing
        </button>
    </x-slot>

    <div class="box">
        <div class="box-body">
            <i class="bi bi-info-circle px-1"></i> You can manage the billing here.
            <hr class="mb-3 mt-3">
            <div class="custom-box">

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

            </div>
        </div>
    </div>


    <div id="create-contact" class="hs-overlay ti-modal pointer-events-none hidden mt-6">
        <div class="hs-overlay ti-modal-box mt-0 lg:!max-w-4xl lg:w-full m-3  items-center justify-center">
            <div class="max-h-full w-full overflow-hidden ti-modal-content">
                <div class="ti-modal-header">
                    <h6 class="modal-title text-[1rem] font-semiboldmodal-title" id="form-header">
                        Client Information - Upload Billing
                    </h6>
                    <button type="button" class="hs-dropdown-toggle ti-modal-close-btn"
                        data-hs-overlay="#create-contact">
                        <span class="sr-only">Close</span>
                        <svg class="w-3.5 h-3.5" width="8" height="8" viewBox="0 0 8 8" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0.258206 1.00652C0.351976 0.912791 0.479126 0.860131 0.611706 0.860131C0.744296 0.860131 0.871447 0.912791 0.965207 1.00652L3.61171 3.65302L6.25822 1.00652C6.30432 0.958771 6.35952 0.920671 6.42052 0.894471C6.48152 0.868271 6.54712 0.854471 6.61352 0.853901C6.67992 0.853321 6.74572 0.865971 6.80722 0.891111C6.86862 0.916251 6.92442 0.953381 6.97142 1.00032C7.01832 1.04727 7.05552 1.1031 7.08062 1.16454C7.10572 1.22599 7.11842 1.29183 7.11782 1.35822C7.11722 1.42461 7.10342 1.49022 7.07722 1.55122C7.05102 1.61222 7.01292 1.6674 6.96522 1.71352L4.31871 4.36002L6.96522 7.00648C7.05632 7.10078 7.10672 7.22708 7.10552 7.35818C7.10442 7.48928 7.05182 7.61468 6.95912 7.70738C6.86642 7.80018 6.74102 7.85268 6.60992 7.85388C6.47882 7.85498 6.35252 7.80458 6.25822 7.71348L3.61171 5.06702L0.965207 7.71348C0.870907 7.80458 0.744606 7.85498 0.613506 7.85388C0.482406 7.85268 0.357007 7.80018 0.264297 7.70738C0.171597 7.61468 0.119017 7.48928 0.117877 7.35818C0.116737 7.22708 0.167126 7.10078 0.258206 7.00648L2.90471 4.36002L0.258206 1.71352C0.164476 1.61976 0.111816 1.4926 0.111816 1.36002C0.111816 1.22744 0.164476 1.10028 0.258206 1.00652Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                </div>

                <form method="POST" class="space-y-6 p-6 pt-0 bg-white rounded-lg"
                    action="{{ route('billing.upload.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label class="block mb-1 font-semibold">Select Account</label>
                        <select class="ti-form-select rounded-sm sm:!p-5" data-trigger name="account_link_id"
                            id="choices-single-default">
                            <option value="">Search Account Number / Owner Name here..</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->account_number }} - {{ $account->owner_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-12 gap-x-6">
                        <div class="xxl:col-span-6 xl:col-span-6 col-span-12">

                            <div class="">
                                <label class="block mb-1 font-semibold">Billing Date</label>
                                <input type="date" value="{{ date('Y-m-d') }}" name="billing_date"
                                    class="w-full border p-2 rounded" required>
                            </div>
                        </div>
                        <div class="xxl:col-span-6 xl:col-span-6 col-span-12">

                            <div class="">
                                <label class="block mb-1 font-semibold">Amount</label>
                                <input type="number" name="amount" step="0.01" class="w-full border p-2 rounded"
                                    placeholder="0.00" required>
                            </div>
                        </div>
                    </div>


                    <div class="mb-4">
                        <label class="block mb-1 font-semibold">PDF File</label>
                        <input type="file" name="pdf_file" accept="application/pdf"
                            class="w-full border p-2 rounded">
                    </div>


                    <hr class="border-gray-300">

                    <div class="flex justify-end space-x-3">
                        <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md"
                            data-hs-overlay="#create-contact">Cancel</button>
                        <button type="submit" id="submit_btn"
                            class="px-4 py-2 bg-green-500 text-white rounded-md shadow-md hover:bg-green-700">
                            Submit Billing
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</x-app-layout>
