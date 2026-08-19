<div class="w-full">
    @if($info && $info->google_drive_id)
        <div class="relative bg-gray-100 rounded-lg overflow-hidden" style="height: 70vh;">
            <iframe 
                src="https://drive.google.com/file/d/{{ $info->google_drive_id }}/preview" 
                class="w-full h-full border-0"
                allow="autoplay"
                loading="lazy">
            </iframe>

            <div class="absolute inset-0 flex items-center justify-center bg-gray-50" id="pdfFallback">
                <div class="text-center">
                    <i class="bi bi-file-earmark-pdf text-red-500 text-4xl mb-3"></i>
                    <p class="text-gray-600 mb-3">Loading PDF preview...</p>
                    <a href="https://drive.google.com/file/d/{{ $info->google_drive_id }}/view" 
                       target="_blank" 
                       class="text-blue-600 hover:text-blue-800 underline">
                        Open in Google Drive
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <i class="bi bi-exclamation-triangle text-yellow-500 text-4xl mb-3"></i>
            <p class="text-gray-600">PDF preview unavailable</p>
        </div>
    @endif
</div>

<script>
    document.querySelector('iframe').addEventListener('load', function() {
        document.getElementById('pdfFallback').style.display = 'none';
    });
</script>
