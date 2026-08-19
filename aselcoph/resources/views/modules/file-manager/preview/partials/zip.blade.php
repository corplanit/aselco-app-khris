<div class="w-full">
    <div class="text-center py-8">
        <i class="bi bi-file-earmark-zip text-purple-600 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">ZIP Archive</h3>
        <p class="text-gray-600 mb-4">{{ $info->name ?? 'Archive file' }}</p>
        
        <div class="bg-gray-50 rounded-lg p-4 mb-4 text-left max-w-md mx-auto">
            <h4 class="font-medium text-gray-700 mb-2">File Information:</h4>
            <ul class="text-sm text-gray-600 space-y-1">
                <li><strong>Type:</strong> ZIP Archive</li>
                <li><strong>Size:</strong> {{ number_format(($info->size ?? 0) / 1024 / 1024, 2) }} MB</li>
                <li><strong>Format:</strong> {{ strtoupper($info->format ?? 'zip') }}</li>
            </ul>
        </div>
        
        <p class="text-gray-500 text-sm mb-4">
            ZIP files cannot be previewed directly.<br>
            Download the file to extract and view its contents.
        </p>
        
        <button onclick="downloadFile()" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
            <i class="bi bi-download"></i>
            Download ZIP File
        </button>
    </div>
</div>
