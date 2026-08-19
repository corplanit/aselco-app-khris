<div class="grid grid-cols-12 sm:gap-x-6 mt-3">

    @foreach ($files as $file)
        @if ($file->is_folder)
            @php
                $companyLogos = [
                    'syracuse' => 'png',
                    'utica' => 'jpg',
                ];

                $matched = null;

                foreach ($companyLogos as $company => $ext) {
                    if (Str::contains(Str::lower($file->name), $company)) {
                        $matched = ['name' => $company, 'ext' => $ext];
                        break;
                    }
                }
            @endphp

            @if ($matched)
                <div class="xl:col-span-2 lg:col-span-4 md:col-span-4 col-span-12">
                    <div class="box">
                        <div class="box-body">
                            <a href="/file-manager/list/folder?f={{ $file->google_drive_id }}&{{ $file->id }}">
                                <img src="/company/{{ $matched['name'] }}.{{ $matched['ext'] }}" class="card-img mb-3"
                                    alt="{{ ucfirst($matched['name']) }} Logo">
                            </a>
                            <h6 class="box-title font-medium mb-3">
                                <a href="/file-manager/list/folder?f={{ $file->google_drive_id }}&{{ $file->id }}">
                                    {{ $file->name }}
                                </a>
                                <span class="badge bg-white float-end">
                                    <div>
                                        <div class="ti-dropdown hs-dropdown">
                                            <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i
                                                    class="ri-more-fill font-semibold text-textmuted dark:text-textmuted/50"></i>
                                            </a>
                                            <ul class="ti-dropdown-menu hs-dropdown-menu hidden">
                                                <li><a class="ti-dropdown-item"
                                                        onclick="remove_data({{ $file->id }}, 'file-manager')"
                                                        href="javascript:void(0);">Delete</a>
                                                </li>
                                                <li><a class="ti-dropdown-item" data-hs-overlay="#rename-files-folder"
                                                        onclick="rename_ff({{ $file->id }}, 'Folder', '{{ $file->name }}')"
                                                        href="javascript:void(0);">Rename</a>
                                                </li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);">Hide
                                                        Folder</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </span>
                            </h6>
                            <p class="card-text">
                                @php
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
                            <div class="flex items-center justify-between flex-wrap">
                                <div>
                                    <span class="text-textmuted dark:text-textmuted/50 text-xs">
                                        <i class="bi bi-file-text"></i>
                                        <span class="px-1">{{ number_format($files_count, 0) }} Files</span>

                                        <i class="bi bi-folder2-open px-1"></i>
                                        <span class="px-1">{{ number_format($folder_count, 0) }} Folders</span>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-defaulttextcolor font-medium">
                                        <?php
                                        $size = $files_sum;
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
                                    </span>
                                </div>
                            </div>
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 col-span-12">
                    <div class="box !shadow-none border border-defaultborder dark:border-defaultborder/10">
                        <div class="box-body">
                            <div class="mb-3 folder-svg-container flex flex-wrap justify-between items-top">
                                <div class="avatar avatar-xl">
                                    <a href="/file-manager/list/folder?f={{ $file->google_drive_id }}&{{ $file->id }}">
                                        @php
                                            $companyLogos_parent = [
                                                'cleantec' => 'webp',
                                            ];

                                            $matched = null;

                                            foreach ($companyLogos_parent as $company => $ext) {
                                                if (Str::contains(Str::lower($file->name), $company)) {
                                                    $matched = ['name' => $company, 'ext' => $ext];
                                                    break;
                                                }
                                            }
                                        @endphp

                                        @if ($matched)
                                            <img src="/company/{{ $matched['name'] }}.{{ $matched['ext'] }}"
                                                style="max-width: 200px; max-height: 200px; width: auto; height: auto; top: 0px; left: 5px; position: absolute"
                                                alt="{{ ucfirst($matched['name']) }} Logo" class="img-fluid">
                                        @else
                                            <img src="/assets/images/media/file-manager/1.png" alt="Default File Icon"
                                                class="img-fluid">
                                        @endif


                                    </a>
                                </div>
                                <div>
                                    <div class="ti-dropdown hs-dropdown">
                                        <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i
                                                class="ri-more-fill font-semibold text-textmuted dark:text-textmuted/50"></i>
                                        </a>
                                        <ul class="ti-dropdown-menu hs-dropdown-menu hidden">
                                            <li><a class="ti-dropdown-item"
                                                    onclick="remove_data({{ $file->id }}, 'file-manager')"
                                                    href="javascript:void(0);">Delete</a>
                                            </li>
                                            <li><a class="ti-dropdown-item" data-hs-overlay="#rename-files-folder"
                                                    onclick="rename_ff({{ $file->id }}, 'Folder', '{{ $file->name }}')"
                                                    href="javascript:void(0);">Rename</a>
                                            </li>
                                            <li><a class="ti-dropdown-item" href="javascript:void(0);">Hide
                                                    Folder</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[14px] font-medium mb-3 leading-none">
                                <a
                                    href="/file-manager/list/folder?f={{ $file->google_drive_id }}&{{ $file->id }}">
                                    @php
                                        $file_privacy = 'public';
                                    @endphp
                                    @if ($file_privacy == 'private')
                                        <i class="ri-lock-fill text-gray-500 text-md"></i>
                                    @elseif ($file_privacy == 'public')
                                        <i class="bi bi-collection text-green-500 text-md"></i>
                                    @endif
                                    <span class="px-1">{{ $file->name }}</span>
                                </a>
                            </p>
                            @php
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
                            <div class="flex items-center justify-between flex-wrap">
                                <div>
                                    <span class="text-textmuted dark:text-textmuted/50 text-xs">
                                        <i class="bi bi-file-text"></i>
                                        <span class="px-1">{{ number_format($files_count, 0) }} Files</span>

                                        <i class="bi bi-folder2-open px-1"></i>
                                        <span class="px-1">{{ number_format($folder_count, 0) }} Folders</span>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-defaulttextcolor font-medium">
                                        <?php
                                        $size = $files_sum;
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
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    @endforeach
</div>
