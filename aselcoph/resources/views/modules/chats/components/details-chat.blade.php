<!-- Conversation Info Modal -->
<div id="conversation-info-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div data-modal-panel
        class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 transform transition-all duration-150 scale-95 opacity-0">
        {{-- Header --}}
        <div class="flex items-start justify-between px-4 py-3 border-b bg-gray-50/60 rounded-t-2xl">
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <h3 id="info-modal-title" class="text-sm font-semibold text-gray-800">
                        Conversation details
                    </h3>
                    <span id="info-type-badge"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                        <!-- Filled by JS -->
                    </span>
                </div>
                <p id="info-modal-subtitle" class="text-xs text-gray-500 mt-0.5"></p>
                <div class="mt-1 flex items-center gap-2 text-[11px] text-gray-400">
                    <span id="info-created-at" class="inline-flex items-center gap-1">
                        <i class="bi bi-clock-history"></i>
                        <span><!-- Started… --></span>
                    </span>
                    <span class="h-3 w-px bg-gray-200"></span>
                    <span id="info-your-role"><!-- You are… --></span>
                </div>
            </div>
            <button type="button" data-info-close
                class="h-7 w-7 inline-flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400">
                <i class="bi bi-x-lg text-[11px]"></i>
            </button>
        </div>

        <div class="px-4 pt-3 pb-4 space-y-4">
            {{-- DM details --}}
            <div id="info-dm-section" class="space-y-3 hidden">
                <div class="flex items-center gap-3">
                    <div id="info-dm-avatar"
                        class="h-11 w-11 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-xs font-semibold text-gray-700 shadow-sm">
                        ?
                    </div>
                    <div class="min-w-0">
                        <div id="info-dm-name" class="text-sm font-semibold text-gray-800 truncate">
                            User name
                        </div>
                        <div id="info-dm-email" class="text-xs text-gray-500 truncate">
                            Email
                        </div>
                    </div>
                </div>

                <div class="mt-2 grid grid-cols-2 gap-3 text-[11px] text-gray-500">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-50 text-emerald-500">
                            <i class="bi bi-person"></i>
                        </span>
                        <div>
                            <div class="font-medium text-gray-700">Participants</div>
                            <div class="text-[11px] text-gray-500">You and this user</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-50 text-indigo-500">
                            <i class="bi bi-chat-dots"></i>
                        </span>
                        <div>
                            <div class="font-medium text-gray-700">Type</div>
                            <div class="text-[11px] text-gray-500">Direct message</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Group details --}}
            <div id="info-group-section" class="space-y-4 hidden">
                {{-- Group name / rename --}}
                <div>
                    <label class="text-[11px] font-medium text-gray-500 uppercase">Group name</label>
                    <div class="mt-1 flex gap-2">
                        <input id="info-group-name" type="text"
                            class="flex-1 rounded-md border-gray-200 text-sm focus:ring-0 focus:border-blue-400"
                            placeholder="Group name">
                        <button type="button" id="info-group-save-btn"
                            class="px-2.5 py-1.5 text-xs font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-60">
                            Save
                        </button>
                    </div>
                    <p id="info-group-name-helper" class="mt-1 text-[11px] text-gray-400">
                        Only the group creator can rename this chat.
                    </p>
                </div>

                {{-- Members list --}}
                <div class="pt-2 border-t border-gray-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-medium text-gray-500 uppercase">Members</span>
                        <div class="flex items-center gap-2 text-[11px] text-gray-400">
                            <span id="info-member-count">0 members</span>
                        </div>
                    </div>

                    <ul id="info-members-list"
                        class="space-y-1 max-h-44 overflow-y-auto text-sm rounded-md bg-gray-50/60 p-2">
                        {{-- Filled by JS --}}
                    </ul>

                    <div id="group-add-member-wrapper" class="pt-2">
                        <form id="group-add-member-form" class="flex gap-2">
                            <input type="email" id="group-add-member-email"
                                class="flex-1 rounded-md border-gray-200 text-xs focus:ring-0 focus:border-blue-400"
                                placeholder="Add member by email">
                            <button type="submit"
                                class="px-2.5 py-1.5 text-xs font-medium rounded-md bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-60">
                                Add
                            </button>
                        </form>
                        <p class="mt-1 text-[11px] text-gray-400">
                            We&#39;ll add an existing user with this email to the group.
                        </p>
                    </div>
                </div>

                {{-- Danger zone --}}
                <div class="pt-3 border-t border-red-50">
                    <button type="button" id="info-group-delete-btn"
                        class="inline-flex items-center gap-1 text-[11px] font-medium text-red-600 hover:text-red-700">
                        <span
                            class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-50 text-red-500">
                            <i class="bi bi-trash3"></i>
                        </span>
                        Delete this group chat
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
