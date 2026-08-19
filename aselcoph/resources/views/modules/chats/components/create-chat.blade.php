{{-- New Chat Modal --}}
@php
    $users = App\Models\User::get();
@endphp
<div id="new-chat-modal" class="fixed inset-0 z-40 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md transform transition-all scale-95 opacity-0"
        data-modal-panel>
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <span
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-blue-50 text-blue-600 text-xs">
                        <i class="bi bi-chat-square-text"></i>
                    </span>
                    Start a new chat
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Choose between a direct message or a group conversation.
                </p>
            </div>
            <button type="button"
                class="inline-flex h-7 w-7 items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600"
                data-new-chat-close>
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-4 pt-3 pb-1">
            {{-- Mode switch --}}
            <div class="flex items-center gap-2 mb-3 text-xs">
                <button type="button"
                    class="new-chat-mode-btn inline-flex items-center gap-1 px-3 py-1.5 rounded-full border border-blue-500 bg-blue-50 text-blue-700 font-medium shadow-sm"
                    data-mode-switch="dm">
                    <i class="bi bi-person"></i>
                    Direct message
                </button>
                <button type="button"
                    class="new-chat-mode-btn inline-flex items-center gap-1 px-3 py-1.5 rounded-full border border-gray-200 text-gray-600 hover:bg-gray-50"
                    data-mode-switch="group">
                    <i class="bi bi-people"></i>
                    Group chat
                </button>
            </div>

            {{-- Form --}}
            <form id="new-chat-form" class="space-y-3">
                @csrf
                <input type="hidden" name="type" id="new-chat-type" value="dm">

                {{-- DM section --}}
                <div data-section="dm">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        Choose user
                    </label>
                    <select name="user_id"
        class="w-full rounded-md border-gray-300 text-xs focus:ring-blue-500 focus:border-blue-500">
    <option value="">Select a user…</option>
    @foreach($users ?? [] as $u)
        @if($u->id !== auth()->id())
            <option value="{{ $u->id }}">
                {{ $u->name }} @if($u->email) ({{ $u->email }}) @endif
            </option>
        @endif
    @endforeach
</select>
                    <p class="text-[11px] text-gray-400 mt-1">
                        Start a private conversation with a single user.
                    </p>
                </div>

                {{-- Group section --}}
                {{-- Group section --}}
                <div data-section="group" class="hidden">
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Group name
                        </label>
                        <input type="text" name="name" id="new-chat-group-name" data-group-only="1"
                            class="w-full rounded-md border-gray-300 text-sm" placeholder="Ex: Support Team, VA Squad">
                    </div>

                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Members
                        </label>
                        <div class="max-h-52 overflow-y-auto border rounded-md p-2 space-y-1 text-sm">
                            @foreach ($users as $user)
                                @if ($user->id !== auth()->id())
                                    <label class="flex items-center gap-2 text-xs">
                                        <input type="checkbox" name="member_ids[]" value="{{ $user->id }}"
                                            data-group-only="1"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="truncate">
                                            {{ $user->name }}
                                            <span class="text-gray-400 text-[11px]">({{ $user->email }})</span>
                                        </span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                        <p class="mt-1 text-[11px] text-gray-500">
                            You will be added automatically as a member.
                        </p>
                    </div>
                </div>



                <p id="new-chat-error" class="hidden text-[11px] text-red-500 mt-1"></p>
            </form>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-2 px-4 py-3 border-t bg-gray-50/80">
            <button type="button"
                class="text-xs px-3 py-1.5 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-100"
                data-new-chat-close>
                Cancel
            </button>
            <button type="submit" form="new-chat-form" id="new-chat-submit"
                class="inline-flex items-center gap-2 text-xs px-4 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700 shadow-sm">
                <span
                    class="new-chat-spinner hidden animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                <span class="new-chat-icon"><i class="bi bi-send"></i></span>
                <span>Create chat</span>
            </button>
        </div>
    </div>
</div>
