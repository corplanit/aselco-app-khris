@php
    $companyLogos = [
        'syracuse' => 'png',
        'utica' => 'jpg',
        'cleantec' => 'webp',
    ];

    $matched = null;
    foreach ($companyLogos as $company => $ext) {
        if (Str::contains(Str::lower($file->name), $company)) {
            $matched = ['name' => $company, 'ext' => $ext];
            break;
        }
    }

    $folder_count = App\Models\FileManager::where('parent_id', $file->link)
        ->where('user_id', $clientId)
        ->where('is_folder', 1)
        ->where('isDeleted', 0)
        ->count();

    $files_count = App\Models\FileManager::where('parent_id', $file->link)
        ->where('user_id', $clientId)
        ->where('is_folder', 0)
        ->where('isDeleted', 0)
        ->count();

    $files_sum = App\Models\FileManager::where('parent_id', $file->link)
        ->where('user_id', $clientId)
        ->where('isDeleted', 0)
        ->sum('size');
@endphp

{{-- resources/views/file-manager/partials/folder-card.blade.php --}}

{{-- resources/views/file-manager/partials/folder-card.blade.php --}}

{{-- resources/views/file-manager/partials/folder-card.blade.php --}}

@php
    $sizeFormatted = function ($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    };
@endphp

{{-- resources/views/file-manager/partials/folder-card.blade.php --}}

@php
    $sizeFormatted = function ($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    };
@endphp

<div class="col-span-3 p-2 hover:shadow-lg">
    <div
        class="relative h-full rounded-xl border border-gray-200 p-4 bg-white shadow-none transition group hover:shadow-lg">
        <a href="/file-manager/list/folder?f={{ $file->link }}&{{ $file->id }}"
            class="flex items-center gap-3">
            <div class="text-yellow-500">
                <div class="text-3xl">
                    @if ($file->is_folder)
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" width="40" height="40">
                            <path fill="#FFA000"
                                d="M853.333 256h-384L384 170.667H170.667c-46.934 0-85.334 38.4-85.334 85.333v170.667h853.334v-85.334c0-46.933-38.4-85.333-85.334-85.333z">
                            </path>
                            <path fill="#FFCA28"
                                d="M853.333 256H170.667c-46.934 0-85.334 38.4-85.334 85.333V768c0 46.933 38.4 85.333 85.334 85.333h682.666c46.934 0 85.334-38.4 85.334-85.333V341.333c0-46.933-38.4-85.333-85.334-85.333z">
                            </path>
                        </svg>
                    @endif
                    @if ($file->format == 'pdf')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 287.82 384" width="40" height="40">
                            <path fill="#e62335"
                                d="M652.52 792.45h192s50.06.1 49-59.26 0-210.58 0-210.58H785.16l-1-114.16H652.52s-45.94 1.52-46.52 53 0 284.71 0 284.71 4.19 44.97 46.52 46.29z"
                                transform="translate(-605.74 -408.45)"></path>
                            <path fill="#ee656c" d="M178.39 0L287.82 114.16 179.42 114.16 178.39 0z"></path>
                            <path fill="#fff"
                                d="M661.75 618.5h13.85V692h-13.85zm6.32 31.64h23.51A8.3 8.3 0 00696 649a7.86 7.86 0 003-3.21 10.35 10.35 0 001-4.79 10.79 10.79 0 00-1-4.83 7.66 7.66 0 00-3-3.17 8.41 8.41 0 00-4.42-1.14h-23.51V618.5h23.15a25.23 25.23 0 0112.11 2.8 19.93 19.93 0 018.11 7.91 25.68 25.68 0 010 23.63 19.84 19.84 0 01-8.11 7.87 25.46 25.46 0 01-12.11 2.78h-23.15zM725.19 618.5H739V692h-13.81zm7 60.15h17.64q6.83 0 10.57-3.29t3.74-9.3v-21.63q0-6-3.74-9.3t-10.57-3.29h-17.66V618.5h17.34a36.29 36.29 0 0115.69 3.08 21.75 21.75 0 019.88 9 28.19 28.19 0 013.39 14.25v20.83a28.16 28.16 0 01-3.26 13.85 22.22 22.22 0 01-9.78 9.2q-6.52 3.28-16 3.29h-17.26zM792.12 618.5H806V692h-13.88zm5 0h43.28v13.34h-43.23zm0 31h37.66v13.35h-37.61z"
                                transform="translate(-605.74 -408.45)"></path>
                        </svg>
                    @endif
                </div>
            </div>
            <div class="flex-1 truncate">
                <p class="font-medium text-gray-800 truncate">{{ $file->name }}</p>
                <p class="text-sm text-gray-500">{{ $sizeFormatted($files_sum) }}</p>
            </div>
        </a>

        <div class="absolute top-5 right-2 flex items-center" style="position: absolute; right: 15px;">
            <div class="ti-dropdown hs-dropdown">
                <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false"
                    class="inline-flex justify-center w-8 h-8 items-center rounded-full bg-gray-100">
                    <i class="ri-more-fill font-semibold text-textmuted dark:text-dark"></i>
                </a>
                <ul class="ti-dropdown-menu hs-dropdown-menu hidden">
                    <li>
                        <a class="ti-dropdown-item flex items-center gap-2 text-gray-700" href="javascript:void(0);">
                            <span class="bi bi-folder-symlink text-lg"></span>
                            <span class="text-sm">Open</span>
                        </a>
                    </li>
                    <li>
                        <a class="ti-dropdown-item flex items-center gap-2 text-gray-700" href="javascript:void(0);">
                            <span class="bi bi-cloud-download text-lg"></span>
                            <span class="text-sm">Download</span>
                        </a>
                    </li>
                    <li>
                        <a class="ti-dropdown-item flex items-center gap-2 text-gray-700" href="javascript:void(0);">
                            <span class="bi bi-pen text-lg"></span>
                            <span class="text-sm">Rename</span>
                        </a>
                    </li>
                    <li>
                        <a class="ti-dropdown-item flex items-center gap-2 text-gray-700" href="javascript:void(0);">
                            <span class="bi bi-send-plus text-lg"></span>
                            <span class="text-sm">Share</span>
                        </a>
                    </li>
                    <li>
                        <a class="ti-dropdown-item flex items-center gap-2 text-danger"
                            onclick="remove_data({{ $file->id }}, 'file-manager')" href="javascript:void(0);">
                            <span class="bi bi-trash text-lg"></span>
                            <span class="text-sm">Delete</span>
                        </a>
                    </li>
                    {{-- <li><a class="ti-dropdown-item" onclick="remove_data({{ $file->id }}, 'file-manager')" href="javascript:void(0);">Delete</a></li>
                    <li><a class="ti-dropdown-item" data-hs-overlay="#rename-files-folder" onclick="rename_ff({{ $file->id }}, 'Folder', '{{ $file->name }}')" href="javascript:void(0);">Rename</a></li>
                    <li><a class="ti-dropdown-item" href="javascript:void(0);">Hide Folder</a></li> --}}
                </ul>
            </div>
        </div>
    </div>
</div>
