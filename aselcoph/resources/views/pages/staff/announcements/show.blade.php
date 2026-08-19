<x-app-layout>
    <x-slot name="title">Announcement</x-slot>
    <x-slot name="url_1">{"link": "/announcements", "text": "Announcements"}</x-slot>
    <x-slot name="url_2">{"link": "{{ route('announcements.show', $announcement) }}", "text": "Detail"}</x-slot>
    <x-slot name="active">#{{ $announcement->id }}</x-slot>
    <x-slot name="buttons">
        <a href="{{ route('announcements.index') }}" class="ti-btn ti-btn-soft-secondary !border-0 btn-wave me-0">
            Back to list
        </a>
        @if (!$announcement->isPublished())
            <form action="{{ route('announcements.publish', $announcement) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="ti-btn ti-btn-primary text-white bg-primary !border-0 btn-wave"
                    onclick="return confirm('Publish and push to audience now?')">
                    Publish &amp; push
                </button>
            </form>
        @endif
    </x-slot>

    <div class="box">
        <div class="box-body">
            @if (session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            <h3 class="font-semibold mb-2">{{ $announcement->title }}</h3>
            <p class="whitespace-pre-wrap mb-4">{{ $announcement->body }}</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                <div class="p-3 border rounded">
                    <div class="text-xs text-muted">Status</div>
                    <div class="font-semibold">{{ ucfirst($announcement->status) }}</div>
                </div>
                <div class="p-3 border rounded">
                    <div class="text-xs text-muted">Category</div>
                    <div class="font-semibold">{{ $announcement->category }}</div>
                </div>
                <div class="p-3 border rounded">
                    <div class="text-xs text-muted">Audience</div>
                    <div class="font-semibold">{{ $announcement->audience_type }}</div>
                    @if ($announcement->audience_type === 'meter')
                        <div class="text-sm mt-1">{{ implode(', ', $announcement->meter_numbers ?? []) }}</div>
                    @endif
                    @if ($announcement->audience_type === 'users')
                        <div class="text-sm mt-1">User IDs: {{ implode(', ', $announcement->audience_user_ids ?? []) }}</div>
                    @endif
                </div>
                <div class="p-3 border rounded">
                    <div class="text-xs text-muted">Resolved / sent</div>
                    <div class="font-semibold">{{ $audienceCount }} audience · {{ $announcement->sent_count }} sent</div>
                    <div class="text-xs text-muted mt-1">
                        Published: {{ $announcement->published_at?->format('M d, Y H:i') ?? '—' }}
                    </div>
                </div>
            </div>

            <h4 class="font-semibold mb-2">Audience sample</h4>
            @if ($preview->isEmpty())
                <p class="text-muted">No members matched this audience.</p>
            @else
                <ul class="mb-0">
                    @foreach ($preview as $user)
                        <li>{{ $user->name }} — {{ $user->email }}</li>
                    @endforeach
                </ul>
                @if ($audienceCount > $preview->count())
                    <p class="text-xs text-muted mt-2">Showing {{ $preview->count() }} of {{ $audienceCount }}.</p>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
