<div class="fixed top-0 left-0 w-full h-full z-[1050] invisible opacity-0 transition-all duration-300 ease-in-out" id="uploadSheet">
    <div class="absolute top-0 left-0 w-full h-full bg-black bg-opacity-50" onclick="hideUploadSheet()"></div>
    <div class="translate-y-full transition-transform duration-300 ease-in-out absolute bottom-0 left-0 w-full bg-white rounded-t-3xl max-h-[calc(100vh-120px)] overflow-y-auto shadow-2xl" id="bottomSheetContent">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 relative">
            <div class="absolute -top-2.5 left-1/2 -translate-x-1/2 w-10 h-1 bg-gray-300 rounded-full"></div>
            <h5 class="mb-0 text-lg font-semibold">Upload Files</h5>
            <button class="btn-close" onclick="hideUploadSheet()"></button>
        </div>
        <div class="p-6">
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="fileInput" class="block text-sm font-medium text-gray-700 mb-2">Choose Files</label>
                    <input type="file" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="fileInput" name="files[]" multiple accept="*/*">
                    <div class="text-xs text-gray-500 mt-1">You can select multiple files</div>
                </div>
                <div class="mb-4">
                    <label for="folderSelect" class="block text-sm font-medium text-gray-700 mb-2">Upload to Folder</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="folderSelect" name="folder_id">
                        <option value="">Root Folder</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <button type="button" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 disabled:opacity-60 disabled:cursor-not-allowed" onclick="uploadFiles()">
                        <i class="bi bi-upload"></i> Upload Files
                    </button>
                    <button type="button" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200" onclick="hideUploadSheet()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>