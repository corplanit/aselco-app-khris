<div class="container-fluid p-3">

    <!-- File Loading Overlay -->
    <div id="fileLoadingOverlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.7); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 16px; padding: 32px; text-align: center; max-width: 280px; margin: 0 16px;">
            <div style="width: 48px; height: 48px; border: 4px solid #dbeafe; border-top: 4px solid #3b82f6; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 16px;"></div>
            <p style="font-size: 16px; font-weight: 500; color: #333; margin: 0 0 8px 0;">Opening file...</p>
            <p style="font-size: 13px; color: #6c757d; margin: 0;">Please wait a moment</p>
        </div>
    </div>

    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" class="form-control" placeholder="Search files..." id="mobileFileSearch">
        </div>
    </div>
    @include('modules.file-manager.mobile.components.pagination')


    {{-- Folders Section --}}
    @php
        $folders = $files->where('is_folder', 1);
        $regularFiles = $files->where('is_folder', 0);
    @endphp
    
    @if($folders->count() > 0)
        <div class="mb-4">
            <h6 class="text-sm font-semibold text-gray-600 mb-2">
                <i class="bi bi-folder-fill text-warning me-1"></i>
                Folders ({{ $folders->count() }})
            </h6>
            <div class="row g-2">
                @foreach($folders as $folder)
                    @php
                        $folder_count = App\Models\FileManager::where('parent_id', $folder->id)
                            ->where('user_id', $clientId)
                            ->where('is_folder', 1)
                            ->where('isDeleted', 0)
                            ->count();
                        
                        $files_count = App\Models\FileManager::where('parent_id', $folder->id)
                            ->where('user_id', $clientId)
                            ->where('is_folder', 0)
                            ->where('isDeleted', 0)
                            ->count();
                            
                        $files_sum = App\Models\FileManager::where('parent_id', $folder->id)
                            ->where('user_id', $clientId)
                            ->where('isDeleted', 0)
                            ->sum('size');
                    @endphp
                    
                    <div class="col-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <a href="{{ route('filemanager.index', ['f' => $folder->id]) }}" class="text-decoration-none" onclick="showFileLoading('Opening folder...')">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-folder-fill text-warning me-2" style="font-size: 1.5rem;"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 text-sm font-semibold text-truncate">{{ $folder->name }}</h6>
                                        </div>
                                    </div>
                                    <div class="text-xs text-muted">
                                        {{ $files_count }} files • {{ number_format($files_sum / 1024, 1) }} KB
                                    </div>
                                </a>
                                <div class="dropdown position-absolute top-0 end-0 m-2">
                                    <button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('filemanager.index', ['f' => $folder->id]) }}" onclick="showFileLoading('Opening folder...')">
                                            <i class="bi bi-folder-open me-2"></i>Open</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="renameFolder('{{ $folder->id }}', '{{ $folder->name }}')">
                                            <i class="bi bi-pencil me-2"></i>Rename</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteFile('{{ $folder->id }}')">
                                            <i class="bi bi-trash me-2"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Files Section --}}
    <div class="file-list-mobile">
        @if($regularFiles->count() > 0)
            <h6 class="text-sm font-semibold text-gray-600 mb-2">
                <i class="bi bi-file-earmark text-primary me-1"></i>
                Files ({{ $regularFiles->count() }})
            </h6>
            @foreach($regularFiles as $file)
            <div class="card mb-2 transition-all duration-200 ease-in-out border border-gray-200 hover:-translate-y-0.5 hover:shadow-lg hover:border-blue-500" data-filename="{{ $file->name ?? '' }}">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex items-center justify-center w-10 h-10 bg-gray-50 rounded-lg me-3">
                            @if($file->is_folder)
                                <i class="bi bi-folder-fill text-warning" style="font-size: 1.5rem;"></i>
                            @else
                                @php
                                    $extension = pathinfo($file->name ?? '', PATHINFO_EXTENSION);
                                @endphp
                                @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    <i class="bi bi-file-earmark-image text-success" style="font-size: 1.5rem;"></i>
                                @elseif(in_array(strtolower($extension), ['pdf']))
                                    <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 1.5rem;"></i>
                                @elseif(in_array(strtolower($extension), ['doc', 'docx']))
                                    <i class="bi bi-file-earmark-word text-primary" style="font-size: 1.5rem;"></i>
                                @elseif(in_array(strtolower($extension), ['xls', 'xlsx']))
                                    <i class="bi bi-file-earmark-excel text-success" style="font-size: 1.5rem;"></i>
                                @elseif(in_array(strtolower($extension), ['mp4', 'avi', 'mov']))
                                    <i class="bi bi-file-earmark-play text-info" style="font-size: 1.5rem;"></i>
                                @else
                                    <i class="bi bi-file-earmark text-secondary" style="font-size: 1.5rem;"></i>
                                @endif
                            @endif
                        </div>
                        
                        <div class="file-info flex-grow-1">
                            @if($file->is_folder)
                                <h6 class="mb-1 text-sm font-semibold text-truncate">{{ $file->name }}</h6>
                            @else
                                <a href="#" onclick="previewFile('{{ $file->google_drive_id ?? '' }}')" class="text-decoration-none">
                                    <h6 class="mb-1 text-sm font-semibold text-truncate text-primary hover:text-blue-700 transition-colors">{{ $file->name }}</h6>
                                </a>
                            @endif
                            <p class="text-xs text-muted mb-1">
                                @if($file->is_folder)
                                    Folder • {{ $file->created_at ? $file->created_at->format('M d, Y') : 'Unknown date' }}
                                @else
                                    {{ number_format($file->size / 1024, 1) }} KB • {{ $file->created_at ? $file->created_at->format('M d, Y') : 'Unknown date' }}
                                @endif
                            </p>
                        </div>
                        <div class="file-actions">
                            <div class="dropdown">
                                <button class="border-0 bg-transparent py-1 px-2 focus:shadow-none" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu border border-gray-200 shadow-lg">
                                    @if($file->is_folder)
                                        <li><a class="dropdown-item" href="{{ route('filemanager.index', ['f' => $file->id]) }}" onclick="showFileLoading('Opening folder...')">
                                            <i class="bi bi-folder-open me-2"></i>Open Folder</a></li>
                                    @else
                                        <li><a class="dropdown-item" href="#" onclick="previewFile('{{ $file->google_drive_id ?? '' }}')">
                                            <i class="bi bi-eye me-2"></i>Preview</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="downloadFile('{{ $file->google_drive_id ?? '' }}')">
                                            <i class="bi bi-download me-2"></i>Download</a></li>
                                    @endif
                                    <li><a class="dropdown-item" href="#" onclick="shareFile('{{ $file->id ?? '' }}')">
                                        <i class="bi bi-share me-2"></i>Share</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteFile('{{ $file->id ?? '' }}')">
                                        <i class="bi bi-trash me-2"></i>Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="text-center py-5">
                <i class="bi bi-folder-x" style="font-size: 3rem; color: #6c757d;"></i>
                <h5 class="mt-3 text-muted">No files found</h5>
                <p class="text-muted">Upload some files to get started</p>
            </div>
        @endif
    </div>

    <div style="height: 100px;"></div>
</div>

@include('modules.file-manager.mobile.bottom-sheets.upload')
@include('modules.file-manager.mobile.bottom-sheets.create-folder')
@include('modules.file-manager.mobile.bottom-sheets.share')
@include('modules.file-manager.mobile.components.scroll-to-bottom')
@include('modules.file-manager.mobile.components.popup-modal')



<script>
// Global pagination variables
let currentPage = {{ $files->currentPage() ?? 1 }};
let lastPage = {{ $files->lastPage() ?? 1 }};

document.addEventListener('DOMContentLoaded', function() {
    updatePaginationUI();
    
    const searchInput = document.getElementById('mobileFileSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const fileItems = document.querySelectorAll('.file-item');
            
            fileItems.forEach(item => {
                const fileName = item.querySelector('.file-name').textContent.toLowerCase();
                if (fileName.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    const fileTypeSelect = document.getElementById('mobileFileType');
    if (fileTypeSelect) {
        fileTypeSelect.addEventListener('change', function() {
            const selectedType = this.value.toLowerCase();
            const fileItems = document.querySelectorAll('.file-item');
            
            fileItems.forEach(item => {
                const fileName = item.getAttribute('data-filename').toLowerCase();
                if (!selectedType || fileName.includes(selectedType)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});

function showUploadSheet() {
    const sheet = document.getElementById('uploadSheet');
    const content = document.getElementById('bottomSheetContent');
    
    sheet.classList.remove('invisible', 'opacity-0');
    sheet.classList.add('visible', 'opacity-100');
    
    setTimeout(() => {
        content.classList.remove('translate-y-full');
        content.classList.add('translate-y-0');
        content.style.transform = 'translateY(0)';
        content.style.marginBottom = '60px';
    }, 10);
    
    document.body.style.overflow = 'hidden';
}

function hideUploadSheet() {
    const sheet = document.getElementById('uploadSheet');
    const content = document.getElementById('bottomSheetContent');
    
    content.classList.remove('translate-y-0');
    content.classList.add('translate-y-full');
    
    setTimeout(() => {
        sheet.classList.remove('visible', 'opacity-100');
        sheet.classList.add('invisible', 'opacity-0');
    }, 300);
    
    document.body.style.overflow = 'auto';
}

function showCreateFolderSheet() {
    const sheet = document.getElementById('createFolderSheet');
    const content = document.getElementById('createFolderContent');
    
    sheet.classList.remove('invisible', 'opacity-0');
    sheet.classList.add('visible', 'opacity-100');
    
    setTimeout(() => {
        content.classList.remove('translate-y-full');
        content.classList.add('translate-y-0');
        content.style.transform = 'translateY(0)';
        content.style.marginBottom = '60px';
    }, 10);
    
    document.body.style.overflow = 'hidden';
}

function hideCreateFolderSheet() {
    const sheet = document.getElementById('createFolderSheet');
    const content = document.getElementById('createFolderContent');
    
    content.classList.remove('translate-y-0');
    content.classList.add('translate-y-full');
    
    setTimeout(() => {
        sheet.classList.remove('visible', 'opacity-100');
        sheet.classList.add('invisible', 'opacity-0');
    }, 300);
    
    document.body.style.overflow = 'auto';
    
    document.getElementById('folderName').value = '';
}

function createFolder() {
    const folderName = document.getElementById('folderName').value.trim();
    
    if (!folderName) {
        showError('Please enter a folder name');
        return;
    }
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('name', folderName);
    formData.append('parent_id', '{{ request()->query("f") ?? "" }}');
    
    const createBtn = document.querySelector('[onclick="createFolder()"]');
    const originalText = createBtn.innerHTML;
    createBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';
    createBtn.disabled = true;
    
    fetch('/drive/folder/create', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideCreateFolderSheet();
            showSuccess('Folder created successfully!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showError('Error creating folder: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Create folder error:', error);
        showError('Error creating folder');
    })
    .finally(() => {
        createBtn.innerHTML = originalText;
        createBtn.disabled = false;
    });
}

function renameFolder(folderId, currentName) {
    const newName = prompt('Enter new folder name:', currentName);
    if (newName && newName !== currentName) {
        console.log('Rename folder', folderId, 'to', newName);
    }
}

function showFileLoading(message = 'Opening file...') {
    const loadingOverlay = document.getElementById('fileLoadingOverlay');
    loadingOverlay.querySelector('p:first-of-type').textContent = message;
    loadingOverlay.style.display = 'flex';
}

function previewFile(googleDriveId) {
    if (googleDriveId) {
        showFileLoading('Opening file...');
        window.location.href = `/file-manager/preview/${googleDriveId}`;
    }
}

function downloadFile(googleDriveId) {
    if (googleDriveId) {
        showFileLoading('Downloading...');
        window.location.href = `/download-file/${googleDriveId}`;
        
        setTimeout(() => {
            document.getElementById('fileLoadingOverlay').style.display = 'none';
        }, 3000);
    }
}

function shareFile(fileId) {
    if (fileId) {
        const shareLink = `${window.location.origin}/file-manager/share/${fileId}`;
        document.getElementById('shareLink').value = shareLink;
        window.currentShareFileId = fileId;
        const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
        shareModal.show();
    }
}

function copyShareLink() {
    const shareLink = document.getElementById('shareLink');
    shareLink.select();
    shareLink.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(shareLink.value).then(() => {
        const copyBtn = document.querySelector('[onclick="copyShareLink()"]');
        const originalText = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="bi bi-check"></i> Copied!';
        copyBtn.classList.add('btn-success');
        copyBtn.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            copyBtn.innerHTML = originalText;
            copyBtn.classList.remove('btn-success');
            copyBtn.classList.add('btn-outline-secondary');
        }, 2000);
    });
}

function sendShareEmail() {
    const email = document.getElementById('shareEmail').value;
    const access = document.getElementById('shareAccess').value;
    const fileId = window.currentShareFileId;
    
    if (!email) {
        alert('Please enter an email address');
        return;
    }
    
    if (!fileId) {
        alert('No file selected for sharing');
        return;
    }
    
    fetch('/file-manager/share', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            file_id: fileId,
            email: email,
            access_level: access
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('File shared successfully!');
            const shareModal = bootstrap.Modal.getInstance(document.getElementById('shareModal'));
            shareModal.hide();
            document.getElementById('shareEmail').value = '';
            document.getElementById('shareAccess').value = 'view';
        } else {
            showError('Error sharing file: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Share error:', error);
        showError('Error sharing file');
    });
}

async function deleteFile(fileId) {
    const confirmed = await showConfirm('Are you sure you want to delete this file? This action cannot be undone.');
    
    if (confirmed) {
        fetch('/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                id: fileId,
                type: 'file-manager'
            })
        })
        .then(response => response.json())
        .then(data => {
            showSuccess('File deleted successfully!');
            setTimeout(() => location.reload(), 1500);
        })
        .catch(error => {
            console.error('Delete error:', error);
            showError('Error deleting file');
        });
    }
}

function uploadFiles() {
    const fileInput = document.getElementById('fileInput');
    const folderSelect = document.getElementById('folderSelect');
    
    if (!fileInput.files.length) {
        showError('Please select files to upload');
        return;
    }
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    for (let i = 0; i < fileInput.files.length; i++) {
        formData.append('files[]', fileInput.files[i]);
    }
    
    if (folderSelect.value) {
        formData.append('folder_id', folderSelect.value);
    }
    
    const uploadBtn = document.querySelector('[onclick="uploadFiles()"]');
    const originalText = uploadBtn.innerHTML;
    uploadBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Uploading...';
    uploadBtn.disabled = true;
    
    fetch('/file-manager/upload', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideUploadSheet();
            showSuccess('Files uploaded successfully!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showError('Error uploading files: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showError('Error uploading files');
    })
    .finally(() => {
        uploadBtn.innerHTML = originalText;
        uploadBtn.disabled = false;
    });
}
</script>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

#uploadSheet.visible #bottomSheetContent,
#createFolderSheet.visible #createFolderContent {
    transform: translateY(0) !important;
    margin-bottom: 60px; 
}
.upload-bottom-sheet.show .bottom-sheet-content,
.create-folder-sheet.show .bottom-sheet-content {
    bottom: 60px !important;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

.file-info {
    min-width: 0;
    flex: 1;
}

.card .file-info h6 {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 200px; 
}

.card .file-info a h6 {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 200px;
    display: block;
}
</style>
