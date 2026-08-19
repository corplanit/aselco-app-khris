<x-app-layout>
    <x-slot name="title">Mobile Announcements</x-slot>
    <x-slot name="url_1">{"link": "/announcements", "text": "Push"}</x-slot>
    <x-slot name="url_2">{"link": "/announcements", "text": "Announcements"}</x-slot>
    <x-slot name="active">List</x-slot>
    <x-slot name="buttons">
        <a href="{{ route('announcements.create') }}"
            class="ti-btn ti-btn-primary text-white bg-primary !border-0 btn-wave me-0">
            <i class="bi bi-megaphone me-1"></i>New announcement
        </a>
    </x-slot>

    <div class="box">
        <div class="box-body">
            <i class="bi bi-info-circle px-1"></i>
            Send billing, service, or alert announcements to the mobile app (in-app inbox + push).
            <hr class="mb-3 mt-3">

            @if (session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-sm table-bordered min-w-full">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Title</th>
                            <th>Audience</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Sent</th>
                            <th>Created</th>
                            <th style="width:140px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $item)
                            <tr>
                                <td>{{ $announcements->firstItem() + $loop->index }}</td>
                                <td>
                                    <a href="{{ route('announcements.show', $item) }}" class="font-semibold text-primary">
                                        {{ $item->title }}
                                    </a>
                                    <div class="text-xs text-muted">{{ \Illuminate\Support\Str::limit($item->body, 80) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary/10 text-primary">{{ $item->audience_type }}</span>
                                    @if ($item->audience_type === 'meter' && is_array($item->meter_numbers))
                                        <div class="text-xs mt-1">{{ implode(', ', $item->meter_numbers) }}</div>
                                    @endif
                                </td>
                                <td>{{ $item->category }}</td>
                                <td>
                                    @if ($item->status === 'published')
                                        <span class="badge bg-success/10 text-success">Published</span>
                                    @else
                                        <span class="badge bg-warning/10 text-warning">Draft</span>
                                    @endif
                                </td>
                                <td>{{ $item->sent_count }}</td>
                                <td>
                                    <div>{{ $item->created_at?->format('M d, Y H:i') }}</div>
                                    <div class="text-xs text-muted">{{ $item->creator?->name ?? '—' }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('announcements.show', $item) }}" class="ti-btn ti-btn-sm ti-btn-soft-primary">View</a>
                                    @if ($item->status !== 'published')
                                        <form action="{{ route('announcements.publish', $item) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="ti-btn ti-btn-sm ti-btn-soft-success"
                                                onclick="return confirm('Publish and push to audience now?')">
                                                Publish
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No announcements yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $announcements->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
