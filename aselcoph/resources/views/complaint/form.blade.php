<div id="complaint" class="hs-overlay ti-modal pointer-events-none hidden mt-6">
    <div class="hs-overlay ti-modal-box mt-0 lg:!max-w-4xl lg:w-full m-3  items-center justify-center">
        <div class="max-h-full w-full overflow-hidden ti-modal-content">
            <div class="ti-modal-header">
                <h6 class="modal-title text-[1rem] font-semiboldmodal-title" id="form-header">
                    Submit a Customer Complaint
                </h6>
                <button type="button" class="hs-dropdown-toggle ti-modal-close-btn" data-hs-overlay="#complaint">
                    <span class="sr-only">Close</span>
                    <svg class="w-3.5 h-3.5" width="8" height="8" viewBox="0 0 8 8" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M0.258206 1.00652C0.351976 0.912791 0.479126 0.860131 0.611706 0.860131C0.744296 0.860131 0.871447 0.912791 0.965207 1.00652L3.61171 3.65302L6.25822 1.00652C6.30432 0.958771 6.35952 0.920671 6.42052 0.894471C6.48152 0.868271 6.54712 0.854471 6.61352 0.853901C6.67992 0.853321 6.74572 0.865971 6.80722 0.891111C6.86862 0.916251 6.92442 0.953381 6.97142 1.00032C7.01832 1.04727 7.05552 1.1031 7.08062 1.16454C7.10572 1.22599 7.11842 1.29183 7.11782 1.35822C7.11722 1.42461 7.10342 1.49022 7.07722 1.55122C7.05102 1.61222 7.01292 1.6674 6.96522 1.71352L4.31871 4.36002L6.96522 7.00648C7.05632 7.10078 7.10672 7.22708 7.10552 7.35818C7.10442 7.48928 7.05182 7.61468 6.95912 7.70738C6.86642 7.80018 6.74102 7.85268 6.60992 7.85388C6.47882 7.85498 6.35252 7.80458 6.25822 7.71348L3.61171 5.06702L0.965207 7.71348C0.870907 7.80458 0.744606 7.85498 0.613506 7.85388C0.482406 7.85268 0.357007 7.80018 0.264297 7.70738C0.171597 7.61468 0.119017 7.48928 0.117877 7.35818C0.116737 7.22708 0.167126 7.10078 0.258206 7.00648L2.90471 4.36002L0.258206 1.71352C0.164476 1.61976 0.111816 1.4926 0.111816 1.36002C0.111816 1.22744 0.164476 1.10028 0.258206 1.00652Z"
                            fill="currentColor" />
                    </svg>
                </button>
            </div>

            @if (session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('complaints.store') }}" method="POST" enctype="multipart/form-data"
                autocomplete="off" class="space-y-6 p-6 pt-0 bg-white shadow-md rounded-lg">
                @csrf
                <div class="mb-0 !mt-5">
                    <label for="account_number" class="block font-medium mx-2 mb-1">Account Number : <strong
                            class="text-danger">*</strong></label>
                    <select type="text"
                        class="form-control form-control-lg !border-s !ps-[1rem] !bg-light dark:!bg-light !rounded-full form-select ti-form-select"
                        placeholder="Account Number Here ..." name="account_number" id="account_number"
                        aria-label="Search Here ..." required>
                        @php
                            $linkedAccounts = App\Models\TAccountRaw::where('user_id', Auth::user()->id)
                                ->where('status', 'Linked')
                                ->get();
                        @endphp
                        <option value="" disabled selected> - SELECT ACCOUNT NO. -</option>
                        @foreach ($linkedAccounts as $account)
                            <option value="{{ $account->account_no }}">{{ $account->account_no }} -
                                {{ $account->customer }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="!mt-3">
                    <label for="name" class="block font-medium mx-2 mb-1">Full Name : <strong
                            class="text-danger">*</strong></label>
                    <input type="text" name="name" id="name"
                        class="form-control form-control-lg !border-s !ps-[1rem] !bg-light dark:!bg-light !rounded-full"
                        placeholder="Complete Name here.." value="{{ Auth::user()->name }}" required>
                </div>

                <div class="!mt-3">
                    <label for="contact" class="block font-medium mx-2 mb-1">Contact Number (optional) :</label>
                    <input type="text" name="contact" id="contact"
                        class="form-control form-control-lg !border-s !ps-[1rem] !bg-light dark:!bg-light !rounded-full"
                        placeholder="Contact Number (optional) here..">
                </div>

                <div class="!mt-3">
                    <label for="complaint" class="block font-medium mx-2 mb-1">Complaint : <strong
                            class="text-danger">*</strong></label>
                    <textarea name="complaint" id="complaint" rows="5"
                        class="form-textarea form-control form-control-lg !border-s !ps-[1rem] !bg-light dark:!bg-light !rounded-lg"
                        required placeholder="Type the customer complaint here.."></textarea>
                </div>

                <div class="!mt-3">
                    <label for="attachment" class="block font-medium mx-2 mb-1">Attach Image (Optional) :</label>
                    <input type="file" name="attachment" id="attachment" accept="image/*"
                        class="block w-full border border-gray-200 focus:shadow-sm dark:focus:shadow-white/10 rounded-sm text-sm focus:z-10 focus:outline-0 focus:border-gray-200 dark:focus:border-white/10 dark:border-white/10 dark:text-white/50 file:border-0 file:bg-light file:me-4 file:py-2 file:px-4 dark:file:bg-black/20 dark:file:text-white/50">

                    <div id="preview-container" class="mt-3 hidden">
                        <div class="relative inline-block">
                            <img id="image-preview" src="" alt="Preview" class="h-32 rounded border">
                            <button type="button" id="remove-image"
                                class="absolute top-0 right-0 bg-red-500 text-white text-xs px-2 py-1 rounded-full">✕</button>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-300">

                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md"
                        data-hs-overlay="#complaint">Cancel</button>
                    <button type="submit" id="submit_btn"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-md hover:bg-blue-700">
                        Submit
                    </button>
                </div>
            </form>

            <script>
                const fileInput = document.getElementById('attachment');
                const previewContainer = document.getElementById('preview-container');
                const imagePreview = document.getElementById('image-preview');
                const removeBtn = document.getElementById('remove-image');

                fileInput.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (file && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = () => {
                            imagePreview.src = reader.result;
                            previewContainer.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewContainer.classList.add('hidden');
                        imagePreview.src = '';
                    }
                });

                removeBtn.addEventListener('click', () => {
                    fileInput.value = '';
                    previewContainer.classList.add('hidden');
                    imagePreview.src = '';
                });
            </script>



        </div>
    </div>
</div>
