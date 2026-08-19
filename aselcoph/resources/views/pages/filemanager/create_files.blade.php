<div id="create-file"
    class="hs-overlay hidden size-full rounded-md fixed top-0 start-0 overflow-x-hidden overflow-y-auto pointer-events-none ti-modal">
    <div
        class="hs-overlay-open:mt-7 ti-modal-box mt-6 pt-6 ease-out lg:!max-w-4xl lg:w-full m-3 items-center justify-center">
        <div class="ti-modal-content flex-grow">
            <div class="ti-modal-header">
                <h6 class="modal-title text-[1rem] font-semibold">Upload Files (Maximum Size: 2GB per file)</h6>
                <button type="button" class="hs-dropdown-toggle ti-modal-close-btn" data-hs-overlay="#create-file">
                    <span class="sr-only">Close</span>
                    &#x2715;
                </button>
            </div>

            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="name" value="-">
                <input type="hidden" name="parent_id"
                    value="{{ $currentFolder->id ?? Auth::user()->google_drive_id }}">

                <div class="ti-modal-body">
                    <div id="dropZone"
                        class="flex flex-col items-center justify-center border-4 border-dashed border-gray-300 rounded-2xl p-12 min-h-[500px] text-center cursor-pointer transition hover:border-blue-400 hover:bg-blue-50 mb-6">
                        <div class="flex flex-col items-center space-y-4">
                            <i class="bi bi-cloud-arrow-up text-5xl text-blue-400"></i>
                            <p class="text-lg text-gray-600 font-medium">Drag and drop files here</p>
                            <p class="text-sm text-gray-400">or click to browse from your device</p>
                        </div>
                    </div>

                    <input type="file" name="files[]" multiple id="fileInput"
                        class="hidden w-full border border-gray-200 focus:shadow-sm dark:focus:shadow-white/10 rounded-sm text-sm focus:z-10 focus:outline-0 focus:border-gray-200 dark:focus:border-white/10 dark:border-white/10 dark:text-white/50 file:border-0 file:bg-light file:me-4 file:py-3 file:px-4 dark:file:bg-black/20 dark:file:text-white/50 mb-3">


                    <table class="w-full mt-3 border-collapse border border-gray-300 hidden" id="fileTable">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 px-2 py-1">File Name</th>
                                <th class="border border-gray-300 px-2 py-1">Format</th>
                                <th class="border border-gray-300 px-2 py-1">Size</th>
                                <th class="border border-gray-300 px-2 py-1">Progress</th>
                                <th class="border border-gray-300 px-2 py-1">Status</th>
                            </tr>
                        </thead>
                        <tbody id="fileList"></tbody>
                    </table>

                    <div id="invalidFilesContainer" class="hidden mt-3 text-dark">
                        <table class="w-full mt-3 border-collapse border border-gray-300" id="invalidFilesTable">
                            <thead>
                                <tr>
                                    <th class="border text-start border-gray-300 px-2 py-1">File Name (Invalid Files
                                        (Exceeding 2GB))</th>
                                    <th class="border text-end border-gray-300 px-2 py-1">Size</th>
                                </tr>
                            </thead>
                            <tbody id="invalidFilesList"></tbody>
                        </table>
                    </div>
                </div>

                <div class="ti-modal-footer">
                    <button type="submit" id="uploadBtn" class="ti-btn ti-btn-success ti-btn-md" disabled>
                        <span id="uploadSpinner" class="hidden animate-spin"><i class="bi bi-opencollective"></i></span>
                        <span id="uploadBtnText">Upload Files</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@php
    $google_drive_id = request()->query('f'); // Get folder ID from URL
@endphp
<script>
    document.getElementById('fileInput').addEventListener('change', function() {
        let fileList = document.getElementById('fileList');
        let fileTable = document.getElementById('fileTable');
        let uploadBtn = document.getElementById('uploadBtn');
        let uploadBtnText = document.getElementById('uploadBtnText');
        let invalidFilesContainer = document.getElementById('invalidFilesContainer');
        let invalidFilesList = document.getElementById('invalidFilesList');
        fileList.innerHTML = '';
        invalidFilesList.innerHTML = '';
        let maxSize = 2 * 1024 * 1024 * 1024;
        let validFiles = [];
        let invalidFiles = [];

        if (this.files.length > 0) {
            fileTable.classList.remove('hidden');
        } else {
            fileTable.classList.add('hidden');
            uploadBtn.disabled = true;
            return;
        }

        function formatFileSize(size) {
            if (size < 1024) return size + " B";
            if (size < 1024 * 1024) return (size / 1024).toFixed(2) + " KB";
            if (size < 1024 * 1024 * 1024) return (size / (1024 * 1024)).toFixed(2) + " MB";
            return (size / (1024 * 1024 * 1024)).toFixed(2) + " GB";
        }

        Array.from(this.files).forEach((file, index) => {
            let fileFormat = file.name.split('.').pop();
            let fileSizeDisplay = formatFileSize(file.size);

            if (file.size > maxSize) {
                let row = document.createElement('tr');
                row.innerHTML =
                    `<td class="border border-gray-300 px-2 py-1">${file.name}</td>
                                 <td class="border border-gray-300 px-2 py-1 text-danger text-end">${fileSizeDisplay}</td>`;
                invalidFilesList.appendChild(row);
                invalidFiles.push(file);
            } else {
                let row = document.createElement('tr');
                row.innerHTML = `
                    <td class="border border-gray-300 px-2 py-1">${file.name}</td>
                    <td class="border border-gray-300 px-2 py-1 text-end">.${fileFormat}</td>
                    <td class="border border-gray-300 px-2 py-1 text-end">${fileSizeDisplay}</td>
                    <td class="border border-gray-300 px-2 py-1">
                        <div class="progress w-full">
                            <div id="progressBar${validFiles.length}" class="progress-bar progress-bar-striped progress-bar-animated bg-primary h-4 text-white text-xs" style="width: 0%"></div>
                        </div>
                    </td>
                    <td class="border border-gray-300 px-2 py-1 text-center">
                        <div id="progressBarText${validFiles.length}">Ready</div>
                    </td>
                `;
                fileList.appendChild(row);
                validFiles.push(file);
            }
        });

        invalidFilesContainer.classList.toggle('hidden', invalidFiles.length === 0);
        uploadBtn.disabled = validFiles.length === 0;
        uploadBtn.dataset.files = JSON.stringify(validFiles.map(f => f.name));
    });

    document.getElementById('uploadForm').addEventListener('submit', function(event) {
        event.preventDefault();
        let fileInput = document.getElementById('fileInput');
        let uploadBtn = document.getElementById('uploadBtn');
        let uploadBtnText = document.getElementById('uploadBtnText');
        let uploadSpinner = document.getElementById('uploadSpinner');
        let selectedFiles = JSON.parse(uploadBtn.dataset.files || "[]");

        if (selectedFiles.length === 0) return;

        uploadBtn.disabled = true;
        uploadBtnText.textContent = "Uploading...";
        uploadSpinner.classList.remove('hidden');
        uploadBtn.classList.add('bg-light');
        uploadBtn.classList.add('text-dark');

        let files = Array.from(fileInput.files).filter(f => selectedFiles.includes(f.name));
        let totalFiles = files.length;
        let uploadedFiles = 0;

        files.forEach((file, index) => {
            let formData = new FormData();
            formData.append('files[]', file);
            formData.append('name', '-');
            formData.append('parent_id', "{{ $google_drive_id ?? '' }}");

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "{{ route('drive.file.upload') }}", true);
            xhr.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");

            xhr.upload.onprogress = function(event) {
                if (event.lengthComputable) {
                    let percent = Math.round((event.loaded / event.total) * 100);
                    let progressBar = document.getElementById(`progressBar${index}`);
                    let progressBarText = document.getElementById(`progressBarText${index}`);

                    progressBar.style.width = percent + "%";
                    progressBarText.innerHTML = percent + "%";
                }
            };

            xhr.onload = function() {
                if (xhr.status === 200) {
                    let progressBar = document.getElementById(`progressBar${index}`);
                    let progressBarText = document.getElementById(`progressBarText${index}`);

                    progressBar.classList.remove('bg-primary');
                    progressBar.classList.add('bg-success');
                    progressBarText.innerHTML =
                        `<i class="bi bi-check2-circle text-[15px] text-success font-bold"></i>`;
                    uploadedFiles++;

                    if (uploadedFiles === totalFiles) {
                        uploadBtnText.textContent = "Upload Complete";
                        uploadSpinner.classList.add('hidden');
                        window.location.reload();
                    }

                } else {
                    console.error("Error uploading file:", xhr.responseText);
                }
            };

            xhr.onerror = function() {
                console.error("Network error, please try again.");
            };

            xhr.send(formData);
        });
    });
</script>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');

        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;

            // Manually trigger the change event to preview files
            const changeEvent = new Event('change');
            fileInput.dispatchEvent(changeEvent);
        }
    });
</script>

<style>
    #dropZone {
        transition: all 0.3s ease;
    }
</style>
