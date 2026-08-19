<div class="grid grid-cols-12 sm:gap-x-6 mb-2 mt-3">


    @foreach ($files as $file)
        @if ($file->is_folder)
            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 col-span-12">
                <div class="box !shadow-none border border-defaultborder dark:border-defaultborder/10">
                    <div class="box-body">
                        <div class="mb-3 folder-svg-container flex flex-wrap justify-between items-top">
                            <div class="avatar avatar-xl">
                                <img src="/assets/images/media/file-manager/1.png" alt="" class="img-fluid">
                            </div>
                            <div>
                                <div class="ti-dropdown hs-dropdown">
                                    <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-fill font-semibold text-textmuted dark:text-textmuted/50"></i>
                                    </a>
                                    <ul class="ti-dropdown-menu hs-dropdown-menu hidden">
                                        <li><a class="ti-dropdown-item" onclick="remove_data({{ $file->id }}, 'file-manager')" href="javascript:void(0);">Delete</a>
                                        </li>
                                        <li><a class="ti-dropdown-item" data-hs-overlay="#rename-files-folder" onclick="rename_ff({{ $file->id }}, 'Folder', '{{ $file->name }}')" href="javascript:void(0);">Rename</a>
                                        </li>
                                        <li><a class="ti-dropdown-item" href="javascript:void(0);">Move
                                                Folder</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <p class="text-[14px] font-medium mb-3 leading-none">
                            <a href="/file-manager?f={{ $file->id }}&{{ md5($file->id) }}">
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
                            $folder_count = App\Models\FileManager::where('parent_id', $file->id)
                                ->where('user_id', auth()->id())
                                ->where('is_folder', 1)
                                ->where('isDeleted', 0)
                                ->count();

                            $files_count = App\Models\FileManager::where('parent_id', $file->id)
                                ->where('user_id', auth()->id())
                                ->where('is_folder', 0)
                                ->where('isDeleted', 0)
                                ->count();

                            $files_sum = App\Models\FileManager::where('parent_id', $file->id)
                                ->where('user_id', auth()->id())
                                ->where('isDeleted', 0)
                                ->sum('size');

                        @endphp
                        <div class="flex items-center justify-between flex-wrap">
                            <div>
                                <span class="text-textmuted dark:text-textmuted/50 text-xs">
                                    <i class="bi bi-file-text"></i>
                                    <span class="px-1">{{ number_format($files_count, 0) }} Files</span>
                                    
                                    <i class="bi bi-folder2-open px-1"></i>
                                    <span class="px-1">{{ number_format($folder_count, 0) }}  Folders</span>
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
    @endforeach
</div>
