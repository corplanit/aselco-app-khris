<x-app-layout>
    <x-slot name="title">Create Menu</x-slot>
    <x-slot name="url_1">{"link": "{{ route('menus.index') }}", "text": "Menus"}</x-slot>
    <x-slot name="active">Create</x-slot>
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

                    @php($menu = new \App\Models\Menu)
                    @include('pages.admin.menu.partials.form', ['menu' => $menu])

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function(){
        const nameEl = document.getElementById('name');
        const keyEl  = document.getElementById('key');
        if (!nameEl || !keyEl) return;

        let keyDirty = keyEl.value.trim().length > 0;
        keyEl.addEventListener('input', () => { keyDirty = true; });

        function slugify(v){
            return v.toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
                .replace(/[^a-z0-9]+/g,'-')
                .replace(/(^-|-$)/g,'')
                .substring(0, 100);
        }

        nameEl.addEventListener('input', () => {
            if (keyDirty) return;
            keyEl.value = slugify(nameEl.value);
        });
    })();
    </script>
    @endpush
</x-app-layout>
