@if (isset($title))
    <div class="flex items-center justify-between page-header-breadcrumb flex-wrap gap-2 mb-4 ">
        <div>
            <h1 class="text-2xl">
                {{-- @if (count(Request::segments()) >= 3)
                <a href="{{ count(Request::segments()) >= 4 ? '../' : (count(Request::segments()) >= 3 ? './' : '') }}"
                    class="inline-flex items-center mb-3 gap-1 text-sm text-gray-600 hover:underline">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left-dashed">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M5 12h6m3 0h1.5m3 0h.5" />
                        <path d="M5 12l6 6" />
                        <path d="M5 12l6 -6" />
                    </svg>
                    Back
                </a>
            @endif --}}

                <div class="mt-1">
                    {{ $title ?? '' }}
                </div>
            </h1>


            <nav>
                <ol class="flex items-center whitespace-nowrap min-w-0 pb-2 mt-4">
                    <li class="text-sm">
                        <a class="flex items-center text-primary hover:text-primary dark:text-primary"
                            href="javascript:void(0);">
                            <svg class="flex-shrink-0 me-3 h-4 w-4 text-primary hover:text-primary dark:text-primary"
                                width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zm11-11V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z" />
                                <path fill-rule="evenodd"
                                    d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z" />
                            </svg>
                            Home
                            <svg class="flex-shrink-0 mx-3 overflow-visible h-2.5 w-2.5 text-gray-300 dark:text-white/10 rtl:rotate-180"
                                width="16" height="16" viewBox="0 0 16 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 1L10.6869 7.16086C10.8637 7.35239 10.8637 7.64761 10.6869 7.83914L5 14"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </a>
                    </li>
                    @for ($i = 0; $i < 10; $i++)
                        @php
                            $slotVar = 'url_' . $i; // Generate dynamic slot name
                            $slotValue = isset($$slotVar) ? json_decode($$slotVar, true) : null;
                        @endphp

                        @if (!empty($slotValue) && is_array($slotValue))
                            <li class="text-sm">
                                <a class="flex items-center text-primary hover:text-primary dark:text-primary"
                                    href="{{ $slotValue['link'] ?? 'javascript:void(0);' }}">
                                    {{ $slotValue['text'] ?? 'Plan Panther ' . $i }}
                                    <svg class="flex-shrink-0 mx-3 overflow-visible h-2.5 w-2.5 text-gray-300 dark:text-white/10 rtl:rotate-180"
                                        width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M5 1L10.6869 7.16086C10.8637 7.35239 10.8637 7.64761 10.6869 7.83914L5 14"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                </a>
                            </li>
                        @endif
                    @endfor

                    <li class="text-sm">
                        <a class="flex items-center text-gray-500 dark:text-[#8c9097] dark:text-white/50 hover:text-primary"
                            href="javascript:void(0);">
                            {{ $active ?? '' }}
                        </a>
                    </li>
                </ol>
            </nav>
        </div>
        <div class="btn-list">
            {{ $buttons ?? '' }}
        </div>
    </div>
@endif
