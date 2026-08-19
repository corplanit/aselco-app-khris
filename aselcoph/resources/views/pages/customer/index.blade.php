<x-app-layout>

    <x-slot name="title">Manage Consumer</x-slot>
    <x-slot name="url_1">{"link": "/consumer/list", "text": "Manage Consumer"}</x-slot>
    <x-slot name="active">Information</x-slot>
    <x-slot name="buttons">
        <button class="ti-btn ti-btn-light text-dark bg-white !border-0 btn-wave me-0" data-hs-overlay="#create-contact">
            <i class="bi bi-person-plus-fill me-1"></i>Register New Consumer
        </button>
    </x-slot>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12">
            <div class="box custom-box">
                <div class="box-body p-5 main-content-card">
                    @include('pages.customer.tables.consumer')

                    @php
                        $menu = \App\Models\Menu::where('key', 'main')->with('itemsWithChildren')->first();
                        $render = function ($items) use (&$render) {
                            echo '<ul>';
                            foreach ($items as $it) {
                                echo '<li>';
                                echo '<a href="' .
                                    e($it->href) .
                                    '" target="' .
                                    e($it->target) .
                                    '">' .
                                    e($it->label) .
                                    '</a>';
                                if ($it->children->count()) {
                                    $render($it->children);
                                }
                                echo '</li>';
                            }
                            echo '</ul>';
                        };
                    @endphp

                    @if ($menu)
                        {!! $render($menu->itemsWithChildren) !!}
                    @endif

                </div>
            </div>
        </div>
    </div>

    @include('pages.customer.modals.register')
    @include('pages.customer.modals.details')
    @include('pages.customer.modals.script')


</x-app-layout>
