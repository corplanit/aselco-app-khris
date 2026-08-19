@php
    use Illuminate\Support\Str;

    $totalConversations = $conversations->count();
    $totalGroups = $conversations->where('is_group', true)->count();
    $totalDMs = $conversations->where('is_group', false)->count();
    $totalMessages = App\Models\Chats\Message::count();
    $totalUnread = App\Models\Chats\ConversationParticipant::whereNot('unread_count', null)->sum('unread_count');
@endphp

<x-app-layout>
    <x-slot name="return">{"link": "/chats", "text": "Chats"}</x-slot>
    <x-slot name="title">Chat Room Monitor</x-slot>
    <x-slot name="url_1">{"link": "/chats", "text": "Chats"}</x-slot>
    <x-slot name="active">Messages</x-slot>
    <x-slot name="buttons"></x-slot>

    <div class="space-y-6">

        {{-- STATS --}}
        <div class="grid gap-4 md:grid-cols-4 lg:grid-cols-4">
            {{-- Total Conversations --}}
            <div class="bg-white border rounded-xl p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Total Conversations</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $totalConversations }}</p>
                    <p class="text-xs text-slate-400 mt-1">All GC & Direct Messages</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center shadow-inner">
                    <i class="bi bi-collection text-slate-700 text-lg"></i>
                </div>
            </div>

            {{-- Total Messages --}}
            <div class="bg-white border rounded-xl p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Total Messages</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($totalMessages, 0) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Sum of all messages in these conversations</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-indigo-50 flex items-center justify-center shadow-inner">
                    <i class="bi bi-chat-text text-indigo-600 text-lg"></i>
                </div>
            </div>

            {{-- Group Chats --}}
            <div class="bg-white border rounded-xl p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Group Chats</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $totalGroups }}</p>
                    <p class="text-xs text-slate-400 mt-1">Conversations with multiple members</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-emerald-50 flex items-center justify-center shadow-inner">
                    <i class="bi bi-people text-emerald-600 text-lg"></i>
                </div>
            </div>

            {{-- Direct Messages / Unread --}}
            <div class="bg-white border rounded-xl p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Direct Messages</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $totalDMs }}</p>
                    <p class="text-xs text-slate-400 mt-1">
                        Unread: <span class="font-semibold text-rose-600">{{ $totalUnread }}</span>
                    </p>
                </div>
                <div class="h-10 w-10 rounded-full bg-rose-50 flex items-center justify-center shadow-inner">
                    <i class="bi bi-person-lines-fill text-rose-600 text-lg"></i>
                </div>
            </div>
        </div>

        {{-- FILTER / SEARCH BAR --}}
        <div
            class="bg-white border rounded-xl shadow-sm px-4 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <i class="bi bi-funnel me-1 text-slate-400"></i>
                <span class="font-medium">Conversations</span>
                <span class="text-xs text-slate-400">Monitor GC/DM usage and activity</span>
            </div>

            <div class="flex items-center gap-2">
                {{-- Type filter --}}
                <select id="filter-type"
                    class="ti-form-select border-slate-300 text-xs rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all">All types</option>
                    <option value="group">Group Chats</option>
                    <option value="dm">Direct Messages</option>
                </select>

                {{-- Search --}}
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                        <i class="bi bi-search text-slate-400 text-xs"></i>
                    </span>
                    <input id="conversation-search" type="text" placeholder="Search title or last message..."
                        class="pl-7 pr-3 py-1.5 text-xs border border-slate-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 w-40 md:w-64" />
                </div>

                {{-- New Group Chat --}}
                <button type="button" onclick="openCreateGroupChat()"
                    class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    <i class="bi bi-people-fill text-[11px]"></i>
                    <span>New</span>
                </button>

                {{-- More moderator actions (dropdown) --}}
                <div class="relative hidden md:block" x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
                        <i class="bi bi-three-dots-vertical text-[11px]"></i>
                        <span>More</span>
                    </button>
                    <div x-cloak x-show="open" @click.outside="open = false"
                        class="absolute right-0 mt-1 w-44 rounded-md border border-slate-200 bg-white shadow-lg z-20 text-xs">
                        <button type="button" onclick="filterHotConversations()"
                            class="w-full text-left px-3 py-2 hover:bg-slate-50 flex items-center gap-2">
                            <i class="bi bi-fire text-amber-500"></i>
                            <span>Show with unread</span>
                        </button>
                        <button type="button" onclick="exportConversationsCsv()"
                            class="w-full text-left px-3 py-2 hover:bg-slate-50 flex items-center gap-2">
                            <i class="bi bi-file-earmark-spreadsheet"></i>
                            <span>Export CSV</span>
                        </button>
                        {{-- add more moderator tools here --}}
                    </div>
                </div>
            </div>
        </div>


        {{-- TABLE --}}
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm" id="conversations-table">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Title
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Participants
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Last Message
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Last Activity
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider text-right">
                                Messages
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider text-right">
                                Unread
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider text-right">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($conversations as $conv)
                            @php
                                $isGroup = $conv->is_group;
                                $lastAt = $conv->last_message_at ?? $conv->updated_at;
                                $participantsCount = $conv->participants_count ?? $conv->participants->count();
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors conversation-row"
                                data-type="{{ $isGroup ? 'group' : 'dm' }}">
                                {{-- Type --}}
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {{ $isGroup ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700' }}">
                                        <i class="bi {{ $isGroup ? 'bi-people-fill' : 'bi-person' }} text-xs"></i>
                                        {{ $isGroup ? 'GROUP' : 'DM' }}
                                    </span>
                                </td>

                                {{-- Title --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        {{-- Simple avatar --}}
                                        <div
                                            class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-semibold text-slate-700">
                                            {{ Str::upper(Str::substr($conv->display_title, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-800 text-sm">
                                                {{ $conv->display_title }}
                                            </div>
                                            <div class="text-xs text-slate-400">
                                                ID: {{ $conv->id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Participants --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1">
                                        <span class="inline-flex -space-x-1">
                                            @foreach ($conv->participants->take(3) as $p)
                                                @php
                                                    $name = $p->user->name ?? 'User';
                                                @endphp
                                                <div
                                                    class="h-6 w-6 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-[10px] font-semibold text-slate-700">
                                                    {{ Str::upper(Str::substr($name, 0, 2)) }}
                                                </div>
                                            @endforeach
                                        </span>
                                        <span class="text-xs text-slate-500">
                                            {{ $participantsCount }} member{{ $participantsCount === 1 ? '' : 's' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Last Message --}}
                                <td class="px-4 py-3 max-w-md">
                                    <div class="text-xs text-slate-600 line-clamp-2">
                                        {{ $conv->last_message_body ?? 'No messages yet' }}
                                    </div>
                                </td>

                                {{-- Last Activity --}}
                                <td class="px-4 py-3">
                                    <div class="text-xs text-slate-600">
                                        {{ optional($lastAt)->diffForHumans() ?? 'N/A' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ optional($lastAt)->format('Y-m-d H:i') ?? '' }}
                                    </div>
                                </td>

                                {{-- Messages count --}}
                                <td class="px-4 py-3 text-right">
                                    <span class="text-xs font-medium text-slate-700">
                                        {{ $conv->messages_count ?? 0 }}
                                    </span>
                                </td>

                                {{-- Unread --}}
                                <td class="px-4 py-3 text-right">
                                    @if (($conv->total_unread ?? 0) > 0)
                                        <span
                                            class="inline-flex items-center justify-center rounded-full bg-rose-100 text-rose-700 text-xs font-semibold px-2 py-0.5">
                                            {{ $conv->total_unread }}
                                        </span>
                                    @else
                                        <span class="text-[11px] text-slate-400">0</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- View Conversation (open existing chat UI, adjust route as needed) --}}
                                        {{-- {{ route('chats.index', ['conversation' => $conv->id]) }} --}}
                                        <a href="{{ route('conversations.show', $conv->id) }}"
                                            class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-2.5 py-3 text-xs text-slate-700 hover:bg-slate-100 hover:border-slate-300 transition"
                                            title="View conversation">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        {{-- Disable / enable conversation (soft lock) --}}
                                        {{-- {{ route('conversations.toggle-status', $conv->id) }} --}}
                                        <form method="POST"
                                            action=""
                                            class="inline-flex"
                                            onsubmit="return confirm('Are you sure you want to change the status of this conversation?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="inline-flex items-center justify-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-3 text-xs text-amber-700 hover:bg-amber-100 hover:border-amber-300 transition"
                                                title="Disable / enable">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        </form>

                                        {{-- Delete conversation (hard delete or archive) --}}
                                        {{-- {{ route('conversations.destroy', $conv->id) }} --}}
                                        <form method="POST" action="" 
                                            class="inline-flex"
                                            onsubmit="return confirm('Permanently delete this conversation and its messages?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center justify-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-3 text-xs text-rose-700 hover:bg-rose-100 hover:border-rose-300 transition"
                                                title="Delete conversation">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-400">
                                    No conversations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>
{{--  window.location.href = "{{ route('chats.groups.create') }}";  --}}
    <script>
        
        function openCreateGroupChat() {
            // Redirect to your group chat creation page
           // change route as needed
        }

        function filterHotConversations() {
            // Example: show only conversations with unread > 0
            const rows = document.querySelectorAll('.conversation-row');
            rows.forEach(row => {
                const unreadBadge = row.querySelector('td:nth-child(7) span');
                const unread = unreadBadge ? parseInt(unreadBadge.textContent.trim()) || 0 : 0;
                row.classList.toggle('hidden', unread === 0);
            });
        }

        function exportConversationsCsv() {
            // stub: point to a route that returns CSV
           
        }
    </script>
{{--  window.location.href = "{{ route('conversations.export') }}"; // define in routes if you want --}}

    {{-- SIMPLE FILTER SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const typeFilter = document.getElementById('filter-type');
            const searchInput = document.getElementById('conversation-search');
            const rows = Array.from(document.querySelectorAll('.conversation-row'));

            function applyFilters() {
                const type = typeFilter.value;
                const search = (searchInput.value || '').toLowerCase();

                rows.forEach(row => {
                    const rowType = row.getAttribute('data-type');
                    const text = row.innerText.toLowerCase();

                    const matchType = (type === 'all') || (type === rowType);
                    const matchSearch = !search || text.includes(search);

                    if (matchType && matchSearch) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            }

            typeFilter.addEventListener('change', applyFilters);
            searchInput.addEventListener('input', applyFilters);
        });
    </script>
</x-app-layout>
