@php
    //use ZipArchive;
    use Illuminate\Support\Facades\Hash;
    $info = App\Models\FileManager::WHERE('link', $url)->first();
    $fileToPreview = '.' . ($info->format ?? 'txt'); 
    $previewContent = asset('storage/') . ($info->path ?? null)
@endphp
<x-app-layout>

    <x-slot name="title">{{ $info->name }}</x-slot>
    <x-slot name="url_1">{"link": "/bid/list", "text": "Manage"}</x-slot>
    <x-slot name="url_2">{"link": "/bid/list", "text": "File"}</x-slot>
    <x-slot name="active">Preview</x-slot>

    <div class="grid grid-cols-12 gap-x-6">
        <div class="xxl:col-span-12 col-span-12">

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Whoops! Something went wrong.</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>W
                        @endforeach
                    </ul>
                </div>
                <hr>
            @endif

            <div class="box justify-between">
                <div class="box-body overflow-y-auto " id="discussion-container" style="max-height: 630px">
                    {{-- {{ urlencode($decodedUrl) }} https://iosbiz.com/storage/file-manager/be1ee775-c828-4159-bcfb-5cde120868dc.docx
                    https://iosbiz.com/storage/file-manager/a8654b0f-d549-40b6-9c96-13471121369e.pdf
                    {{ urlencode('https://iosbiz.com/storage/file-manager/89f7d356-f3ba-42bb-afe1-7416969006d8.xlsx') }} --}}
                    <div class="grid grid-cols-12 gap-x-6">

                        @if (Str::endsWith($fileToPreview, ['.txt', '.csv', '.html', '.json']))
                            <pre class="bg-gray-100 p-3 border">{{ $previewContent }}</pre>
                        @elseif (Str::endsWith($fileToPreview, ['.jpg', '.jpeg', '.png', '.gif', '.svg']))
                            <img src="data:image/png;base64,{{ base64_encode($previewContent) }}"
                                class="w-full max-w-md rounded shadow">
                        @elseif (Str::endsWith($fileToPreview, ['.pdf']))
                            <iframe src="data:application/pdf;base64,{{ base64_encode($previewContent) }}"
                                width="100%" height="600px"></iframe>
                        @else
                            <p class="text-red-500">Preview not available for this file type.</p>
                        @endif

                        @php

                            $randomString = Illuminate\Support\Str::random(rand(7, 7));

                            $hashedId = Hash::make('123');

                            // if (Hash::check('1234', $hashedId)) {
                            //     echo 'ID Matched!';
                            // } else {
                            //     echo 'ID Mismatch!';
                            // }

                            // echo '<br>';
                            // echo $randomString;

                            $dummy = 'file-manager/be8e3561-8e54-49bc-9e0f-0a85dab8e3d0.pdf';

                            // $zip = new ZipArchive();
                            // $fileName = '8629fb92-1b8e-467c-85d5-81ea9c68f689.zip';

                            // $filePath = storage_path("app/public/file-manager/{$fileName}");

                            // if ($zip->open($filePath) === true) {
                            //     $fileList = [];
                            //     for ($i = 0; $i < $zip->numFiles; $i++) {
                            //         $fileList[] = $zip->getNameIndex($i);
                            //     }
                            //     $zip->close();
                            //     //return view('pages.filemanager.zip-preview', compact('fileList', 'fileName'));
                            // } else {
                            //     $data = [];
                            //     echo 'error' . 'Failed to open ZIP file.';
                            // }

                        @endphp
                        
                        {{-- <div class="box-header">
                            <h2 class="text-lg font-bold">ZIP File Contents</h2>
                        </div>
                        <div class="box-body">
                            <ul class="list-disc pl-5">
                                @foreach ($fileList as $file)
                                    <li>{{ $file }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="box-footer mt-4">
                            <a href="{{ asset('storage/file-manager/' . $fileName) }}" download class="btn btn-primary">
                                Download ZIP
                            </a>
                        </div>

                        <div class="xl:col-span-12 col-span-12">
                            <iframe
                                src="https://view.officeapps.live.com/op/view.aspx?src={{ urlencode('https://iosbiz.com/storage/file-manager/89f7d356-f3ba-42bb-afe1-7416969006d8.xlsx') }}"
                                width="100%" height="900px">
                            </iframe>
                        </div> --}}
                    </div>
                    <br>
                    <br>
                </div>
            </div>
        </div>
    </div>


</x-app-layout>
