<div class="w-full text-center">
    @if($info && $info->path)
        <div class="relative">
            <img 
                src="{{ asset('storage/' . $info->path) }}" 
                alt="{{ $info->name }}"
                class="max-w-full h-auto mx-auto rounded-lg shadow-lg"
                style="max-height: 70vh; object-fit: contain;"
                loading="lazy"
                onerror="this.parentElement.innerHTML='<div class=\'text-center py-8\'><i class=\'bi bi-image text-gray-400 text-4xl mb-3\'></i><p class=\'text-gray-600\'>Image could not be loaded</p></div>'"
            >

            <div class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded-lg" id="imagePlaceholder">
                <div class="text-center">
                    <div class="loading-spinner w-8 h-8 border-4 border-gray-200 border-t-blue-600 rounded-full mx-auto mb-3"></div>
                    <p class="text-gray-600 text-sm">Loading image...</p>
                </div>
            </div>
        </div>

        <div class="mt-4 text-sm text-gray-600">
            <p>{{ $info->name }}</p>
        </div>
    @else
        <div class="text-center py-8">
            <i class="bi bi-exclamation-triangle text-yellow-500 text-4xl mb-3"></i>
            <p class="text-gray-600">Image preview unavailable</p>
        </div>
    @endif
</div>

<script>
    const img = document.querySelector('img');
    if (img) {
        img.addEventListener('load', function() {
            const placeholder = document.getElementById('imagePlaceholder');
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        });
        
        if (img.complete) {
            const placeholder = document.getElementById('imagePlaceholder');
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        }
    }
</script>
