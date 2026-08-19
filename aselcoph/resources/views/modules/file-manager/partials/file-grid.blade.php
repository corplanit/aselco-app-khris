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

    $folder_count = App\Models\FileManager::where('parent_id', $file->google_drive_id)
        ->where('user_id', $clientId)
        ->where('is_folder', 1)
        ->where('isDeleted', 0)
        ->count();

    $files_count = App\Models\FileManager::where('parent_id', $file->google_drive_id)
        ->where('user_id', $clientId)
        ->where('is_folder', 0)
        ->where('isDeleted', 0)
        ->count();

    $files_sum = App\Models\FileManager::where('parent_id', $file->google_drive_id)
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
        <a href="/file-manager/list/folder?f={{ $file->google_drive_id }}&{{ $file->id }}"
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
                    @elseif($file->format == 'docx' || $file->format == 'doc')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 287.82 384" width="40" height="40">
                            <path fill="#4a81f4"
                                d="M652.52 792.45h192s50.06.1 49-59.26 0-210.58 0-210.58H785.16l-1-114.16H652.52s-45.94 1.52-46.52 53 0 284.71 0 284.71 4.19 44.97 46.52 46.29z"
                                transform="translate(-605.74 -408.45)"></path>
                            <path fill="#759ff7" d="M178.39 0L287.82 114.16 179.42 114.16 178.39 0z"></path>
                            <path fill="#fff"
                                d="M825.17 582.35h-20.59c-7 0-12.68 4.47-12.68 10s5.68 10 12.68 10h20.59c7 0 12.68-4.48 12.68-10s-5.68-10-12.68-10zM754.87 582.35H674c-7 0-12.68 4.47-12.68 10s5.68 10 12.68 10h80.85c7 0 12.68-4.48 12.68-10s-5.66-10-12.66-10zM826.17 633.06H674c-7 0-12.68 4.48-12.68 10s5.68 10 12.68 10h152.17c7 0 12.68-4.48 12.68-10s-5.68-10-12.68-10zM826.17 683.78H674c-7 0-12.68 4.47-12.68 10s5.68 10 12.68 10h152.17c7 0 12.68-4.48 12.68-10s-5.68-10-12.68-10z"
                                transform="translate(-605.74 -408.45)"></path>
                        </svg>
                    @elseif($file->format == 'xlsx' || $file->format == 'xls' || $file->format == 'csv')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 287.82 384" width="40" height="40">
                            <path fill="#00a971"
                                d="M652.52 792.45h192s50.06.1 49-59.26 0-210.58 0-210.58H785.16l-1-114.16H652.52s-45.94 1.52-46.52 53 0 284.71 0 284.71 4.19 44.97 46.52 46.29z"
                                transform="translate(-605.74 -408.45)"></path>
                            <path fill="#3dbe93" d="M178.39 0L287.82 114.16 179.42 114.16 178.39 0z"></path>
                            <path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="10"
                                d="M143.91 228.27v92.93m-83.65-46.47h167.29M60.26 228.27h167.29M90.01 321.2h107.8c10.41 0 15.62 0 19.59-2a18.58 18.58 0 008.12-8.12c2-4 2-9.18 2-19.59v-89.25c0-10.4 0-15.61-2-19.58a18.55 18.55 0 00-8.12-8.13c-4-2-9.18-2-19.59-2H90.01c-10.41 0-15.61 0-19.59 2a18.6 18.6 0 00-8.12 8.13c-2 4-2 9.18-2 19.58v89.22c0 10.41 0 15.61 2 19.59a18.63 18.63 0 008.12 8.12c3.98 2.03 9.18 2.03 19.59 2.03z">
                            </path>
                        </svg>
                    @elseif(in_array($file->format, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'tiff', 'ico', 'heic', 'heif']))
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 287.82 384" width="40" height="40">
                            <path fill="#00cc69"
                                d="M652.52 792.45h192s50.06.1 49-59.26 0-210.58 0-210.58H785.16l-1-114.16H652.52s-45.94 1.52-46.52 53 0 284.71 0 284.71 4.19 44.97 46.52 46.29z"
                                transform="translate(-605.74 -408.45)"></path>
                            <path fill="#49db95" d="M178.39 0L287.82 114.16 179.42 114.16 178.39 0z"></path>
                            <path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="10"
                                d="M167.31 283.05l-16.14-15.67c-7.79-7.56-11.69-11.34-16.16-12.72a19.68 19.68 0 00-12 .13c-4.44 1.47-8.25 5.34-15.87 13.06l-38.67 37.4m98.89-22.2l3.3-3.2c7.8-7.57 11.7-11.36 16.17-12.74a19.67 19.67 0 0112.07.14c4.44 1.48 8.25 5.35 15.87 13.09l8.09 8m-55.5-5.27l38.81 38.08m0 0a135 135 0 01-14.27.41H99c-10.83 0-16.25 0-20.39-2.07a19.08 19.08 0 01-8.46-8.28 17.19 17.19 0 01-1.68-5.94m137.7 15.88a18.44 18.44 0 006.13-1.66 19.06 19.06 0 008.45-8.28c2.11-4 2.11-9.35 2.11-20v-2.9M68.42 305.25a124.38 124.38 0 01-.43-14v-90.97c0-10.61 0-15.91 2.11-20a19.18 19.18 0 018.46-8.28c4.14-2.06 9.56-2.06 20.39-2.06h92.9c10.84 0 16.26 0 20.4 2.06a19.16 19.16 0 018.45 8.28c2.11 4.06 2.11 9.36 2.11 20v88m-29-71a19.35 19.35 0 11-19.35-18.94 19.15 19.15 0 0119.32 18.99z">
                            </path>
                        </svg>
                    @elseif(in_array($file->format, ['mp4', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'webm', 'm4v', '3gp', 'mpeg', 'mpg']))
                        
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
