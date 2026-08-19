@php
    /** @var \App\Models\Menu $menu */
    $isEdit = $menu?->exists ?? false;
@endphp

<form method="POST"
      action="{{ $isEdit ? route('menus.update', $menu) : route('menus.store') }}"
      class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="grid grid-cols-12 gap-4">
        {{-- Name --}}
        <div class="col-span-12 md:col-span-6">
            <label for="name" class="block text-sm font-medium text-gray-700">
                Name <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" name="name" required
                   value="{{ old('name', $menu->name) }}"
                   class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="e.g. Main Navigation">
            <p class="mt-1 text-xs text-gray-500">Human-readable label.</p>
        </div>

        {{-- Key --}}
        <div class="col-span-12 md:col-span-6">
            <label for="key" class="block text-sm font-medium text-gray-700">
                Key <span class="text-red-500">*</span>
            </label>
            <input type="text" id="key" name="key" required
                   value="{{ old('key', $menu->key) }}"
                   class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="e.g. main, footer">
            <p class="mt-1 text-xs text-gray-500">Unique slug used in code.</p>
        </div>

        {{-- Description --}}
        <div class="col-span-12">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="Optional">{{ old('description', $menu->description) }}</textarea>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('menus.index') }}"
           class="ti-btn ti-btn-light text-dark bg-white !border-0 btn-wave">
            Cancel
        </a>
        <button type="submit"
                class="ti-btn ti-btn-primary btn-wave">
            {{ $isEdit ? 'Update Menu' : 'Create Menu' }}
        </button>
    </div>
</form>
