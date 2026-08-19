<div class="grid grid-cols-12 gap-x-6">
    <div class="xxl:col-span-12 col-span-12">
        <div class="box">
            <div class="box-body">
                @php
                    $videoExt = pathinfo($fileToPreview, PATHINFO_EXTENSION);
                @endphp

                @if (in_array($videoExt, ['mp4', 'webm', 'ogg']))
                    <video controls class="w-full h-[500px] rounded-lg" style="height: 600px;">
                        <source src="{{ asset('storage/' . $info->path) }}" type="video/{{ $videoExt }}">
                        Your browser does not support the video tag.
                    </video>
                @else
                    <p class="text-red-500">Video format ({{ $videoExt }}) not supported for browser preview. Please
                        download to view.</p>
                @endif

            </div>
        </div>
    </div>
</div>
