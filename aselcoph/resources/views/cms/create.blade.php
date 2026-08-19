<x-app-layout>

    <x-slot name="title">Create New Articles</x-slot>
    <x-slot name="url_1">{"link": "/", "text": "Manage Blog"}</x-slot>
    <x-slot name="active">Registration</x-slot>
    <x-slot name="buttons"></x-slot>

    <link rel="stylesheet" href="/assets/libs/quill/quill.snow.css">
    <link rel="stylesheet" href="/assets/libs/quill/quill.bubble.css">
    <script src="/assets/libs/quill/quill.js"></script>



    <form action="{{ route('blog.save') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-12 gap-6">
            <div class="xl:col-span-9 col-span-9">
                <div class="box custom-box">
                    <div class="box-header">
                        <div class="box-body">
                            <label class="!mb-2"><b> Title: <b class="text-danger">*</b></b></label>
                            <input type="text" name="inp_title" class="form-control mb-3 mt-2"
                                placeholder="Post Title here..">


                            <input class="form-control xl:col-span-12 hidden" name="content_blog" id="content_blog"
                                class="" style="width: 200% !important" required>
                            <div class="xl:col-span-12 col-span-12">
                                <label class="form-label">Description : </label>
                                <div contenteditable="false" id="blog-description" oninput="updateDescription()"
                                    class=" border p-2 !min-h-[600px]" style="min-height: 600px;">
                                    Write your content here..
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="xl:col-span-3 col-span-3">
                <div class="box custom-box">
                    <div class="box-header">
                        <div class="box-body">
                            <button type="submit"
                                class="ti-btn ti-btn-success-gradient !rounded-full btn-wave  waves-effect waves-light w-full"><em
                                    class="icon ni ni-check"></em> <span>SUBMIT NEW POST</span><em
                                    class="icon ni ni-arrow-right"></em></button>
                            <hr class="mt-4 mb-4">
                            <div class="form-group  mb-4" style="display: block;">
                                <i><label class="form-label">Menu : <b class="text-danger">*</b></label></i>
                                <div class="form-control-wrap">
                                    <select class="form-select" id="post_menu" name="post_menu"
                                        data-placeholder="Select Categories">
                                        <option value="announcement">Announcement</option>
                                        <option value="news">News</option>
                                        <option value="guidelines">Guidelines</option>
                                        <option value="downloads">Downloads</option>
                                    </select>
                                </div>
                            </div>


                            <i><label class="form-label">Thumbnail : <b class="text-danger">*</b></label></i><br>
                            <img src="/upload_y.png" style="width: 100%;" class="img-thumbnail !rounded-lg"
                                id="thumbnail">
                            <input type="file" id="fileInput" name="inp_thumbnail" class="hidden" accept="image/*"
                                required onchange="previewImage(event)">



                            <!-- Custom File Button -->
                            <button type="button" onclick="document.getElementById('fileInput').click();"
                                class="w-full py-2 px-4 bg-white hover:bg-gray-200 text-dark border rounded-md transition-colors text-sm font-medium mt-4">
                                📁 Select Image
                            </button>
                            <i><label class="form-label mt-4">PDF Attachments (Optional)</label></i>
                            <input type="file" name="inp_attachment[]" accept=".pdf" multiple
                                class="block w-full border border-gray-200 focus:shadow-sm dark:focus:shadow-white/10 rounded-sm text-sm focus:z-10 focus:outline-0 focus:border-gray-200 dark:focus:border-white/10 dark:border-white/10 dark:text-white/50 file:border-0 file:bg-light file:me-4 file:py-2 file:px-4 dark:file:bg-black/20 dark:file:text-white/50">

                            <ul id="pdf-preview" class="text-sm text-gray-700 mt-2 space-y-1 hidden"></ul>
                            <script>
                                document.querySelector('input[name="inp_attachment[]"]').addEventListener('change', function() {
                                    const previewList = document.getElementById('pdf-preview');
                                    previewList.innerHTML = '';
                                    const files = Array.from(this.files);

                                    if (files.length === 0) {
                                        previewList.classList.add('hidden');
                                        return;
                                    }

                                    files.forEach((file, index) => {
                                        const listItem = document.createElement('li');
                                        listItem.innerHTML = `📄 ${file.name}`;
                                        previewList.appendChild(listItem);
                                    });

                                    previewList.classList.remove('hidden');
                                });
                            </script>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('thumbnail').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        (function() {
            "use strict"

            var toolbarOptions = [
                [{
                    'header': [1, 2, 3, 4, 5, 6, false]
                }],
                [{
                    'font': []
                }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{
                    'list': 'ordered'
                }, {
                    'list': 'bullet'
                }],
                [{
                    'indent': '-1'
                }, {
                    'indent': '+1'
                }],
                [{
                    'direction': 'rtl'
                }],
                [{
                    'color': []
                }, {
                    'background': []
                }],
                [{
                    'align': []
                }],
                ['image', 'video', 'link'],
                ['clean']
            ];
            var quill = new Quill('#blog-description', {
                modules: {
                    toolbar: toolbarOptions
                },
                theme: 'snow'
            });

            // Sync Quill content to hidden input
            const contentInput = document.querySelector('#content_blog');
            const form = document.querySelector('form');

            // On submit, sync content
            form.addEventListener('submit', function() {
                contentInput.value = quill.root.innerHTML;
            });

            // Optional: Keep it live updated on key press/paste
            quill.on('text-change', function() {
                contentInput.value = quill.root.innerHTML;
            });

            document.querySelector('form').addEventListener('submit', function() {
                document.querySelector('#content_blog').value = quill.root.innerHTML;
            });
        })();

        function updateDescription() {
            document.querySelector('#content_blog').value = document.querySelector('#blog-description')
                .innerHTML;
        }
    </script>


</x-app-layout>
