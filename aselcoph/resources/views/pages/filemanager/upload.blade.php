<x-app-layout>

    <x-slot name="title">Upload To Google Drive</x-slot>
    <x-slot name="url_1">{"link": "/client/list", "text": "Feedback"}</x-slot>
    <x-slot name="active">Hub</x-slot>
    <x-slot name="buttons"></x-slot>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12">
            <div class="box custom-box">
                <div class="box-header">
                    <div class="box-body">
                        @php
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            $output = curl_exec($ch);
                            if (curl_errno($ch)) {
                                echo 'Curl error: ' . curl_error($ch);
                            } else {
                                echo 'No SSL error. All good!';
                            }
                            curl_close($ch);
                        @endphp
                        <hr class="mt-6 mb-6">
                        <form action="/upload-file" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="file" required>
                            <button type="submit">Upload to Google Drive</button>
                        </form>

                        <hr class="mt-6 mb-6">
                        
                        <form action="{{ route('drive.folder.create') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="name">Folder Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                    
                            <div class="form-group mt-2">
                                <label for="parent_id">Parent Folder (Optional)</label>
                                <input type="text" name="parent_id" class="form-control" placeholder="Internal DB parent_id">
                            </div>
                    
                            <button type="submit" class="btn btn-primary mt-3">Create Folder</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
