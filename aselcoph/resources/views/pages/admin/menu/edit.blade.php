<x-app-layout>
    <x-slot name="title">Edit Menu</x-slot>
    <x-slot name="url_1">{"link": "{{ route('menus.index') }}", "text": "Menus"}</x-slot>
    <x-slot name="active">Edit</x-slot>
    <x-slot name="buttons">
        <a href="{{ route('menus.index') }}" class="ti-btn ti-btn-light text-dark bg-white !border-0 btn-wave me-0">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-slot>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12">
            <div class="box custom-box">
                <div class="box-body p-5 main-content-card">
                    @if ($errors->any())
                        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <div class="font-semibold mb-2">Please fix the following:</div>
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @include('menus.partials.form', ['menu' => $menu])
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- same slugify script as create, optional on edit --}}
    @endpush
</x-app-layout>
