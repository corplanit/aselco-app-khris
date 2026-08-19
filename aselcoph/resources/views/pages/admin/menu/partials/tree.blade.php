@foreach($items as $it)
<li class="menu-item-card menu-item-collapsed border rounded-xl bg-white shadow-sm hover:shadow-md transition-all group"
    data-id="{{ $it->id }}">

    <!-- HEADER -->
    <div class="menu-item-header flex items-center gap-3 px-3 py-2">
        <!-- drag handle -->
        <div class="drag-handle w-7 h-7 inline-flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 cursor-grab ring-1 ring-gray-200/60 group-hover:ring-gray-300 ">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <circle cx="6.5" cy="7" r="1.25"/><circle cx="6.5" cy="12" r="1.25"/><circle cx="6.5" cy="17" r="1.25"/>
                <circle cx="12"  cy="7" r="1.25"/><circle cx="12"  cy="12" r="1.25"/><circle cx="12"  cy="17" r="1.25"/>
            </svg>
        </div>

        <!-- label + meta -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 min-w-0">
                <span class="truncate text-sm font-semibold text-gray-900">{{ $it->label }}</span>

                @if(!$it->is_active)
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-700 ring-1 ring-inset ring-gray-200">
                        Hidden
                    </span>
                @endif

                @if($it->link_type === 'route')
                    <span class="inline-flex items-center rounded-md bg-indigo-50 px-1.5 py-0.5 text-[10px] font-medium text-indigo-700 ring-1 ring-inset ring-indigo-200">
                        Route
                    </span>
                @else
                    <span class="inline-flex items-center rounded-md bg-sky-50 px-1.5 py-0.5 text-[10px] font-medium text-sky-700 ring-1 ring-inset ring-sky-200">
                        URL
                    </span>
                @endif

                @if($it->target === '_blank')
                    <span class="inline-flex items-center rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 ring-1 ring-inset ring-amber-200">
                        New Tab
                    </span>
                @endif
            </div>

            <div class="mt-0.5 flex items-center gap-1 text-[11px] text-gray-500 min-w-0">
                <svg class="w-3.5 h-3.5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 10-5.656 5.656m7.07-1.414a4 4 0 105.657-5.657M10 14l4-4"/>
                </svg>
                <span class="truncate">{{ $it->href }}</span>
            </div>
        </div>

        <!-- actions -->
        <div class="flex items-center gap-1">
            <button class="ti-btn ti-btn-ghost btn-wave text-red-600 hover:text-red-700 bg-red-100 rounded-xl"
                    data-action="delete-item" data-id="{{ $it->id }}" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>

    <!-- BODY (inline editor) -->
    <div class="menu-item-body border-t border-gray-100 px-3 py-3 bg-gray-50/60">
        <form class="edit-item-form space-y-3" data-id="{{ $it->id }}">
            @csrf
            <div class="grid grid-cols-12 gap-3">
                <div class="col-span-12 md:col-span-6">
                    <label class="block text-[12px] font-medium text-gray-700">Label</label>
                    <input name="label" value="{{ $it->label }}"
                           class="mt-1 w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                           required>
                </div>

                <div class="col-span-12 md:col-span-3">
                    <label class="block text-[12px] font-medium text-gray-700">Target</label>
                    <select name="target"
                            class="mt-1 w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="_self"  @selected($it->target === '_self')>Same tab</option>
                        <option value="_blank" @selected($it->target === '_blank')>New tab</option>
                    </select>
                </div>

                <div class="col-span-12 md:col-span-3">
                    <label class="block text-[12px] font-medium text-gray-700">Active</label>
                    <label class="inline-flex items-center gap-2 mt-2">
                        <input type="checkbox" name="is_active" class="ti-form-checkbox" @checked($it->is_active)>
                        <span class="text-xs text-gray-600">Visible</span>
                    </label>
                </div>

                <div class="col-span-12">
                    <label class="block text-[12px] font-medium text-gray-700">Link Type</label>
                    <div class="mt-1 inline-flex overflow-hidden rounded-lg ring-1 ring-gray-200">
                        <label class="px-3 py-1.5 text-sm flex items-center gap-2 cursor-pointer bg-white hover:bg-gray-50">
                            <input type="radio" name="link_type" value="url" class="accent-indigo-600"
                                   @checked($it->link_type === 'url')> URL
                        </label>
                        <label class="px-3 py-1.5 text-sm flex items-center gap-2 cursor-pointer bg-white hover:bg-gray-50 border-l border-gray-200">
                            <input type="radio" name="link_type" value="route" class="accent-indigo-600"
                                   @checked($it->link_type === 'route')> Route
                        </label>
                    </div>
                </div>

                {{-- URL --}}
                <div class="col-span-12 link-url" @if($it->link_type !== 'url') style="display:none" @endif>
                    <label class="block text-[12px] font-medium text-gray-700">Custom URL</label>
                    <input name="custom_url" value="{{ $it->custom_url }}" placeholder="https://..."
                           class="mt-1 w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- Route --}}
                <div class="col-span-12 link-route" @if($it->link_type !== 'route') style="display:none" @endif>
                    <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-12 md:col-span-6">
                            <label class="block text-[12px] font-medium text-gray-700">Route name</label>
                            <input name="route_name" value="{{ $it->route_name }}" placeholder="e.g. pages.show"
                                   class="mt-1 w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label class="block text-[12px] font-medium text-gray-700">Route params (JSON)</label>
                            <input name="route_params"
                                   value="{{ $it->route_params ? json_encode($it->route_params) : '' }}"
                                   placeholder='{"slug":"about"}'
                                   class="mt-1 w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <span class="save-ok hidden text-[12px] text-emerald-600">Saved</span>
                <button class="ti-btn ti-btn-primary btn-wave" type="submit">
                    <i class="bi bi-save me-1"></i>Save
                </button>
            </div>
        </form>
    </div>

    {{-- children --}}
    <ul class="menu-children ml-6 mt-2 space-y-2" data-parent-id="{{ $it->id }}">
        @if($it->children && $it->children->count())
            @include('pages.admin.menu.partials.tree', ['items' => $it->children])
        @endif
    </ul>
</li>
@endforeach
