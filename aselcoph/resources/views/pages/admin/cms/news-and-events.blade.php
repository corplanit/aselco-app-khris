<x-app-layout>

    <x-slot name="title">News & Events</x-slot>
    <x-slot name="url_1">{"link": "/admin/news-and-events", "text": "Manage"}</x-slot>
    <x-slot name="active">News & Events</x-slot>
    <x-slot name="buttons"></x-slot>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12">
            <div class="box">
                <div class="box-body">
                    <i class="bi bi-info-circle px-1"></i> You can manage the news & events here.
                    <hr class="mb-3 mt-3">
                    <div class="custom-box">
                        {{--  Contents --}}
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</x-app-layout>
