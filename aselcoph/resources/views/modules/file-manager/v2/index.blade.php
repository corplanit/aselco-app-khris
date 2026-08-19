<x-app-layout>
    <x-slot name="active">Tools</x-slot>x

    <div class="p-4 flex items-center gap-3">
        <button type="button" class="ti-btn ti-btn-primary" data-hs-overlay="#create-file" id="openUploaderBtn">
            Open Uploader
        </button>
        <div id="uploadIndicator" class="hidden flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg">
            <span class="animate-spin"><i class="bi bi-arrow-repeat text-blue-600"></i></span>
            <span class="text-sm text-blue-700 font-medium"><span id="activeUploadsCount">0</span> file(s) uploading...</span>
            <button type="button" class="text-blue-600 hover:text-blue-800 underline text-sm" onclick="document.querySelector('[data-hs-overlay=\"#create-file\"]').click()">
                Show Progress
            </button>
        </div>
    </div>

    <div id="create-file"
        class="hs-overlay hidden size-full rounded-md fixed top-0 start-0 overflow-x-hidden overflow-y-auto pointer-events-none ti-modal">
        <div
            class="hs-overlay-open:mt-7 ti-modal-box mt-6 pt-6 ease-out lg:!max-w-4xl lg:w-full m-3 items-center justify-center">
            <div class="ti-modal-content flex-grow">
                <div class="ti-modal-header">
                    <h6 class="modal-title text-[1rem] font-semibold">Upload Files (Maximum Size: 5GB per file)</h6>
                    <button type="button" class="hs-dropdown-toggle ti-modal-close-btn" data-hs-overlay="#create-file">
                        <span class="sr-only">Close</span>&#x2715;
                    </button>
                </div>

                <form id="uploadForm" enctype="multipart/form-data" action="#" method="post"
                    onsubmit="return false;">
                    @csrf
                    <input type="hidden" name="name" value="-">
                    {{-- FIX: remove the Google Drive fallback; the chunk XHR decides what to send --}}
                    {{-- <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? Auth::user()->google_drive_id }}"> --}}

                    <div class="ti-modal-body">
                        <div id="dropZone"
                            class="flex flex-col items-center justify-center border-4 border-dashed border-gray-300 rounded-2xl p-12 min-h-[500px] text-center cursor-pointer transition hover:border-blue-400 hover:bg-blue-50 mb-6">
                            <div class="flex flex-col items-center space-y-4">
                                <i class="bi bi-cloud-arrow-up text-5xl text-blue-400"></i>
                                <p class="text-lg text-gray-600 font-medium">Drag and drop files here</p>
                                <p class="text-sm text-gray-400">or click to browse from your device</p>
                            </div>
                        </div>

                        <input type="file" id="fileInput" name="files[]" multiple class="hidden">

                        <table class="w-full mt-3 border-collapse border border-gray-300 hidden" id="fileTable">
                            <thead>
                                <tr>
                                    <th class="border border-gray-300 px-2 py-1 text-left">File Name</th>
                                    <th class="border border-gray-300 px-2 py-1 text-left">Format</th>
                                    <th class="border border-gray-300 px-2 py-1 text-right">Size</th>
                                    <th class="border border-gray-300 px-2 py-1 text-center">Status</th>
                                    <th class="border border-gray-300 px-2 py-1">Progress</th>
                                    <th class="border border-gray-300 px-2 py-1 text-right" width="180">Transferred
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="fileList"></tbody>
                        </table>

                        <div id="invalidFilesContainer" class="hidden mt-3 text-dark">
                            <table class="w-full mt-3 border-collapse border border-gray-300" id="invalidFilesTable">
                                <thead>
                                    <tr>
                                        <th class="border text-start border-gray-300 px-2 py-1">Invalid Files (exceeding
                                            5GB)</th>
                                        <th class="border text-end border-gray-300 px-2 py-1">Size</th>
                                    </tr>
                                </thead>
                                <tbody id="invalidFilesList"></tbody>
                            </table>
                        </div>

                        <div id="errorBanner"
                            class="hidden mt-3 px-3 py-2 rounded bg-red-50 border border-red-200 text-red-700 text-sm">
                        </div>
                    </div>

                    <div class="ti-modal-footer flex justify-between">
                        <button type="button" id="hideBtn" class="ti-btn ti-btn-secondary ti-btn-md hidden" onclick="hideUploadModal()">
                            <i class="bi bi-dash-circle"></i>
                            <span>Hide (Continue in Background)</span>
                        </button>
                        <button type="submit" id="uploadBtn" class="ti-btn ti-btn-success ti-btn-md" disabled>
                            <span id="uploadSpinner" class="hidden animate-spin"><i
                                    class="bi bi-opencollective"></i></span>
                            <span id="uploadBtnText">Upload Files</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        // FIX: expose ONLY a numeric local folder id or null (root). No Google Drive id, no query string.
        $parent_id_expose = isset($currentFolder) && is_numeric($currentFolder->id) ? (int) $currentFolder->id : null;
    @endphp

    <script>
        let modalHidden = false; 
        
        (function() {
            // ===== Tunables =====
            const CHUNK_SIZE = 2 * 1024 * 1024;
            const CHUNK_CONCURRENCY = 6;
            const FILE_CONCURRENCY = 3;
            const MAX_FILE_SIZE = 5 * 1024 * 1024 * 1024;
            const ENDPOINT = @json(route('api.drive.file.upload-chunk'));

            // ===== Elements =====
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            const fileTable = document.getElementById('fileTable');
            const fileList = document.getElementById('fileList');
            const invalidWrap = document.getElementById('invalidFilesContainer');
            const invalidList = document.getElementById('invalidFilesList');
            const uploadForm = document.getElementById('uploadForm');
            const uploadBtn = document.getElementById('uploadBtn');
            const uploadSpinner = document.getElementById('uploadSpinner');
            const uploadBtnText = document.getElementById('uploadBtnText');
            const errorBanner = document.getElementById('errorBanner');

            const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
            const PARENT_ID = {!! $parent_id_expose !== null ? (int) $parent_id_expose : 'null' !!};

            // ===== Formatters =====
            // Keep the general formatter for the static “Size” column
            const fmtSize = (n) => n < 1024 ? `${n} B` :
                n < 1024 ** 2 ? `${(n/1024).toFixed(2)} KB` :
                n < 1024 ** 3 ? `${(n/1024**2).toFixed(2)} MB` :
                `${(n/1024**3).toFixed(2)} GB`;

            // NEW: KB-only formatter for the live “Transferred” column (minimum 1 KB when > 0 bytes)
            const fmtKB = (n) => {
                if (n <= 0) return '0 KB';
                const kb = Math.max(1, Math.floor(n / 1024));
                return kb.toLocaleString() + ' KB';
            };

            function makeRow(index, file) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
      <td class="border border-gray-300 px-2 py-1">${file.name}</td>
      <td class="border border-gray-300 px-2 py-1 text-left">.${(file.name.split('.').pop()||'').toLowerCase()}</td>
      <td class="border border-gray-300 px-2 py-1 text-right">${fmtSize(file.size)}</td>
      <td class="border border-gray-300 px-2 py-1 text-center"><div id="status${index}">Pending</div></td>
      <td class="border border-gray-300 px-2 py-1">
        <div class="w-full bg-gray-200 rounded overflow-hidden">
          <div id="progressBar${index}" class="bg-primary h-4 text-white text-xs text-center leading-4" style="width:0%">0%</div>
        </div>
      </td>
      <td class="border border-gray-300 px-2 py-1 text-right">
        <span id="xfer${index}">0 KB / ${fmtKB(file.size)}</span>
      </td>`;
                fileList.appendChild(tr);
            }

            function renderSelection(files) {
                fileList.innerHTML = '';
                invalidList.innerHTML = '';
                errorBanner.classList.add('hidden');
                errorBanner.textContent = '';

                const valid = [],
                    invalid = [];
                Array.from(files).forEach(f => f.size > MAX_FILE_SIZE ? invalid.push(f) : valid.push(f));
                valid.forEach((f, i) => makeRow(i, f));
                invalid.forEach((f) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td class="border border-gray-300 px-2 py-1">${f.name}</td>
                      <td class="border border-gray-300 px-2 py-1 text-end text-danger">${fmtSize(f.size)}</td>`;
                    invalidList.appendChild(tr);
                });

                fileTable.classList.toggle('hidden', valid.length === 0);
                invalidWrap.classList.toggle('hidden', invalid.length === 0);
                uploadBtn.disabled = valid.length === 0;
                uploadBtn._validFiles = valid;
            }

            // DnD + picker
            dropZone.addEventListener('click', () => fileInput.click());
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('border-blue-500', 'bg-blue-50');
            });
            dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-blue-500', 'bg-blue-50'));
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                if (e.dataTransfer?.files?.length) {
                    fileInput.files = e.dataTransfer.files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
            fileInput.addEventListener('change', function() {
                renderSelection(this.files);
            });

            async function runLimited(list, limit, worker) {
                const executing = new Set();
                let i = 0;
                async function enqueue() {
                    if (i >= list.length) return;
                    const idx = i++;
                    const p = Promise.resolve(worker(list[idx], idx)).finally(() => executing.delete(p));
                    executing.add(p);
                    if (executing.size >= limit) await Promise.race(executing);
                    return enqueue();
                }
                await enqueue();
                await Promise.allSettled(executing);
            }

            function uploadFileInChunks(file, rowIndex) {
                return new Promise((resolve) => {
                    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                    const fileId = (crypto && crypto.randomUUID) ? crypto.randomUUID() :
                        (Date.now().toString(36) + Math.random().toString(36).slice(2));
                    
                    // Add to global tracker
                    if (window.uploadTracker) {
                        window.uploadTracker.addFile(fileId, file.name, file.size);
                    }

                    const bar = document.getElementById(`progressBar${rowIndex}`);
                    const statusEl = document.getElementById(`status${rowIndex}`);
                    const xferEl = document.getElementById(`xfer${rowIndex}`);

                    let uploadedBytes = 0,
                        completedChunks = 0;
                    const perChunk = new Map();
                    let fileFailed = false,
                        fileErrorMsg = '';
                    
                    let lastUpdate = 0;
                    let pendingUpdate = false;

                    function updateUI(force = false) {
                        const now = Date.now();
                        if (!force && now - lastUpdate < 100) {
                            if (!pendingUpdate) {
                                pendingUpdate = true;
                                setTimeout(() => {
                                    pendingUpdate = false;
                                    updateUI(true);
                                }, 100);
                            }
                            return;
                        }
                        
                        lastUpdate = now;
                        const percent = file.size > 0 ? Math.floor((uploadedBytes / file.size) * 100) : 0;
                        bar.style.width = percent + '%';
                        bar.textContent = percent + '%';
                        // >>> KB-only live readout
                        xferEl.textContent = `${fmtKB(uploadedBytes)} / ${fmtKB(file.size)}`;
                        
                        // Update global tracker
                        if (window.uploadTracker) {
                            window.uploadTracker.updateProgress(fileId, percent, uploadedBytes, file.size);
                        }
                    }

                    function fail(msg) {
                        fileFailed = true;
                        fileErrorMsg = msg || 'Upload failed';
                        bar.classList.remove('bg-primary');
                        bar.classList.add('bg-danger');
                        statusEl.textContent = fileErrorMsg;
                    }

                    function sendChunk(idx) {
                        if (fileFailed) return Promise.reject(new Error('cancelled'));
                        const start = idx * CHUNK_SIZE;
                        const end = Math.min(start + CHUNK_SIZE, file.size);
                        const blob = file.slice(start, end);

                        return new Promise((res, rej) => {
                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', ENDPOINT, true);
                            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF);
                            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                            xhr.upload.onprogress = (e) => {
                                if (!e.lengthComputable) return;
                                const prev = perChunk.get(idx) || 0;
                                perChunk.set(idx, e.loaded);
                                uploadedBytes += (e.loaded - prev);
                                updateUI();
                            };

                            xhr.onload = () => {
                                const ok = (xhr.status >= 200 && xhr.status < 300);
                                if (!ok) {
                                    let msg = `HTTP ${xhr.status}`;
                                    try {
                                        const j = JSON.parse(xhr.responseText || '{}');
                                        if (j.error) msg = j.error;
                                    } catch (_) {
                                        if (xhr.responseText) msg = xhr.responseText.slice(0, 200);
                                    }
                                    fail(msg);
                                    rej(new Error(msg));
                                    return;
                                }
                                completedChunks++;
                                const prev = perChunk.get(idx) || 0;
                                const full = (end - start);
                                uploadedBytes += (full - prev);
                                perChunk.set(idx, full);
                                updateUI();
                                res(true);
                            };

                            xhr.onerror = () => {
                                fail('Network error');
                                rej(new Error('Network error'));
                            };

                            const form = new FormData();
                            form.append('chunk', blob);
                            form.append('file_id', fileId);
                            form.append('index', idx);
                            form.append('total', totalChunks);
                            form.append('filename', file.name);
                            form.append('size', file.size);
                            if (PARENT_ID !== null) form.append('parent_id', PARENT_ID);
                            xhr.send(form);
                        });
                    }

                    const indices = Array.from({
                        length: totalChunks
                    }, (_, i) => i);
                    runLimited(indices, CHUNK_CONCURRENCY, sendChunk)
                        .then(() => {
                            if (fileFailed) {
                                resolve({
                                    ok: false,
                                    error: fileErrorMsg
                                });
                                return;
                            }
                            statusEl.innerHTML =
                                `<i class="bi bi-check2-circle text-[15px] text-success font-bold"></i>`;
                            bar.classList.remove('bg-primary');
                            bar.classList.add('bg-success');
                            updateUI(true); // Force final update to show 100%
                            
                            // Mark as complete in global tracker
                            if (window.uploadTracker) {
                                window.uploadTracker.setComplete(fileId, window.location.href);
                            }
                            
                            resolve({
                                ok: true
                            });
                        })
                        .catch(() => {
                            // Mark as error in global tracker
                            if (window.uploadTracker) {
                                window.uploadTracker.setError(fileId, fileErrorMsg || 'Upload failed');
                            }
                            resolve({
                                ok: false,
                                error: fileErrorMsg || 'Upload failed'
                            });
                        });
                });
            }

            let isUploading = false;
            let modalHidden = false;
            
            window.addEventListener('beforeunload', (e) => {
                // Only show warning if upload modal is open AND uploading
                if (isUploading && !modalHidden) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            uploadForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const files = uploadBtn._validFiles || [];
                if (!files.length) return;

                isUploading = true;
                uploadBtn.disabled = true;
                uploadBtnText.textContent = 'Uploading...';
                uploadSpinner.classList.remove('hidden');
                errorBanner.classList.add('hidden');
                errorBanner.textContent = '';
                
                // Show hide button during upload
                document.getElementById('hideBtn').classList.remove('hidden');
                
                // Update upload indicator
                updateUploadIndicator(files.length, true);

                const failures = [];
                await runLimited(files, FILE_CONCURRENCY, async (file, i) => {
                    const {
                        ok,
                        error
                    } = await uploadFileInChunks(file, i);
                    if (!ok) failures.push({
                        name: file.name,
                        error
                    });
                });

                isUploading = false;
                uploadSpinner.classList.add('hidden');
                document.getElementById('hideBtn').classList.add('hidden');
                
                // Hide upload indicator
                updateUploadIndicator(0, false);

                if (failures.length === 0) {
                    uploadBtnText.textContent = 'Upload Complete';
                    // Don't auto-reload - let user click refresh in tracker
                    setTimeout(() => {
                        const modal = document.getElementById('create-file');
                        const closeBtn = modal?.querySelector('[data-hs-overlay="#create-file"]');
                        if (closeBtn) closeBtn.click();
                    }, 1000);
                } else {
                    uploadBtnText.textContent = 'Some files failed';
                    uploadBtn.disabled = false;
                    errorBanner.textContent = 'Some uploads failed:\n' + failures.map(f =>
                        `• ${f.name}: ${f.error}`).join('\n');
                    errorBanner.classList.remove('hidden');
                }
            });
        })();
    </script>

    <script>
        // Hide modal and continue upload in background
        function hideUploadModal() {
            modalHidden = true;
            const modal = document.getElementById('create-file');
            const closeBtn = modal.querySelector('[data-hs-overlay="#create-file"]');
            if (closeBtn) closeBtn.click();
        }

        // Update upload indicator
        function updateUploadIndicator(count, show) {
            const indicator = document.getElementById('uploadIndicator');
            const countEl = document.getElementById('activeUploadsCount');
            
            if (countEl) countEl.textContent = count;
            
            if (show && count > 0) {
                indicator.classList.remove('hidden');
            } else {
                indicator.classList.add('hidden');
            }
        }
    </script>


    <style>
        .bg-primary {
            background-color: #2563eb;
        }

        .bg-success {
            background-color: #10b981;
        }

        .bg-danger {
            background-color: #ef4444;
        }
    </style>
</x-app-layout>
