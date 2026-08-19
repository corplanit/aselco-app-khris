<div class="grid grid-cols-12 gap-3 shadow-none">
    @foreach ($files as $file)
        @if ($file->is_folder)
            @include('modules.file-manager.partials.folder-card', [
                'file' => $file,
                'clientId' => $clientId,
            ])
        @endif
    @endforeach
</div>
