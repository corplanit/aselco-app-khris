<div class="fixed top-0 left-0 w-full h-full z-[1050] invisible opacity-0 transition-all duration-300 ease-in-out" id="createFolderSheet">
    <div class="absolute top-0 left-0 w-full h-full bg-black bg-opacity-50" onclick="hideCreateFolderSheet()"></div>
    <div class="translate-y-full transition-transform duration-300 ease-in-out absolute bottom-0 left-0 w-full bg-white rounded-t-3xl max-h-[calc(100vh-120px)] overflow-y-auto shadow-2xl" id="createFolderContent">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 relative">
            <div class="absolute -top-2.5 left-1/2 -translate-x-1/2 w-10 h-1 bg-gray-300 rounded-full"></div>
            <h5 class="mb-0 text-lg font-semibold">Create Folder</h5>
            <button class="btn-close" onclick="hideCreateFolderSheet()"></button>
        </div>
        <div class="p-6">
            <form id="createFolderForm">
                @csrf
                <div class="mb-4">
                    <label for="folderName" class="block text-sm font-medium text-gray-700 mb-2">Folder Name</label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="folderName" name="name" placeholder="Enter folder name" required>
                </div>
                <div class="space-y-2">
                    <button type="button" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 disabled:opacity-60 disabled:cursor-not-allowed" onclick="createFolder()">
                        <i class="bi bi-folder-plus"></i> Create Folder
                    </button>
                    <button type="button" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200" onclick="hideCreateFolderSheet()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
