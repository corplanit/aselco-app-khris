<div id="PopupInfo" class="hs-overlay ti-modal pointer-events-none hidden mt-6">
    <div class="hs-overlay ti-modal-box mt-0 lg:!max-w-4xl lg:w-full m-3  items-center justify-center">
        <div class="max-h-full w-full overflow-hidden ti-modal-content">
            <div class="ti-modal-header">
                <h6 class="modal-title text-[1rem] font-semiboldmodal-title" id="popup-name">
                    Consumer Information
                </h6>
                <button type="button" class="hs-dropdown-toggle ti-modal-close-btn" data-hs-overlay="#PopupInfo">
                    <span class="sr-only">Close</span>
                    <svg class="w-3.5 h-3.5" width="8" height="8" viewBox="0 0 8 8" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M0.258206 1.00652C0.351976 0.912791 0.479126 0.860131 0.611706 0.860131C0.744296 0.860131 0.871447 0.912791 0.965207 1.00652L3.61171 3.65302L6.25822 1.00652C6.30432 0.958771 6.35952 0.920671 6.42052 0.894471C6.48152 0.868271 6.54712 0.854471 6.61352 0.853901C6.67992 0.853321 6.74572 0.865971 6.80722 0.891111C6.86862 0.916251 6.92442 0.953381 6.97142 1.00032C7.01832 1.04727 7.05552 1.1031 7.08062 1.16454C7.10572 1.22599 7.11842 1.29183 7.11782 1.35822C7.11722 1.42461 7.10342 1.49022 7.07722 1.55122C7.05102 1.61222 7.01292 1.6674 6.96522 1.71352L4.31871 4.36002L6.96522 7.00648C7.05632 7.10078 7.10672 7.22708 7.10552 7.35818C7.10442 7.48928 7.05182 7.61468 6.95912 7.70738C6.86642 7.80018 6.74102 7.85268 6.60992 7.85388C6.47882 7.85498 6.35252 7.80458 6.25822 7.71348L3.61171 5.06702L0.965207 7.71348C0.870907 7.80458 0.744606 7.85498 0.613506 7.85388C0.482406 7.85268 0.357007 7.80018 0.264297 7.70738C0.171597 7.61468 0.119017 7.48928 0.117877 7.35818C0.116737 7.22708 0.167126 7.10078 0.258206 7.00648L2.90471 4.36002L0.258206 1.71352C0.164476 1.61976 0.111816 1.4926 0.111816 1.36002C0.111816 1.22744 0.164476 1.10028 0.258206 1.00652Z"
                            fill="currentColor" />
                    </svg>
                </button>
            </div>
            <div class="space-y-6 p-6 pt-0 bg-white shadow-md rounded-lg">
                @csrf
                <div class="box-body !p-0 !mt-0">
                    <div class="sm:border-b-2 border-gray-200 dark:border-white/10">
                        <nav class="-mb-0.5 sm:flex sm:space-x-6 rtl:space-x-reverse" role="tablist">
                            <a class="w-full sm:w-auto hs-tab-active:font-semibold hs-tab-active:border-primary hs-tab-active:text-primary py-4 px-1 inline-flex items-center gap-2 border-b-[3px] border-transparent text-sm whitespace-nowrap text-defaulttextcolor dark:text-[#8c9097] dark:text-white/50 hover:text-primary active"
                                href="javascript:void(0);" id="icon-item-1" data-hs-tab="#icon-1"
                                aria-controls="icon-1">
                                <span class="bi bi-info-circle"></span>
                                Information
                            </a>
                            <a class="w-full sm:w-auto hs-tab-active:font-semibold hs-tab-active:border-primary hs-tab-active:text-primary py-4 px-1 inline-flex items-center gap-2 border-b-[3px] border-transparent text-sm whitespace-nowrap text-defaulttextcolor dark:text-[#8c9097] dark:text-white/50 hover:text-primary"
                                href="javascript:void(0);" id="icon-item-2" data-hs-tab="#icon-2"
                                aria-controls="icon-2">
                                <span class="bi bi-link-45deg"></span>
                                Linked Accounts
                            </a>
                            <a class="w-full sm:w-auto hs-tab-active:font-semibold hs-tab-active:border-primary hs-tab-active:text-primary py-4 px-1 inline-flex items-center gap-2 border-b-[3px] border-transparent text-sm whitespace-nowrap text-defaulttextcolor dark:text-[#8c9097] dark:text-white/50 hover:text-primary"
                                href="javascript:void(0);" id="icon-item-3" data-hs-tab="#icon-3"
                                aria-controls="icon-3">
                                <span class="bi bi-unlock"></span>
                                Change Password
                            </a>
                        </nav>
                    </div>

                    <div class="mt-3">
                        <div id="icon-1" class="" role="tabpanel" aria-labelledby="icon-item-1">
                            <div
                                class="text-gray-500 dark:text-[#8c9097] dark:text-white/50 p-5 border rounded-sm dark:border-white/10 border-gray-200">
                                <form action="" method="POST" enctype="multipart/form-data" id="client_form"
                                    autocomplete="off">
                                    @csrf
                                    <table class="ti-custom-table pt-0 mt-0">
                                        <tbody>
                                            <tr>
                                                <td class="w-10">
                                                    <span class="block font-semibold text-dark">
                                                        Account No.
                                                    </span>
                                                </td>
                                                <td class="w-10">:</td>
                                                <td class="text-start">
                                                    <div class="relative">
                                                        <input type="text" name="account_no"
                                                            class="ti-form-input text-dark rounded-sm ps-11 focus:z-10"
                                                            placeholder="Account Number" required id="d_account_no">
                                                        <div
                                                            class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-4">
                                                            <span class="bi bi-grid h-4 w-4"></span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="w-10">
                                                    <span class="block font-semibold  text-dark">
                                                        Owner Name
                                                    </span>
                                                </td>
                                                <td class="w-10">:</td>
                                                <td class="text-start">
                                                    <div class="relative">
                                                        <input type="text" name="consumer"
                                                            class="ti-form-input text-dark rounded-sm ps-11 focus:z-10 m-0"
                                                            placeholder="Consumer" required id="d_consumer">
                                                        <div
                                                            class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-4">
                                                            <span class="bi bi-info-circle h-4 w-4"></span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="w-15">
                                                    <span class="block font-semibold  text-dark">
                                                        Email Address
                                                    </span>
                                                </td>
                                                <td class="w-10">:</td>
                                                <td class="text-start">
                                                    <div class="relative">
                                                        <input type="text" name="email"
                                                            class="ti-form-input text-dark rounded-sm ps-11 focus:z-10 m-0"
                                                            placeholder="Email Address" required id="d_email">
                                                        <div
                                                            class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-4">
                                                            <span class="bi bi-envelope h-4 w-4"></span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="w-15">
                                                    <span class="block font-semibold  text-dark">
                                                        Contact Number
                                                    </span>
                                                </td>
                                                <td class="w-10">:</td>
                                                <td class="text-start">
                                                    <div class="relative">
                                                        <input type="text" name="contact"
                                                            class="ti-form-input text-dark rounded-sm ps-11 focus:z-10 m-0 "
                                                            placeholder="Contact Number" required id="d_mobile">
                                                        <div
                                                            class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-4">
                                                            <span class="bi bi-telephone h-4 w-4"></span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </form>

                                <!-- Search Link UI -->
                                <div id="link-ui" class="hidden mt-4">
                                    <div class="mb-3">
                                        <label for="search_email" class="block font-semibold text-dark">Search
                                            Email</label>
                                        <input type="email" id="search_email"
                                            class="ti-form-input w-full mt-1 rounded-sm"
                                            placeholder="Enter registered email">
                                    </div>
                                    <div class="flex gap-2">
                                        <button id="search_user" class="ti-btn ti-btn-dark">Search</button>
                                        <button id="cancel_link" class="ti-btn ti-btn-light">Cancel</button>
                                    </div>

                                    <div id="search_result" class="mt-4 hidden border rounded p-3 bg-gray-50">
                                        <p class="text-sm text-gray-700">Result for: <span id="result_email"
                                                class="font-semibold"></span></p>
                                        <div id="user_match" class="hidden">
                                            <p><strong>Name:</strong> <span id="found_name"></span></p>
                                            <p><strong>Contact:</strong> <span id="found_contact"></span></p>
                                            <input type="hidden" id="found_user_id">
                                            <button id="confirm_link" class="ti-btn ti-btn-primary mt-2">Confirm
                                                Link</button>
                                        </div>
                                        <div id="no_match" class="text-red-500 text-sm hidden">No matching
                                            account found.</div>
                                    </div>
                                </div>

                            </div>
                            <br>
                            <div id="check-status" class="text-sm text-red-600 mt-2 text-center"></div>
                            <div class="flex flex-wrap items-center justify-center gap-2 mt-4">
                                <button type="button" id="create-account" class="ti-btn ti-btn-soft-primary hidden">
                                    <span class="bi bi-pencil-square mx-2"></span>
                                    Create Account
                                </button>

                                <button type="button" id="link-account"
                                    class="ti-btn ti-btn-soft-light  text-sm hidden">
                                    <span class="bi bi-link-45deg mx-2"></span>
                                    Link to Existing Account
                                </button>
                            </div>

                            <button type="button" class="ti-btn ti-btn-soft-success bg-success/10 float-end"
                                id="save-changes">
                                <span class="bi bi-check-circle mx-2"></span>
                                Save Changes
                            </button>
                        </div>
                        <!-- Linked Accounts Tab -->
                        <div id="icon-2" role="tabpanel" aria-labelledby="icon-item-2" class="hidden">
                            <div class="text-gray-500 p-5 border rounded-sm border-gray-200">
                                <table
                                    class="table-auto w-full text-sm text-left border border-gray-200 rounded overflow-hidden"
                                    id="linkedAccountsTable">
                                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs tracking-wider">
                                        <tr>
                                            <th class="px-4 py-3">Account No.</th>
                                            <th class="px-4 py-3">Customer</th>
                                            <th class="px-4 py-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white text-gray-800">
                                        <!-- dynamic rows -->
                                    </tbody>
                                </table>

                            </div>
                        </div>

                        <div id="icon-3" class="hidden" role="tabpanel" aria-labelledby="icon-item-3">
                            <div
                                class="text-gray-500 dark:text-[#8c9097] dark:text-white/50 p-5 border rounded-sm dark:border-white/10 border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-700 mb-4">Password Management</h2>

                                <div id="pwd_content" class="hidden">

                                    <div id="pwd_msg" class="text-sm text-red-600 mt-2 text-center"></div>

                                    <input type="hidden" id="pw_user_id">

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700">New Password</label>
                                        <input type="password" id="pw_password" class="ti-form-input mt-1"
                                            placeholder="New password">
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700">Confirm
                                            Password</label>
                                        <input type="password" id="pw_password_confirmation"
                                            class="ti-form-input mt-1" placeholder="Confirm password">
                                    </div>

                                    <div class="flex gap-2 justify-end">
                                        <button id="btn-change-password" class="ti-btn ti-btn-success relative">
                                            <span class="default-text"><i class="bi bi-check-circle mr-1"></i>
                                                Change
                                                Password</span>
                                            <span class="loading-text hidden"><i
                                                    class="bi bi-arrow-repeat animate-spin mr-1"></i>
                                                Saving...</span>
                                        </button>

                                        <button id="btn-reset-password" class="ti-btn ti-btn-danger relative">
                                            <span class="default-text"><i class="bi bi-arrow-clockwise mr-1"></i>
                                                Reset Password</span>
                                            <span class="loading-text hidden"><i
                                                    class="bi bi-arrow-repeat animate-spin mr-1"></i>
                                                Sending...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
