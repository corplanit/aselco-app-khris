<x-app-layout>

    <x-slot name="title">User Management</x-slot>
    <x-slot name="url_1">{"link": "/users", "text": "Manage"}</x-slot>
    <x-slot name="url_2">{"link": "/users", "text": "Users"}</x-slot>
    <x-slot name="active">Accounts</x-slot>
    <x-slot name="buttons">
        <button class="ti-btn ti-btn-primary text-white bg-primary !border-0 btn-wave me-0"
            data-hs-overlay="#user-create">
            <i class="bi bi-plus me-1"></i>Register Users
        </button>
    </x-slot>

    <div class="box">
        <div class="box-body">
            <i class="bi bi-info-circle px-1"></i> You can manage the billing here.
            <hr class="mb-3 mt-3">
            <div class="custom-box">

                @include('pages.actions.table-mod')


                <div class="table-responsive-n">
                    <table id="userTable"
                        class="table table-sm min-w-full !border border-defaultborder dark:border-defaultborder/10">
                        <thead>
                            <tr class="border-b border-defaultborder dark:border-defaultborder/10">
                                <th class="px-6 py-2 border" style="width: 10px;"><span class="mx-2">#</span></th>
                                <th class="px-6 py-2 border"><span class="mx-2">Name</span></th>
                                <th class="px-4 py-2 border">Email</th>
                                <th class="px-4 py-2 border" style="width: 90px;">Validated</th>
                                <th class="px-4 py-2 border">Role</th>
                                <th class="px-4 py-2 border">Created At</th>
                                <th class="px-4 py-2 border">Status</th>
                                <th class="px-4 py-2 border" id="action_th">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <script>
                    $(document).ready(function() {
                        $('#userTable').DataTable({
                            processing: true,
                            serverSide: false,
                            ajax: {
                                url: "{{ route('api.users') }}",
                                type: 'POST',
                                headers: {
                                    'X-CSRF-Token': '{{ csrf_token() }}'
                                }
                            },
                            columns: [{
                                    data: 'id',
                                    render: function(data, type, row, meta) {
                                        return `<span class="mx-2">${meta.row + 1}.</span>`;
                                    }
                                },
                                {
                                    data: 'name'
                                },
                                {
                                    data: 'email'
                                },
                                {
                                    data: 'email_verified_at',
                                    render: function(data) {
                                        if (data) {
                                            return `<p class="mb-1 text-info py-1 px-2 bg-info/10 rounded-full text-xs">
                                                        <i class="ri-checkbox-circle-fill me-1 align-middle inline-block"></i> Verified
                                                    </p>`;
                                        } else {
                                            return `<p class="mb-1 text-danger py-1 px-2 bg-danger/10 rounded-full text-xs">
                                                        <i class="ri-close-circle-fill me-1 align-middle inline-block"></i> Not Verified
                                                    </p>`;
                                        }
                                    }
                                },
                                {
                                    data: 'role',
                                    render: function(data) {
                                        if (data === 'admin@aselco.ph') {
                                            return `Administrator`;
                                        } else {
                                            return data;
                                        }
                                    }
                                },
                                {
                                    data: 'created_at'
                                },
                                {
                                    data: 'status',
                                    render: function(data) {
                                        let badgeClass = data === 'Active' ? 'bg-success/10 text-success' :
                                            'bg-danger/10 text-danger';
                                        return `<span class="badge ${badgeClass}">${data}</span>`;
                                    }
                                },
                                {
                                    data: 'id',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        const userJson = JSON.stringify(row).replace(/"/g,
                                            '&quot;'); // escape quotes

                                        return `
                                            <a href="#" class="ti-btn ti-btn-sm ti-btn-soft-primary bg-primary/10 open-user-modal"
                                            data-user="${userJson}"
                                            data-hs-overlay="#user-info">
                                            <i class="bi bi-pencil"></i>
                                            Edit User
                                            </a>`;
                                    }
                                }
                            ]
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

    <script>
        $(document).on('click', '.open-user-modal', function(e) {
            e.preventDefault();

            try {
                const user = $(this).data('user');

                $('#user-info input[name="name"]').val(user.name);
                $('#user-info input[name="email"]').val(user.email);
                $('#user-info select[name="role"]').val(user.role.toLowerCase() || 'customer');

                const isVerified = !!user.email_verified_at;

                $('#user-info select[name="email_validated"]')
                    .val(isVerified ? '1' : '0');


                const form = $('#user-info form');
                form.attr('action', `/users/${user.id}`);

                form.find('input[name="_method"]').remove();
                form.append(`<input type="hidden" name="_method" value="PUT">`);

                // Ensure modal is opened (fallback)
                window.HSOverlay?.open(document.getElementById('user-info'));
            } catch (err) {
                console.error('Failed to open modal:', err);
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.HSOverlay?.autoInit(); // Ensures overlays respond
        });
    </script>

    <div id="user-create" class="hs-overlay hs-overlay-open:mt-6 ti-modal hidden">
        <div class="hs-overlay ti-modal-box mt-0 lg:!max-w-4xl lg:w-full m-3  items-center justify-center">
            <div class="max-h-full w-full overflow-hidden ti-modal-content">
                <div class="ti-modal-header">
                    <h6 class="modal-title text-[1rem] font-semiboldmodal-title" id="form-header">
                        User Management
                    </h6>
                    <button type="button" class="hs-dropdown-toggle ti-modal-close-btn" data-hs-overlay="#user-create">
                        <span class="sr-only">Close</span>
                        <svg class="w-3.5 h-3.5" width="8" height="8" viewBox="0 0 8 8" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0.258206 1.00652C0.351976 0.912791 0.479126 0.860131 0.611706 0.860131C0.744296 0.860131 0.871447 0.912791 0.965207 1.00652L3.61171 3.65302L6.25822 1.00652C6.30432 0.958771 6.35952 0.920671 6.42052 0.894471C6.48152 0.868271 6.54712 0.854471 6.61352 0.853901C6.67992 0.853321 6.74572 0.865971 6.80722 0.891111C6.86862 0.916251 6.92442 0.953381 6.97142 1.00032C7.01832 1.04727 7.05552 1.1031 7.08062 1.16454C7.10572 1.22599 7.11842 1.29183 7.11782 1.35822C7.11722 1.42461 7.10342 1.49022 7.07722 1.55122C7.05102 1.61222 7.01292 1.6674 6.96522 1.71352L4.31871 4.36002L6.96522 7.00648C7.05632 7.10078 7.10672 7.22708 7.10552 7.35818C7.10442 7.48928 7.05182 7.61468 6.95912 7.70738C6.86642 7.80018 6.74102 7.85268 6.60992 7.85388C6.47882 7.85498 6.35252 7.80458 6.25822 7.71348L3.61171 5.06702L0.965207 7.71348C0.870907 7.80458 0.744606 7.85498 0.613506 7.85388C0.482406 7.85268 0.357007 7.80018 0.264297 7.70738C0.171597 7.61468 0.119017 7.48928 0.117877 7.35818C0.116737 7.22708 0.167126 7.10078 0.258206 7.00648L2.90471 4.36002L0.258206 1.71352C0.164476 1.61976 0.111816 1.4926 0.111816 1.36002C0.111816 1.22744 0.164476 1.10028 0.258206 1.00652Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                </div>

                <form method="POST" class="space-y-6 p-6 pt-0 bg-white rounded-lg" action="{{ route('users.save') }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div>
                        <label>Name</label>
                        <input name="name" value="" class="w-full border p-2 rounded" required>
                    </div>

                    <div>
                        <label>Email</label>
                        <input name="email" type="email" value="" class="w-full border p-2 rounded" required>
                    </div>

                    <div>
                        <label>Role</label>
                        <select name="role_n" class="w-full border p-2 rounded" required>
                            @foreach (['support', 'customer'] as $role)
                                <option value="{{ $role }}">
                                        {{ ucwords($role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="border-gray-300">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded" type="submit">Update</button>
                </form>

            </div>
        </div>
    </div>

    <div id="user-info" class="hs-overlay hs-overlay-open:mt-6 ti-modal hidden">
        <div class="hs-overlay ti-modal-box mt-0 lg:!max-w-4xl lg:w-full m-3  items-center justify-center">
            <div class="max-h-full w-full overflow-hidden ti-modal-content">
                <div class="ti-modal-header">
                    <h6 class="modal-title text-[1rem] font-semiboldmodal-title" id="form-header">
                        User Management
                    </h6>
                    <button type="button" class="hs-dropdown-toggle ti-modal-close-btn" data-hs-overlay="#user-info">
                        <span class="sr-only">Close</span>
                        <svg class="w-3.5 h-3.5" width="8" height="8" viewBox="0 0 8 8" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0.258206 1.00652C0.351976 0.912791 0.479126 0.860131 0.611706 0.860131C0.744296 0.860131 0.871447 0.912791 0.965207 1.00652L3.61171 3.65302L6.25822 1.00652C6.30432 0.958771 6.35952 0.920671 6.42052 0.894471C6.48152 0.868271 6.54712 0.854471 6.61352 0.853901C6.67992 0.853321 6.74572 0.865971 6.80722 0.891111C6.86862 0.916251 6.92442 0.953381 6.97142 1.00032C7.01832 1.04727 7.05552 1.1031 7.08062 1.16454C7.10572 1.22599 7.11842 1.29183 7.11782 1.35822C7.11722 1.42461 7.10342 1.49022 7.07722 1.55122C7.05102 1.61222 7.01292 1.6674 6.96522 1.71352L4.31871 4.36002L6.96522 7.00648C7.05632 7.10078 7.10672 7.22708 7.10552 7.35818C7.10442 7.48928 7.05182 7.61468 6.95912 7.70738C6.86642 7.80018 6.74102 7.85268 6.60992 7.85388C6.47882 7.85498 6.35252 7.80458 6.25822 7.71348L3.61171 5.06702L0.965207 7.71348C0.870907 7.80458 0.744606 7.85498 0.613506 7.85388C0.482406 7.85268 0.357007 7.80018 0.264297 7.70738C0.171597 7.61468 0.119017 7.48928 0.117877 7.35818C0.116737 7.22708 0.167126 7.10078 0.258206 7.00648L2.90471 4.36002L0.258206 1.71352C0.164476 1.61976 0.111816 1.4926 0.111816 1.36002C0.111816 1.22744 0.164476 1.10028 0.258206 1.00652Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="#" enctype="multipart/form-data"
                    class="bg-white rounded-lg p-6 pt-0 space-y-6">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Name
                            </label>
                            <input name="name" value="" required
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-2">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email
                            </label>
                            <input type="email" name="email" value="" required
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-2">
                        </div>
                    </div>
                    <!-- Role + Email Validation -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Role
                            </label>
                            <select name="role" required
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-2">
                                @foreach (['support', 'customer'] as $role)
                                    <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>
                                        {{ ucwords($role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email Validated
                            </label>
                            <select name="email_validated" required
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-2">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                    </div>

                    <hr class="border-gray-200">

                    <!-- Actions -->
                    <div class="flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md font-medium transition">
                            Update
                        </button>
                    </div>
                </form>


            </div>
        </div>
    </div>

</x-app-layout>
