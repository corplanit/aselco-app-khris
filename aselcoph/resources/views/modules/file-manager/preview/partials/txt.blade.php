<div class="w-full">
    @if($info && $info->path && file_exists(storage_path('app/public/' . $info->path)))
        @php
            $content = file_get_contents(storage_path('app/public/' . $info->path));
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        @endphp
        
        <div class="bg-gray-50 rounded-lg p-4 border">
            <div class="flex items-center justify-between mb-3 pb-2 border-b">
                <h4 class="font-medium text-gray-700">{{ $info->name }}</h4>
                <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">
                    {{ strtoupper($info->format) }}
                </span>
            </div>
            
            <div class="text-content overflow-auto" style="max-height: 60vh;">
                @if(Str::endsWith($fileToPreview, ['.json']))
                    <pre class="text-sm text-gray-800 whitespace-pre-wrap font-mono bg-white p-3 rounded border"><code>{{ json_encode(json_decode($content), JSON_PRETTY_PRINT) }}</code></pre>
                @elseif(Str::endsWith($fileToPreview, ['.html']))
                    <div class="prose max-w-none">
                        {!! $content !!}
                    </div>
                @elseif(Str::endsWith($fileToPreview, ['.csv']))
                    @php
                        $lines = explode("\n", $content);
                        $headers = str_getcsv(array_shift($lines));
                    @endphp
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    @foreach($headers as $header)
                                        <th class="px-3 py-2 text-left font-medium text-gray-700 border-b">{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($lines, 0, 50) as $line)
                                    @if(trim($line))
                                        @php $row = str_getcsv($line); @endphp
                                        <tr class="border-b hover:bg-gray-50">
                                            @foreach($row as $cell)
                                                <td class="px-3 py-2 text-gray-800">{{ $cell }}</td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        @if(count($lines) > 50)
                            <p class="text-center text-gray-500 mt-3 text-xs">Showing first 50 rows of {{ count($lines) }} total rows</p>
                        @endif
                    </div>
                @else
                    <pre class="text-sm text-gray-800 whitespace-pre-wrap bg-white p-3 rounded border" style="max-height: 50vh; overflow-y: auto;">{{ Str::limit($content, 10000) }}</pre>
                    @if(strlen($content) > 10000)
                        <p class="text-center text-gray-500 mt-2 text-xs">Content truncated. Download file to view complete content.</p>
                    @endif
                @endif
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <i class="bi bi-file-earmark-text text-gray-400 text-4xl mb-3"></i>
            <p class="text-gray-600">Text file could not be loaded</p>
        </div>
    @endif
</div>
