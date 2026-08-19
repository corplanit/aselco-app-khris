<x-app-layout>

    <x-slot name="title">Manage Customer Complaint</x-slot>
    <x-slot name="url_1">{"link": "/dashboard", "text": "Manage"}</x-slot>
    <x-slot name="url_2">{"link": "/dashboard", "text": "Customer Complaint"}</x-slot>
    <x-slot name="active">Information</x-slot>
    <x-slot name="buttons"></x-slot>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12 mb-0">

            <div class="box">
                <div class="box-body">

                    <i class="bi bi-info-circle px-1"></i> You can check the complaints here.
                    <hr class="mb-3 mt-3">

                    @include('pages.dashboard.complaint-admin')

                </div>
            </div>

        </div>

    </div>

</x-app-layout>
