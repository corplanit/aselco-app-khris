<div class="grid grid-cols-12 gap-x-6">
    <div class="xxl:col-span-12 col-span-12">
        <div class="box">
            <div class="box-header">
                @php
                    function formatSize($size)
                    {
                        if ($size < 1024) {
                            return $size . ' B';
                        } elseif ($size < 1048576) {
                            return round($size / 1024, 2) . ' KB';
                        } elseif ($size < 1073741824) {
                            return round($size / 1048576, 2) . ' MB';
                        } else {
                            return round($size / 1073741824, 2) . ' GB';
                        }
                    }
                @endphp
                <ul class="mt-2 text-sm text-gray-600 px-2">
                    <li class="mb-1"><strong class="text-gray-800">📄 Name:</strong> {{ $info->name }}</li>
                    <li class="mb-1"><strong class="text-gray-800">📦 Size:</strong> {{ formatSize($info->size) }}</li>
                </ul>
            </div>
            <div class="box-body overflow-y-auto border-1">
                <pre class="bg-white p-3 overflow-y-auto border">{{ file_get_contents($filePath) }}</pre>
            </div>
            <div class="box-footer p-4 px-6">
                {{ date_format($info->created_at, 'D, M, d, Y h:i A') }}
            </div>
        </div>
    </div>
</div>