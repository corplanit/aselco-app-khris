@php
    use Carbon\Carbon;
    use App\Models\FileManager;
    $parent_id = Auth::user()->company ?? null;
@endphp
<x-app-layout>

    <x-slot name="title">File Manager</x-slot>
    <x-slot name="url_1">{"link": "/bid/list", "text": "Manage"}</x-slot>
    <x-slot name="url_2">{"link": "/bid/list", "text": "File"}</x-slot>
    <x-slot name="active">File Manager</x-slot>

    <div class="grid grid-cols-12 gap-x-5">
        @php

            function formatSize($bytes)
            {
                if ($bytes < 1024) {
                    return $bytes . ' B';
                }
                if ($bytes < 1024 * 1024) {
                    return number_format($bytes / 1024, 2) . ' KB';
                }
                if ($bytes < 1024 * 1024 * 1024) {
                    return number_format($bytes / (1024 * 1024), 2) . ' MB';
                }
                return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
            }

            $categories = [
                'Images' => ['icon' => 'ti ti-photo', 'type' => ['jpg', 'jpeg', 'png', 'gif']],
                'Videos' => ['icon' => 'ti ti-video', 'type' => ['mp4', 'mkv', 'avi']],
                'Docs' => ['icon' => 'ti ti-file-description', 'type' => ['pdf', 'doc', 'docx', 'txt']],
                'Apps' => ['icon' => 'bi bi-file-earmark-zip', 'type' => ['zip', 'exe', 'apk']],
            ];
        @endphp

        @foreach ($categories as $category => $data)
            @php

                $user = Auth::user();
                $clientId = null;

                if ($user->role === 'Virtual Assistant') {
                    $clientId = $user->company;
                } elseif ($user->role === 'Sub-Client') {
                    $clientId = App\Models\Clients::where('email', $user->email)->value('id');
                } else {
                    $clientId = App\Models\Lead::where('email', $user->email)->value('id');
                }

                $files = FileManager::where('user_id', $clientId)
                    ->whereIn('format', $data['type'])
                    ->where('isDeleted', 0)
                    ->get();
                $totalSize = $files->sum('size');
                $fileCount = $files->count();
                $formattedSize = formatSize($totalSize);
            @endphp

            <div class="xxl:col-span-3 col-span-12">
                <div class="box overflow-hidden main-content-card">
                    <div class="box-body">
                        <div class="flex items-center gap-4 flex-wrap">
                            <div class="main-card-icon primary">
                                <div class="avatar avatar-md bg-primary/10 border border-primary/10">
                                    <div class="avatar avatar-sm !text-primary">
                                        <i class="{{ $data['icon'] }} text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-auto">
                                <a href="javascript:void(0);" class="block font-medium">{{ $category }}</a>
                                <span class="text-xs text-textmuted dark:text-textmuted/50">
                                    Total Size: {{ $formattedSize }}
                                </span>
                            </div>
                            <div class="text-end">
                                <span class="font-medium">{{ $fileCount }} files</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>



    <div class="grid grid-cols-12 gap-x-6">
        <div class="xxl:col-span-12 col-span-12">

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    <strong class="font-bold">Whoops! Something went wrong.</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>W
                        @endforeach
                    </ul>
                </div>
                <hr>
            @endif

            <div class="box justify-between">
                <div class="box-header justify-between">
                    <div class="box-title p-5 pb-0 pt-2">
                        File Manager
                    </div>
                </div>
                <div class="box-body overflow-y-auto" id="discussion-container" style="max-height: 630px">
                    <hr>
                    <div class="file-manager-folders">
                        <div
                            class="flex p-4 flex-wrap gap-6 items-center justify-between border-b border-defaultborder dark:border-defaultborder/10">
                            <div class="flex gap-1 lg:nowrap flex-wrap justify-content-sm-end sm:w-[80%]">
                                <div class="input-group sm:!w-[50%]">
                                    <input type="text" class="form-control !border-s" placeholder="Search File"
                                        aria-describedby="button-addon01">
                                    <button class="ti-btn ti-btn-soft-primary !m-0" type="button"
                                        id="button-addon01"><i class="ri-search-line"></i></button>
                                </div>
                                <button
                                    class="ti-btn !m-0 ti-btn-light btn-w-md flex items-center justify-center btn-wave waves-light"
                                    data-hs-overlay="#create-folder">
                                    <i class="bi bi-folder-plus align-middle"></i>Create Folder
                                </button>

                                <button
                                    class="ti-btn !m-0 ti-btn-light btn-w-md flex items-center justify-center btn-wave waves-light"
                                    data-hs-overlay="#create-file">
                                    <i class="bi bi-upload align-middle"></i>Upload File
                                </button>
                            </div>
                        </div>
                        <script>
                            document.getElementById("fileInput").addEventListener("change", function() {
                                const file = this.files[0]; // Get selected file
                                const maxSize = 50 * 1024 * 1024; // 50MB in bytes

                                if (file && file.size > maxSize) {
                                    Swal.fire({
                                        title: "Maximum allowed size of 50MB",
                                        text: "The selected file exceeds the maximum allowed size of 50MB. Please contact the VA to assist you with uploading large files.",
                                        icon: "warning"
                                    });
                                    this.value = ""; // Clear the input
                                }
                            });
                        </script>

                        <div class="p-4 file-folders-container">

                            @php
                                $folderId = request()->query('f'); // Get folder ID from URL
                                $currentFolder = $folderId ? App\Models\FileManager::find($folderId) : null;
                                $parentFolder = $currentFolder ? $currentFolder->parent : null;

                                // Fetch files inside the current folder
                                $files = App\Models\FileManager::where('parent_id', $folderId)
                                    ->where('user_id', Auth::id())
                                    ->where('isDeleted', 0)
                                    ->orderBy('id', 'DESC')
                                    ->get();

                                // Generate breadcrumbs
                                $breadcrumbs = [];
                                $current = $currentFolder;

                                while ($current) {
                                    $breadcrumbs[] = $current;
                                    $current = $current->parent ?? null;
                                }

                                $breadcrumbs = array_reverse($breadcrumbs);
                            @endphp



                            <nav aria-label="breadcrumb mb-3">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="/file-manager">
                                            <i class="bi bi-folder px-2"></i>
                                            File Manager
                                        </a>
                                    </li>
                                    @foreach ($breadcrumbs as $crumb)
                                        <li class="breadcrumb-item">
                                            <a
                                                href="/file-manager?f={{ $crumb->id }}&{{ md5($crumb->id) }}">{{ $crumb->name }}</a>
                                        </li>
                                    @endforeach
                                </ol>
                            </nav>

                            @include('pages.storage.folder_list')

                            <div class="grid grid-cols-12 gap-x-6">
                                <div class="xl:col-span-12 col-span-12">
                                    <div
                                        class="table-responsive border border-defaultborder dark:border-defaultborder/10 border-b-1">
                                        <table class="ti-custom-table ti-custom-table-head ti-custom-table-hover">
                                            <thead>
                                                <tr
                                                    class="border-b !border-defaultborder dark:!border-defaultborder/10">
                                                    <th class="task-checkbox" width="10">
                                                        <input class="form-check-input" type="checkbox" value=""
                                                            aria-label="...">
                                                    </th>
                                                    <th scope="col">File Name</th>
                                                    <th scope="col">Format</th>
                                                    <th scope="col">Size</th>
                                                    <th scope="col">Date Uploaded</th>
                                                    <th scope="col">Privacy</th>
                                                    <th scope="col" width="50">...</th>
                                                </tr>
                                            </thead>
                                            <tbody class="files-list">
                                                @foreach ($files as $file)
                                                    @if (!$file->is_folder)
                                                        <tr
                                                            class="border-b !border-defaultborder dark:!border-defaultborder/10">
                                                            <td class="task-checkbox">
                                                                <input class="form-check-input" type="checkbox"
                                                                    value="" aria-label="...">
                                                            </td>
                                                            <th scope="row">
                                                                <div class="flex items-center">
                                                                    <div>
                                                                        <a href="{{ route('folder.preview.file', $file->link) }}"
                                                                            target="_blank"
                                                                            data-hs-overlay="#offcanvasRight">
                                                                            {{ $file->name }}
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </th>
                                                            <td style="text-transform: uppercase">{{ $file->format }}
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $size = $file->size;
                                                                if ($size >= 1073741824) {
                                                                    echo number_format($size / 1073741824, 2) . ' GB';
                                                                } elseif ($size >= 1048576) {
                                                                    echo number_format($size / 1048576, 2) . ' MB';
                                                                } elseif ($size >= 1024) {
                                                                    echo number_format($size / 1024, 2) . ' KB';
                                                                } elseif ($size > 1) {
                                                                    echo $size . ' bytes';
                                                                } elseif ($size == 1) {
                                                                    echo $size . ' byte';
                                                                } else {
                                                                    echo '0 bytes';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td class="text-gray-400">
                                                                {{ date_format($file->created_at, 'D, d, M. Y h:i A') }}
                                                            </td>
                                                            <td class="text-start">
                                                                <button class="privacy-toggle"
                                                                    data-id="{{ $file->id }}"
                                                                    data-status="{{ $file->is_public ? 'public' : 'private' }}">
                                                                    <i
                                                                        class="ri-{{ $file->is_public ? 'unlock' : 'lock' }}-line text-lg"></i>
                                                                    <span
                                                                        class="ml-1">{{ $file->is_public ? 'Public' : 'Private' }}</span>
                                                                </button>
                                                            </td>
                                                            <td>
                                                                <div class="ti-btn ti-btn-sm ti-btn-soft-light p-2">
                                                                    <center>
                                                                        <div class="ti-dropdown hs-dropdown">
                                                                            <a href="javascript:void(0);" class="text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                                                                                <i class="bi bi-filter-circle font-semibold text-dark dark:text-textmuted/50"></i>
                                                                                Action
                                                                            </a>
                                                                            <ul class="ti-dropdown-menu hs-dropdown-menu hidden">
                                                                                <li><a class="ti-dropdown-item" onclick="remove_data({{ $file->id }}, 'file-manager')" href="javascript:void(0);">Delete</a></li>
                                                                                <li><a class="ti-dropdown-item" data-hs-overlay="#rename-files-folder" onclick="rename_ff({{ $file->id }}, 'Folder', '{{ $file->name }}')"  href="javascript:void(0);">Rename</a></li>
                                                                                <li><a class="ti-dropdown-item" data-hs-overlay="#move-files-folder" href="javascript:void(0);">Move File</a></li>
                                                                            </ul>
                                                                        </div>
                                                                    </center>
                                                                </div>
                                                                

                                                                {{-- <div class="hstack gap-2 text-[15px]">
                                                                    <a href="{{ asset('storage/' . $file->path) }}"
                                                                        target="_blank"
                                                                        class="ti-btn ti-btn-icon ti-btn-sm ti-btn-soft-warning">
                                                                        <i class="ri-eye-line"></i>
                                                                    </a>
                                                                    <button
                                                                        onclick="copyToClipboard('{{ asset('dir/v1/' . md5($file->path . 'K3.nt') . '/' . $file->path) }}')"
                                                                        class="ti-btn ti-btn-sm ti-btn-soft-light">
                                                                        <i class="bi bi-copy"></i> Copy Link
                                                                    </button>
                                                                </div> --}}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <script>
                                            document.querySelectorAll('.privacy-toggle').forEach(button => {
                                                button.addEventListener('click', function() {
                                                    const fileId = this.dataset.id;
                                                    const currentStatus = this.dataset.status;
                                                    const newStatus = currentStatus === 'public' ? 'private' : 'public';
                                                    const confirmMessage =
                                                        `Are you sure you want to make this file ${newStatus.toUpperCase()}?`;

                                                    if (!window.confirm(confirmMessage)) {
                                                        return; // Stop if the user cancels
                                                    }

                                                    // Simulate AJAX request to update privacy status
                                                    fetch(`/update-file-privacy/${fileId}`, {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                        },
                                                        body: JSON.stringify({
                                                            is_public: newStatus === 'public'
                                                        })
                                                    }).then(response => {
                                                        if (response.ok) {
                                                            // Update button appearance
                                                            this.dataset.status = newStatus;
                                                            this.innerHTML =
                                                                `<i class="ri-${newStatus === 'public' ? 'unlock' : 'lock'}-line text-lg"></i>
                                                                          <span class="ml-1">${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}</span>`;
                                                        } else {
                                                            alert('Failed to update privacy status.');
                                                        }
                                                    });
                                                });
                                            });
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Emoji Script -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelector('input[name="message"]').addEventListener('input', function(event) {
                        this.value = this.value.replace(/:smile:/g, '😊').replace(/:heart:/g, '❤️');
                    });
                });

                function displayFileName(input) {
                    let fileName = input.files.length ? input.files[0].name : '';
                    document.getElementById('file-name-display').innerText = fileName ? `Selected File: ${fileName}` : '';
                }
            </script>
        </div>
    </div>

    <!--End::row-1 -->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>
    <div id="upload-document" class="hs-overlay hidden ti-modal">
        <div
            class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out h-[calc(100%-3.5rem)] min-h-[calc(100%-3.5rem)] flex items-center">
            <div class="max-h-full w-full overflow-hidden ti-modal-content">
                <div class="ti-modal-header">
                    <h6 class="modal-title text-[1rem] font-semiboldmodal-title">
                        Upload Documents (<b id="docs_name"></b>)
                    </h6>
                </div>
                <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="ti-modal-body overflow-y-auto">
                        <div class="grid grid-cols-12 gap-x-6 gap-y-3">
                            <div class="xl:col-span-12 col-span-12">
                                <input type="hidden" class="form-control" name="document_name" id="document_name"
                                    placeholder="Enter Document Name" required>
                            </div>

                            <div class="xl:col-span-12 col-span-12">
                                <input type="file"
                                    class="block w-full border border-gray-200 focus:shadow-sm dark:focus:shadow-white/10 rounded-sm text-sm focus:z-10 focus:outline-0 focus:border-gray-200 dark:focus:border-white/10 dark:border-white/10 dark:text-white/50 file:border-0 file:bg-light file:me-4 file:py-3 file:px-4 dark:file:bg-black/20 dark:file:text-white/50 mb-3"
                                    name="document_file" id="document_file" required
                                    accept=".pdf,.doc,.docx,.jpg,.png">
                                <span class="text-md mt-3 text-textmuted dark:text-textmuted/50">Allowed types:
                                    PDF,
                                    DOC,
                                    DOCX, JPG, PNG | Max Size: 50MB</span>
                                <p id="file-size-display" class="text-sm text-gray-600 mt-1"></p>
                            </div>
                        </div>
                    </div>
                    <div class="ti-modal-footer">
                        <button type="button" class="hs-dropdown-toggle ti-btn ti-btn-soft-secondary"
                            data-hs-overlay="#upload-document">
                            Cancel
                        </button>
                        <button type="submit" class="ti-btn ti-btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('pages.storage.create_folder')
    @include('pages.storage.create_files')

    @include('pages.storage.rename_ff')
    
    @include('pages.storage.move_ff')

    <script>
        function rename_ff(id, type, value){
            document.getElementById('form_rename').action = '/file-manager/rename/' + id
            document.getElementById('name').value = value
        }
    </script>

    <script>
        function upload_file(docs) {
            document.getElementById('document_name').value = docs
            document.getElementById('docs_name').innerHTML = docs
        }
        document.getElementById('document_file').addEventListener('change', function() {
            let file = this.files[0];
            if (file) {
                let fileSize = (file.size / 1024).toFixed(2); // Convert to KB
                document.getElementById('file-size-display').innerText = `File Size: ${fileSize} KB`;
            }
        });

        function copyToClipboard(text) {
            navigator.clipboard.writeText('-').then(() => {
                Swal.fire({
                    title: "Copied Successfully!",
                    text: "The file link has been copied to your clipboard.",
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false
                });

            }).catch(err => {
                console.error("Failed to copy: ", err);
            });
        }
    </script>

    <script src="./assets/js/switch.js"></script>
    <script src="./assets/libs/@popperjs/core/umd/popper.min.js"></script>
    <script src="./assets/libs/preline/preline.js"></script>
    <script src="./assets/js/defaultmenu.min.js"></script>
    <script src="./assets/libs/node-waves/waves.min.js"></script>
    <script src="./assets/js/sticky.js"></script>
    <script src="./assets/libs/simplebar/simplebar.min.js"></script>
    <script src="./assets/js/simplebar.js"></script>
    <script src="./assets/libs/@tarekraafat/autocomplete.js/autoComplete.min.js"></script>
    <script src="./assets/libs/@simonwep/pickr/pickr.es5.min.js"></script>
    <script src="./assets/libs/flatpickr/flatpickr.min.js"></script>
    <script src="./assets/js/custom-switcher.min.js"></script>
    <script src="./assets/libs/flatpickr/flatpickr.min.js"></script>
    <script src="./assets/js/crm-leads.js"></script>
    <script src="./assets/js/custom.js"></script>

</x-app-layout>
