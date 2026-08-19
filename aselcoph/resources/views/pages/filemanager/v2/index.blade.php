<x-app-layout>

    <x-slot name="title">File Manager</x-slot>
    <x-slot name="url_1">{"link": "/bid/list", "text": "Manage"}</x-slot>
    <x-slot name="url_2">{"link": "/bid/list", "text": "File"}</x-slot>
    <x-slot name="active">File Manager</x-slot>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12">
            <div class="box custom-box">
                <div class="box-header">
                    <div class="box-body">
                        {{-- @include('pages.filemanager.create_folder')
                        @include('pages.filemanager.create_files') --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
