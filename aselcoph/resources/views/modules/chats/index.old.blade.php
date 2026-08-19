<x-app-layout>

    <x-slot name="return">{"link": "/chats/message", "text": "Manage"}</x-slot>
    <x-slot name="title">Chats</x-slot>
    <x-slot name="url_1">{"link": "/chats/message", "text": "Chats"}</x-slot>
    <x-slot name="active">Messages</x-slot>
    <x-slot name="buttons"> </x-slot>

    <!-- Mobile Layout -->
    {{-- <div class="mobile-response">
        @include('modules.chats.mobile')
    </div> --}}

    <!-- Desktop Layout -->
    <div class="grid grid-cols-12 gap-6 desktop-response">
        <div class="xl:col-span-12 col-span-12">
            <div class="box rounded-md shadow-none border"  style="background: linear-gradient(to bottom right, #fff, #ffffff, #F2F7FF);">
                <div class="box-body p-0">
                      @include('modules.chats.partials.messages')
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
