<div class="grid grid-cols-12 gap-x-6">
    <div class="xxl:col-span-12 col-span-12">
        <div class="box">
            <div class="box-header mb-0 pb-0">
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

            <div class="box-body overflow-y-auto border-1 relative">
                <div class="file-tree-container border-2 p-3 relative">
                    <!-- Fullscreen Button (Now Correctly Positioned) -->
                    <button id="fullscreenBtn" 
                        class="absolute top-4 right-4 bg-gray-800 text-white px-3 py-2 rounded-md text-sm shadow-md 
                            hover:bg-gray-900 transition-all z-[1000]"
                        onclick="toggleFullScreen()">
                        ⛶ Fullscreen
                    </button>

                    <!-- File Preview -->
                    <iframe id="fileViewer" src="{{ asset('storage/' . $info->path) }}" 
                        class="w-full h-[900px] transition-all duration-300"
                        width="100%" height="900px">
                    </iframe>
                </div>
            </div>

            <div class="box-footer p-4 px-6">
                {{ date_format($info->created_at, 'D, M, d, Y h:i A') }}
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Fullscreen Mode (Fixed ESC Key Exit) -->
<script>
    function toggleFullScreen() {
        let viewer = document.getElementById("fileViewer");

        if (!document.fullscreenElement) {
            if (viewer.requestFullscreen) {
                viewer.requestFullscreen();
            } else if (viewer.mozRequestFullScreen) { // Firefox
                viewer.mozRequestFullScreen();
            } else if (viewer.webkitRequestFullscreen) { // Chrome, Safari, Opera
                viewer.webkitRequestFullscreen();
            } else if (viewer.msRequestFullscreen) { // IE/Edge
                viewer.msRequestFullscreen();
            }
        } else {
            document.exitFullscreen();
        }
    }

    // Listen for fullscreen change and bind ESC key
    document.addEventListener("fullscreenchange", () => {
        if (!document.fullscreenElement) {
            document.getElementById("fullscreenBtn").style.display = "block";
        } else {
            document.getElementById("fullscreenBtn").style.display = "none";
        }
    });

    // Exit fullscreen when ESC is pressed
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && document.fullscreenElement) {
            document.exitFullscreen();
        }
    });
</script>
