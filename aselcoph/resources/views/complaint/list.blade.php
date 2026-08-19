<div class="p-6 bg-white shadow-md rounded-lg">
    <h2 class="text-xl font-semibold mb-4">Customer Complaints</h2>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-auto">
        <table class="min-w-full text-sm text-left border">
            <thead class="bg-gray-100 text-gray-600">
                <tr>
                    <th class="p-2 border">#</th>
                    <th class="p-2 border">Account Number</th>
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Contact</th>
                    <th class="p-2 border">Complaint</th>
                    <th class="p-2 border">Attachment</th>
                    <th class="p-2 border">Submitted At</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $complaints = App\Models\CustomerComplaint::latest()->get();
                @endphp
                @forelse($complaints as $index => $complaint)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2 border">{{ $index + 1 }}</td>
                        <td class="p-2 border">{{ $complaint->account_number }}</td>
                        <td class="p-2 border">{{ $complaint->name }}</td>
                        <td class="p-2 border">{{ $complaint->contact ?? '—' }}</td>
                        <td class="p-2 border max-w-xs truncate">{{ $complaint->complaint }}</td>
                        <td class="p-2 border">
                            @if ($complaint->attachment)
                                <a href="{{ asset('storage/' . $complaint->attachment) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $complaint->attachment) }}"
                                        class="h-12 w-12 object-cover rounded shadow" />
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="p-2 border">{{ $complaint->created_at->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center p-4 text-gray-500">No complaints
                            found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
