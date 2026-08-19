@php
    $zip = new ZipArchive();
    $zipContents = [];

    if ($zip->open($filePath) === true) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileName = trim($zip->getNameIndex($i));
            if (!empty($fileName)) {
                // Skip empty names
                $zipContents[] = $fileName;
            }
        }
        $zip->close();
    }

    function buildTree(array $files)
    {
        $tree = [];
        foreach ($files as $file) {
            $parts = explode('/', $file);
            $current = &$tree;
            $depth = count($parts);

            foreach ($parts as $index => $part) {
                if (!isset($current[$part])) {
                    $current[$part] = ['__count' => 0]; // Initialize count
                }

                if ($index === $depth - 1) {
                    $current[$part]['__isFile'] = true; // Mark as a file
                    $current[$part]['__count'] = 1; // Set count to 1 for files
                } else {
                    $current[$part]['__count']++; // Increase count only if it's part of a path
            }

            $current = &$current[$part];
        }
    }
    return $tree;
}

function renderTree($tree, $level = 0)
{
    echo '<ul class="file-tree">';
    foreach ($tree as $name => $subtree) {
        if ($name === '__count' || $name === '__isFile') {
            continue;
        }

        $id = uniqid('folder_');
        $padding = $level * 20;
        $isFile = isset($subtree['__isFile']);
        $itemCount = $subtree['__count'] ?? 0;

        if (!$isFile) {
            // Folder
            if ($itemCount > 0) {
                echo '<li class="folder-item" onclick="toggleFolder(event, \'' .
                    $id .
                    '\')" style="padding-left:' .
                    $padding .
                    'px;">';
                echo '<span class="px-2 mx-1">+</span>';
                echo '<span class="icon">📂</span>';
                echo '<span class="folder-name">' . $name . ' (' . $itemCount - 1 . ')</span>'; // Display correct count
                echo '</li>';
                echo '<ul id="' . $id . '" class="sub-folder hidden">';
                renderTree($subtree, $level + 1);
                echo '</ul>';
            }
        } else {
            // File
            if (!empty($name)) {
                echo '<li class="file-item" style="padding-left:' .
                    ($padding + 20) .
                    'px;" onclick="notifyPreviewUnavailable(event)">';
                echo '<span class="icon">📄</span>';
                echo '<span class="file-name">' . $name . '</span>';
                echo '</li>';
            }
        }
    }
    echo '</ul>';
    }

    $fileTree = buildTree($zipContents);
@endphp

<div class="grid grid-cols-12 gap-x-6">
    <div class="xxl:col-span-12 col-span-12">
        <div class="box">
            <div class="box-header">
                @php
                    function formatSize($size)
                    {
                        if ($size < 1024) {
                            return $size . ' B';
                        } elseif ($size < 1048576) {
                            return round($size / 1024, 2) . ' KB';
                        } elseif ($size < 1073741824) {
                            return round($size / 1048576, 2) . ' MB';
                        } else {
                            return round($size / 1073741824, 2) . ' GB';
                        }
                    }
                @endphp
                <ul class="mt-2 text-sm text-gray-600 px-2">
                    <li class="mb-1"><strong class="text-gray-800">📄 Name:</strong> {{ $info->name }}</li>
                    <li class="mb-1"><strong class="text-gray-800">📦 Size:</strong> {{ formatSize($info->size) }}</li>
                </ul>
            </div>
            <div class="box-body overflow-y-auto border-1">
                <div class="file-tree-container border-2 p-5">
                    @php renderTree($fileTree); @endphp
                </div>
            </div>
            <div class="box-footer p-4 px-6">
                {{ date_format($info->created_at, 'D, M, d, Y h:i A') }}
            </div>
        </div>
    </div>
</div>

<!-- Notification Message -->
<div id="notification-container" style="position: fixed; top: 20px; right: 30px; z-index: 1000;"></div>


<script>
    function toggleFolder(event, id) {
        event.stopPropagation();
        let folder = document.getElementById(id);
        if (folder) {
            folder.classList.toggle("hidden");
        }
    }

    function notifyPreviewUnavailable(event) {
        event.stopPropagation();

        // Create a new notification element
        let notificationContainer = document.getElementById("notification-container");
        let notification = document.createElement("div");

        notification.className =
            "notification alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm flex items-center";
        notification.innerHTML = `
        <div class="text-dark" style="padding: 8px; padding-left: 3px;">
            <span class="bi bi-exclamation-octagon px-1 text-danger"></span>
           Preview is unavailable for this file because it is compressed in a ZIP format.
        </div>
    `;

        // Append new notification to the container
        notificationContainer.appendChild(notification);

        // Apply fade-in animation
        notification.style.animation = "fadeInDown 0.5s forwards";

        // Remove after timeout
        setTimeout(() => {
            notification.style.animation = "fadeOutUp 0.5s forwards";
            setTimeout(() => {
                notification.remove();
            }, 500); // Wait for fade-out animation to finish
        }, 5000); // Keep visible for 2.5 seconds
    }

    toggleFolder(1)
</script>

<style>
    .file-tree-container {
        width: 100%;
        max-width: 100%;
    }

    .file-tree {
        list-style: none;
        padding: 0;
        margin: 0;
        width: 100%;
    }

    .folder-item,
    .file-item {
        display: flex;
        align-items: center;
        padding: 8px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        width: 100%;
    }

    .folder-item:hover,
    .file-item:hover {
        background: #e5e7eb;
    }

    .icon {
        margin-right: 8px;
        font-size: 18px;
    }

    .folder-name,
    .file-name {
        font-size: 14px;
        color: #333;
        font-weight: 500;
        flex-grow: 1;
    }

    .sub-folder {
        list-style: none;
        margin-top: 5px;
        padding-left: 15px;
    }

    .hidden {
        display: none !important;
    }

    /* Notification Styles */
    /* Notification Styles */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeOutUp {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }

    .notification {
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 10px;
        /* Stack notifications */
        opacity: 1;
        transition: opacity 0.5s ease-out, transform 0.5s ease-out;
    }
</style>
