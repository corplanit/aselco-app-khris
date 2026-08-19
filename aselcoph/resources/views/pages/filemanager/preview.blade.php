@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $info = App\Models\FileManager::where('link', $url)->first();
    $fileToPreview = '.' . ($info->format ?? 'txt');
    $filePath = storage_path('app/public/' . ($info->path ?? ''));

    // Check if the file exists in storage
    $fileExists = file_exists($filePath);

    //$filePath = 'https://iosbiz.com/storage/file-manager/89f7d356-f3ba-42bb-afe1-7416969006d8.xlsx';

@endphp

<x-app-layout>

    <x-slot name="title">{{ $info->name ?? 'Preview' }}</x-slot>
    <x-slot name="url_1">{"link": "/bid/list", "text": "Manage"}</x-slot>
    <x-slot name="url_2">{"link": "/bid/list", "text": "File"}</x-slot>
    <x-slot name="active">Preview</x-slot>

    @if (!$fileExists)
        <p class="text-red-500">File not found or inaccessible.</p>
    @else
        @if (Str::endsWith($fileToPreview, ['.txt', '.csv', '.html', '.json']))
            @include('pages.filemanager.preview.txt')
        @elseif (Str::endsWith($fileToPreview, ['.jpg', '.jpeg', '.png', '.gif', '.svg', '.webp']))
            @include('pages.filemanager.preview.images')
        @elseif (Str::endsWith($fileToPreview, ['.pdf']))
            @include('pages.filemanager.preview.pdf')
        @elseif (Str::endsWith($fileToPreview, ['.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx']))
        @include('pages.filemanager.preview.msoffice')           
        @elseif (Str::endsWith($fileToPreview, ['.zip']))
            @include('pages.filemanager.preview.zip')
        @else
            <p class="text-red-500">Preview not available for this file type.</p>
        @endif
    @endif

</x-app-layout>
