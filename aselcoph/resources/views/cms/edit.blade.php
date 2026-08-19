<x-app-layout>

    @php
        $blog = App\Models\postModel::where('post_id', $id)->first();
    @endphp

    <x-slot name="title">Manage Details</x-slot>
    <x-slot name="url_1">{"link": "/", "text": "Edit"}</x-slot>
    <x-slot name="active">{{ $blog->post_title }}</x-slot>
    <x-slot name="buttons"></x-slot>

    <link rel="stylesheet" href="/assets/libs/quill/quill.snow.css">
    <link rel="stylesheet" href="/assets/libs/quill/quill.bubble.css">
    <script src="/assets/libs/quill/quill.js"></script>

    

    <form action="{{ route('blog.update', $blog->post_id) }}" id="blogUpdateForm" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-12 gap-6">
            <div class="xl:col-span-9 col-span-9">
                <div class="box custom-box">
                    <div class="box-header">
                        <div class="box-body">
                            <label class="!mb-2"><b> Title: <b class="text-danger">*</b></b></label>
                            <input type="text" name="inp_title" value="{{ old('inp_title', $blog->post_title) }}"
                                class="form-control mb-3 mt-2" placeholder="Post Title here..">


                            <input class="form-control xl:col-span-12 hidden" name="content_blog" id="content_blog"
                                class="" style="width: 200% !important">
                            <div class="xl:col-span-12 col-span-12">
                                <label class="form-label">Description : </label>
                                <div contenteditable="false" id="blog-description" oninput="updateDescription()"
                                    class=" border p-2 !min-h-[600px]" style="min-height: 600px;">
                                    {!! $blog->post_content !!}
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
                                    class="icon ni ni-check"></em> <span>UPDATE POST</span><em
                                    class="icon ni ni-arrow-right"></em></button>
                            <hr class="mt-4 mb-4">
                            <div class="form-group  mb-4" style="display: block;">
                                <i><label class="form-label">Menu : <b class="text-danger">*</b></label></i>
                                <div class="form-control-wrap">
                                    <select class="form-select" id="post_menu" name="post_menu"
                                        data-placeholder="Select Categories">
                                        <option value="announcement"
                                            {{ $blog->post_menu == 'announcement' ? 'selected' : '' }}>Announcement
                                        </option>
                                        <option value="news" {{ $blog->post_menu == 'news' ? 'selected' : '' }}>News
                                        </option>
                                        <option value="guidelines"
                                            {{ $blog->post_menu == 'guidelines' ? 'selected' : '' }}>Guidelines</option>
                                        <option value="downloads"
                                            {{ $blog->post_menu == 'downloads' ? 'selected' : '' }}>Downloads</option>
                                    </select>

                                </div>
                            </div>


                            <i><label class="form-label">Thumbnail : <b class="text-danger">*</b></label></i><br>
                            <img src="{{ asset($blog->post_thumbnail) }}" style="width: 100%;"
                                class="img-thumbnail !rounded-lg" id="thumbnail">
                            <input type="file" id="fileInput" name="inp_thumbnail" class="hidden" accept="image/*"
                                onchange="previewImage(event)">

                            <!-- Custom File Button -->
                            <button type="button" onclick="document.getElementById('fileInput').click();"
                                class="w-full py-2 px-4 bg-white hover:bg-gray-200 text-dark border rounded-md transition-colors text-sm font-medium mt-4">
                                📁 Select Image
                            </button>
                            <i><label class="form-label mt-4">PDF Attachments (Optional)</label></i>
                            <input type="file" name="inp_attachment[]" accept=".pdf" multiple
                                class="block w-full border border-gray-200 focus:shadow-sm dark:focus:shadow-white/10 rounded-sm text-sm focus:z-10 focus:outline-0 focus:border-gray-200 dark:focus:border-white/10 dark:border-white/10 dark:text-white/50 file:border-0 file:bg-light file:me-4 file:py-2 file:px-4 dark:file:bg-black/20 dark:file:text-white/50">

                            <ul id="pdf-preview" class="text-sm text-gray-700 mt-2 space-y-1 hidden"></ul>

                            @php
                                $attachments = is_array($blog->post_attachment)
                                    ? $blog->post_attachment
                                    : json_decode($blog->post_attachment, true);
                            @endphp

                            @if (!empty($attachments) && is_array($attachments))
                                <div class="mt-4">
                                    <h5><strong>Current Attachments</strong></h5>
                                    <table class="w-full text-sm text-left border mt-2 mb-4">
                                        <thead>
                                            <tr class="border-b bg-gray-100">
                                                <th class="p-2">File</th>
                                                <th class="p-2 text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($attachments as $index => $file)
                                                <tr class="border-b" id="row-{{ $index }}">
                                                    <td class="p-2">
                                                        <a href="{{ asset($file) }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            📎 {{ basename($file) }}
                                                        </a>
                                                    </td>
                                                    <td class="p-2 text-center">
                                                        <button type="button"
                                                            class="text-red-600 hover:underline text-xs"
                                                            onclick="confirmRemovePdf('{{ $file }}', '{{ $blog->post_id }}', 'row-{{ $index }}')">
                                                            ❌ Remove
                                                        </button>
                                                        <input type="hidden" name="existing_attachments[]" value="{{ $file }}">
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <script>
                                function confirmRemovePdf(file, postId, rowId) {
                                    Swal.fire({
                                        title: 'Remove this file?',
                                        text: "This action cannot be undone.",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#d33',
                                        cancelButtonColor: '#aaa',
                                        confirmButtonText: 'Yes, remove it!'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            fetch("{{ route('blog.remove_pdf') }}", {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                    },
                                                    body: JSON.stringify({
                                                        post_id: postId,
                                                        file: file
                                                    })
                                                })
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        document.getElementById(rowId).remove();
                                                        Swal.fire('Removed!', 'The file has been deleted.', 'success');
                                                    } else {
                                                        Swal.fire('Error!', 'Something went wrong.', 'error');
                                                    }
                                                })
                                                .catch(error => {
                                                    Swal.fire('Error!', error.message, 'error');
                                                });
                                        }
                                    });
                                }
                            </script>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const form = document.getElementById('blogUpdateForm');

                                    form.addEventListener('submit', function(e) {
                                        e.preventDefault();

                                        Swal.fire({
                                            title: 'Are you sure?',
                                            text: "Update this post?",
                                            icon: 'question',
                                            showCancelButton: true,
                                            confirmButtonColor: '#3085d6',
                                            cancelButtonColor: '#d33',
                                            confirmButtonText: 'Yes, update it!'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                const formData = new FormData(form);

                                                // Set Quill content manually
                                                const quillEditor = document.querySelector('.ql-editor');
                                                if (quillEditor) {
                                                    formData.set('content_blog', quillEditor.innerHTML);
                                                }

                                                fetch(form.action, {
                                                        method: 'POST',
                                                        headers: {
                                                            'X-CSRF-TOKEN': document.querySelector(
                                                                'input[name="_token"]').value,
                                                            'Accept': 'application/json'
                                                        },
                                                        body: formData
                                                    })
                                                    .then(response => {
                                                        if (!response.ok) {
                                                            return response.json().then(data => {
                                                                throw new Error(data.message ||
                                                                    'Update failed.');
                                                            });
                                                        }
                                                        return response.json();
                                                    })
                                                    .then(data => {
                                                        Swal.fire('Updated!', 'The post has been successfully updated.',
                                                                'success')
                                                            .then(() => window.location.reload());
                                                    })
                                                    .catch(error => {
                                                        Swal.fire('Error!', error.message, 'error');
                                                        console.log(error.message)
                                                    });
                                            }
                                        });
                                    });
                                });
                            </script>


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
                            <script></script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="removed_attachments" id="removed_attachments" value="">
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

        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            // Quill toolbar config
            const toolbarOptions = [
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

            // Init Quill
            const quill = new Quill('#blog-description', {
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

            // Remove attachment and mark content as changed
            window.removeExistingAttachment = function(index, button) {
                const row = button.closest('tr');
                if (row) {
                    const input = row.querySelector('input[name="existing_attachments[]"]');
                    if (input) {
                        const removedField = document.getElementById('removed_attachments');
                        const removedList = removedField.value ? JSON.parse(removedField.value) : [];
                        removedList.push(input.value);
                        removedField.value = JSON.stringify(removedList);
                    }

                    row.remove();
                }

                // Ensure some form value is changed (to trigger Laravel update)
                document.getElementById('content_blog').value += ' ';
            };

        });
    </script>



</x-app-layout>
