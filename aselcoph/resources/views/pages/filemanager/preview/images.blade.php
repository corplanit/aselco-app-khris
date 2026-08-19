<div class="grid grid-cols-12 gap-x-6">
    <div class="xxl:col-span-12 col-span-12">
        <div class="box">
            <div class="box-body">
                <!-- Image Container -->
                <div class="box-body bg-light overflow-hidden flex justify-center items-center p-4">
                    <img src="{{ asset('storage/' . $info->path) }}"
                        class="rounded-md max-w-lg h-auto object-contain cursor-pointer transition-opacity duration-300 opacity-100 hover:opacity-80"
                        onclick="openImageModal(this)">
                </div>

                <!-- Fullscreen Image Modal -->
                <div id="imageModal"
                    class="fixed inset-0 bg-black bg-opacity-70 hidden flex justify-center items-center z-[9999] p-5 opacity-0 transition-opacity duration-300">
                    
                    <!-- Fullscreen Image -->
                    <img id="modalImage"
                        class="w-auto h-auto max-w-full max-h-full object-contain z-[10000] opacity-0 transition-opacity duration-300">
                    
                    <!-- Close Button -->
                    <button id="closeButton"
                        class="absolute top-5 right-5 text-red-500 bg-white p-4 rounded-md shadow-lg text-md font-bold cursor-pointer z-[10000] hover:scale-110 hover:text-red-700 transition-transform"
                        onclick="closeImageModal()" style="right: 20px !important">
                        ✕
                    </button>
                </div>

                <!-- JavaScript for Modal & ESC Key -->
                <script>
                    function openImageModal(img) {
                        let modal = document.getElementById("imageModal");
                        let modalImg = document.getElementById("modalImage");

                        modalImg.src = img.src;
                        modal.classList.remove("hidden");

                        // Add fade-in effect
                        setTimeout(() => {
                            modal.classList.add("opacity-100");
                            modalImg.classList.add("opacity-100");
                        }, 10);

                        // ESC key support
                        document.addEventListener("keydown", handleKeyPress);
                    }

                    function closeImageModal() {
                        let modal = document.getElementById("imageModal");
                        let modalImg = document.getElementById("modalImage");

                        // Fade out effect
                        modal.classList.remove("opacity-100");
                        modalImg.classList.remove("opacity-100");

                        setTimeout(() => {
                            modal.classList.add("hidden");
                        }, 300); // Wait for fade-out to finish

                        // Remove ESC key event listener
                        document.removeEventListener("keydown", handleKeyPress);
                    }

                    function handleKeyPress(event) {
                        if (event.key === "Escape") {
                            closeImageModal();
                        }
                    }
                </script>

            </div>
        </div>
    </div>
</div>
