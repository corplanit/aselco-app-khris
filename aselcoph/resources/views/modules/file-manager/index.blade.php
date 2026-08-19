{{-- resources/views/file-manager/index.blade.php --}}

<x-app-layout>
    <x-slot name="return">{"link": "/file-manager/list", "text": "Back"}</x-slot>
    <x-slot name="title">Manage Resources</x-slot>
    <x-slot name="url_1">{"link": "/file-manager/list", "text": "Manage Resources"}</x-slot>
    <x-slot name="active">Resources</x-slot>

    {{-- Desktop Layout --}}
    <div class="desktop-response">
        <div class="grid grid-cols-12 gap-6">
            <div class="xl:col-span-12 col-span-12">
                <div class="box shadow-none border">
                    <div class="box-body">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mx-2">
                            <div>
                                <h6 class="font-bold text-2xl  dark:text-white">
                                    <strong>Manage Resources</strong>
                                </h6>
                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                    You can manage &amp; add upload files here.
                                </span>
                            </div>
                            <div class="inline-flex items-center gap-2">

                                <div class="segment gap-2 bg-gray-100 dark:bg-gray-700 rounded-md p-1">
                                    <button
                                        class="segment-item rounded-md bg-white h-10 py-2 segment-item-active text-xl px-3"><svg
                                            stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                                            stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z">
                                            </path>
                                            <path
                                                d="M14 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z">
                                            </path>
                                            <path
                                                d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z">
                                            </path>
                                            <path
                                                d="M14 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button class="segment-item h-10 py-2 text-xl px-3">
                                        <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                                            stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9 6l11 0"></path>
                                            <path d="M9 12l11 0"></path>
                                            <path d="M9 18l11 0"></path>
                                            <path d="M5 6l0 .01"></path>
                                            <path d="M5 12l0 .01"></path>
                                            <path d="M5 18l0 .01"></path>
                                        </svg>
                                    </button>
                                </div>
                                <button
                                    class="inline-flex items-center gap-2 rounded-md border bg-white border-slate-300  px-3 py-3 text-sm font-medium text-dark hover:bg-gray-500"
                                    data-hs-overlay="#create-folder">
                                    <i class="bi bi-folder-plus align-middle"></i>
                                    <span class="mx-1">Create Folder</span>
                                </button>
                                @if (request()->query('f'))
                                    <button type="button"
                                        class="inline-flex items-center gap-2 rounded-md border bg-white border-slate-300  px-3 py-3 text-sm font-medium text-dark hover:bg-gray-500"
                                        data-hs-overlay="#upload-file">
                                        <i class="bi bi-upload align-middle"></i>
                                        <span class="mx-1">Upload File</span>
                                    </button>
                                @endif
                            </div>

                        </div>
                        <hr class="mb-3 !mt-3">
                        @if ($errors->any())
                            <div
                                class="alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm flex items-center mx-3">
                                <div>
                                    <strong class="text-danger">Whoops! Something went wrong:</strong>
                                    <ul class="list-disc list-inside mt-2 mx-4">
                                        @foreach ($errors->all() as $error)
                                            <li class="text-dark"><i>{{ $error }}</i></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @php
                            $folderId = request()->query('f');
                            $currentFolder = $folderId ? App\Models\FileManager::find($folderId) : null;
                            $clientId = auth()->id();

                            $files = App\Models\FileManager::where('parent_id', $folderId)
                                ->where('user_id', $clientId)
                                ->where('isDeleted', 0)
                                ->orderBy('is_folder', 'desc')
                                ->orderBy('name')
                                ->paginate(24);
                            $breadcrumbs = [];
                            $current = $currentFolder;
                            while ($current) {
                                $breadcrumbs[] = $current;
                                $current = $current->parent ?? null;
                            }
                            $breadcrumbs = array_reverse($breadcrumbs);
                        @endphp

                        {{-- {{ $files->get() ?? 'N/A' }}
                        {{ $clientId }}
                        {{ $clientId }} --}}

                        {{-- Breadcrumbs --}}
                        {{-- <nav class="text-sm mb-4">
                            <a href="{{ route('filemanager.index') }}" class="text-blue-500">Root</a>
                            @foreach ($breadcrumbs as $crumb)
                                &nbsp;/&nbsp;
                                <a href="{{ route('filemanager.index', ['f' => $crumb->id]) }}" class="text-blue-500">
                                    {{ $crumb->name }}
                                </a>
                            @endforeach
                        </nav> --}}


                        {{-- Folder Grid --}}
                        @include('modules.file-manager.partials.folder-grid', [
                            'files' => $files,
                            'clientId' => $clientId,
                        ])

                        {{-- <h3 class="text-lg mx-2"><strong>Files</strong></h3>
                        <div class="grid grid-cols-12 gap-3 shadow-none">
                            @foreach ($files as $file)
                                @if ($file->is_folder == 0)
                                    @include('modules.file-manager.partials.file-grid', [
                                        'file' => $file,
                                        'clientId' => $clientId,
                                    ])
                                @endif
                            @endforeach
                        </div> --}}
                        @if (request()->query('f'))
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-12 p-2 shadow-none">
                                    @include('modules.file-manager.tables')
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

            </div>

        </div>
    </div>
    </div>


    @include('modules.file-manager.v2.upload')


    {{-- Mobile Layout Path: /modules/file-manager/mobile/mobile.blade.php --}}
    {{-- <div class="mobile-response">
        @php
            $folderId = request()->query('f');
            $currentFolder = $folderId ? App\Models\FileManager::find($folderId) : null;

            if (session('manage_portal_id')) {
                $clientId = session()->get('manage_portal_id');
            } else {
                $clientId = Auth::user()->id;

                if (Auth::user()->role == 'Developer') {
                    $clientId = 44; // Same hardcoded ID as desktop API
                }
            }

            $files = App\Models\FileManager::where('parent_id', $folderId)
                ->where('user_id', $clientId)
                ->where('isDeleted', 0)
                ->orderBy('is_folder', 'desc')
                ->orderBy('name')
                ->paginate(24);
        @endphp

        @include('modules.file-manager.mobile.mobile', [
            'files' => $files,
            'currentFolder' => $currentFolder,
            'clientId' => $clientId,
        ])
    </div> --}}

    @include('pages.filemanager.create_folder')


    @include('pages.storage.rename_ff')
    @include('pages.storage.move_ff')

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

    <script>
        function rename_ff(id, type, value) {
            document.getElementById('name').value = value
        }
    </script>
    {{-- @include('pages.filemanager.create_files') --}}
</x-app-layout>
