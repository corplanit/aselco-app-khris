<div class="grid grid-cols-12 gap-x-6">
    <div class="xxl:col-span-12 col-span-12">
        <div class="box">
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
            <div class="box-body overflow-y-auto border-1">
                <div class="file-tree-container border-2 p-5">
                    @php
                       $filePath =  asset('storage') . '/' . $info->path;
                    @endphp
                    <iframe src="https://view.officeapps.live.com/op/view.aspx?src={{ urlencode( $filePath) }}" width="100%"
                    height="900px"></iframe>
                </div>
            </div>
            <div class="box-footer p-4 px-6">
                {{ date_format($info->created_at, 'D, M, d, Y h:i A') }} | 
                <strong class="text-gray-800">📄 Name:</strong> {{ $info->name }} |
                <strong class="text-gray-800">📦 Size:</strong> {{ formatSize($info->size) }}
            </div>
        </div>
    </div>
</div>