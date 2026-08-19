<x-app-layout>

    <x-slot name="title">My Linked Account Requests</x-slot>
    <x-slot name="url_1">{"link": "/", "text": "Manage Request"}</x-slot>
    <x-slot name="active">Account Requests</x-slot>
    <x-slot name="buttons"></x-slot>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-8 col-span-12">
            <div class="box custom-box">
                <div class="box-header">
                    <div class="box-body p-5 main-content-card">
                        <div id="validation-form">
                            <center>
                                <style>
                                    .transparent-shadow {
                                        filter: drop-shadow(0px 0px 1px #01742d);
                                    }
                                </style>
                                <img src="/assets/img/social-media.png" style="max-height: 175px"
                                    class="transparent-shadow mt-6 mb-3">
                                <h1 class="text-4xl text-bold"><strong>Welcome to Aselco Inc.</strong></h1>
                                <p class="text-[15px]">
                                    Please complete the form to link your account with your ledger details. <br>
                                    Your information will be validated against our records to ensure a match. Thank you.
                                </p>


                                <div class="form-group mt-4 text-[16px] form-check-lg">
                                    <input class="form-check-input border-2 border-black" type="checkbox"
                                        id="agreeCheck">
                                    <label class="form-check-label mx-1 text-[15px]" for="agreeCheck">
                                        I have read and agree to the <a href="#" data-hs-overlay="#create-contact"
                                            class="text-primary underline">Data Privacy Policy</a>.
                                    </label>
                                </div>

                                <br>

                                <button id="proceedBtn" type="button"
                                    class="ti-btn ti-btn-light !rounded-full ti-btn-w-lg" disabled onclick="proceed()">
                                    <span class="bi bi-check text-xl"></span>
                                    I Agree & Proceed
                                </button>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {

                                        document.querySelectorAll('.wizard-nav, .wizard-header, [data-wizard-tab]').forEach(el => {
                                            el.style.pointerEvents = "none";
                                        });

                                        const checkbox = document.getElementById('agreeCheck');
                                        const button = document.getElementById('proceedBtn');

                                        // Enable ONLY when checked
                                        checkbox.addEventListener('change', () => {
                                            button.disabled = !checkbox.checked;

                                            if (!checkbox.checked) {
                                                button.style.cursor = "not-allowed";
                                                button.classList.add('ti-btn-light');
                                                button.classList.remove('ti-btn-primary');
                                            } else {
                                                button.style.cursor = "pointer";
                                                button.classList.add('ti-btn-primary');
                                                button.classList.remove('ti-btn-light');
                                            }
                                        });
                                    });

                                    function proceed() {
                                        const checkbox = document.getElementById('agreeCheck');
                                        if (!checkbox.checked) {
                                            alert("Please agree to the privacy policy before proceeding.");
                                            return;
                                        }

                                        document.getElementById('validation-form').style.display = 'none';
                                        document.getElementById('registration-form').style.display = 'block';
                                    }
                                </script>



                            </center>
                        </div>

                        <div style="display: none" id="registration-form">
                            <h1 class="text-4xl text-bold text-center"><strong>Welcome to Agusan del Sur Electric
                                    Cooperative,
                                    Inc</strong></h1>
                            <p class="text-[15px] text-center">
                                Please complete the form to link your account with your ledger details.
                                Your information will be validated against our records to ensure a match. Thank you.
                            </p>
                            <form class="wizard wizard-tab horizontal" action="{{ route('link.store') }}"
                                method="POST">
                                @csrf
                                <aside class="wizard-content container">
                                    <div class="wizard-step " data-title="Account Classification"
                                        data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z">
                                        <div class="grid grid-cols-12 sm:gap-x-6 justify-content-center">
                                            <div class="xl:col-span-12 col-span-12">
                                                <div class="register-page">
                                                    <div class="grid grid-cols-12 sm:gap-x-6 gap-y-4">
                                                        <div class="xl:col-span-12 col-span-12">
                                                            <label for="Customer" class="ti-form-label">
                                                                Account Number : <strong class="text-danger">*</strong>
                                                            </label>
                                                            <input type="number" name="account_number"
                                                                class="form-control form-control-lg" id="Customer"
                                                                placeholder="e,i 550006052"
                                                                oninput="fwd_account(this.value)">
                                                        </div>
                                                        <div class="xl:col-span-12 col-span-12">
                                                            <label for="Customer" class="ti-form-label">
                                                                Stablishment / Owner Name : <small><i>Last Name, First
                                                                        Name, Middle Initial</i></small> <strong
                                                                    class="text-danger">*</strong>
                                                            </label>
                                                            <input style="text-transform: upercase;" name="owner_name"
                                                                type="text" style="text-transform: uppercase"
                                                                class="form-control form-control-lg" id="last-name"
                                                                placeholder="e,i ABA-A, GEMMA C."
                                                                oninput="fwd_owner(this.value)">
                                                        </div>

                                                        <script>
                                                            function fwd_account(value) {
                                                                const acount = document.getElementById('prev_account')
                                                                acount.innerHTML = value;
                                                            }

                                                            function fwd_owner(value) {
                                                                const name = document.getElementById('prev_owner')
                                                                name.innerHTML = value;
                                                            }
                                                        </script>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wizard-step" data-title="Preview Details"
                                        data-id="H53WJiv9blN17MYTztq4g8U6eSVkaZDx">
                                        <div class="summary-view">
                                            <div class="sm:max-w-[70.33333333%] mx-auto bg-white">
                                                <div
                                                    class="border border-defaultborder dark:border-defaultborder/10 rounded-1 mb-3">
                                                    <div class="box-body !p-0">
                                                        <div class="table-responsive">
                                                            <table class="ti-custom-table">
                                                                <thead
                                                                    class="border-b border-defaultborder dark:border-defaultborder/10">
                                                                    <tr>
                                                                        <th scope="col">Details</th>
                                                                        <th scope="col"></th>
                                                                        <th scope="col"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td class="w-25">
                                                                            <span class="block font-semibold">Account
                                                                                Number</span>
                                                                        </td>
                                                                        <td class="w-10">:</td>
                                                                        <td
                                                                            class="text-start  text-textmuted dark:text-textmuted/50">
                                                                            <span id="prev_account"></span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="w-25">
                                                                            <span
                                                                                class="block font-semibold">Stablishment
                                                                                Or Owner Name</span>
                                                                        </td>
                                                                        <td class="w-10">:</td>
                                                                        <td
                                                                            class="text-start text-textmuted dark:text-textmuted/50">
                                                                            <span id="prev_owner"></span>
                                                                        </td>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wizard-step" data-title="Processing"
                                        data-id="dOM0iRAyJXsLTr9b3KZfQ2jNv4pgn6Gu" data-limit="3">
                                        <div class="grid grid-cols-12 sm:gap-x-6">
                                            <div class="xl:col-span-12 col-span-12">
                                                <div class="checkout-payment-success">
                                                    <div class="mb-4">
                                                        <img src="/assets/img/paper-plane.png"
                                                            style="height: 120px; width: 120px" alt=""
                                                            class="img-fluid !inline-flex">
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="mb-1 fs-14">
                                                            Do you confirm that all the information provided is correct
                                                            and ready for submission?
                                                        </p>
                                                        <p class="text-textmuted dark:text-textmuted/50">
                                                            Ready to go? Confirm to submit your details.
                                                        </p>
                                                    </div>
                                                    <button style="submit"
                                                        class="ti-btn ti-btn-primary !rounded-full btn-wave waves-effect waves-light ti-btn-w-lg">
                                                        <span class="bi bi-check text-xl"></span>
                                                        Yes, proceed with submission!
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </aside>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="xl:col-span-4 col-span-12">
            <div class="box custom-box">
                <div class="box-header">
                    <div class="box-body p-5 main-content-card">
                        <h2 class="text-xl font-semibold mb-6">My Linked Account Requests</h2>
                        @php
                            $accountLinks = App\Models\AccountLink::where('user_id', auth()->id())
                                ->orderByDesc('created_at')
                                ->get();
                        @endphp
                        @if ($accountLinks->isEmpty())
                            <div class="text-gray-600 text-center">No account link requests found.</div>
                        @else
                            <div class="overflow-x-auto bg-white rounded">
                                <table class="min-w-full table-auto text-sm border">
                                    <thead class="bg-gray-100 text-gray-700">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Account Details</th>
                                            <th class="px-4 py-2 text-left" width="50">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($accountLinks as $link)
                                            <tr class="border-t">
                                                <td class="px-4 py-2">
                                                    <span class="text-muted">
                                                        {{ $link->account_number }}
                                                    </span><br>
                                                    {{ $link->owner_name }}
                                                </td>
                                                <td class="px-4 py-2 text-center">
                                                    @if ($link->validated_at)
                                                        <span
                                                            class="badge bg-success font-medium rounded-full p-2 w-full justify-center">Validated</span>
                                                    @else
                                                        <span
                                                            class="badge bg-warning font-medium rounded-full p-2 w-full justify-center">Pending</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="create-contact" class="hs-overlay ti-modal pointer-events-none hidden mt-6">
        <div class="hs-overlay ti-modal-box mt-0 lg:!max-w-4xl lg:w-full m-3  items-center justify-center">
            <div class="max-h-full w-full overflow-hidden ti-modal-content">
                <div class="ti-modal-header">
                    <h6 class="modal-title text-[1rem] font-semiboldmodal-title" id="form-header">
                        Data Privacy Act of 2012 (R.A. 10173)
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

                <div class="modal-body">
                    <div style="max-height: 500px; overflow-y: auto;">
                        @include('pages.customer.partials.data-privacy')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Internal Form Wizard JS -->
    <script src="/assets/js/form-wizard.js"></script>
    <script src="/assets/js/form-wizard-init.js"></script>

</x-app-layout>
