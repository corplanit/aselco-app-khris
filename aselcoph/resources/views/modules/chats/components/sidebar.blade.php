<aside class="w-96 bg-white border border-gray-200 flex flex-col" style="border-top:none;border-left:none;border-bottom:none">
    <div class="p-3 pt-5">
        <div class="flex items-center justify-between">
            <div class="font-bold text-xl">Messages</div>
            <div class="flex items-center gap-2">
                <button data-hs-overlay="#newChatModal" class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center gap-1">
                    <i data-lucide="message-square-plus" class="w-4 h-4"></i> New
                </button>
                <button data-hs-overlay="#newGroupModal" class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center gap-1">
                    <i data-lucide="users" class="w-4 h-4"></i> Group
                </button>
            </div>
        </div>

        <div class="relative mt-4">
            <input id="sidebarSearch" type="text" placeholder="Search conversations..." class="ti-form-input rounded-lg ps-11 focus:z-10">
            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-4">
                <i class="bi bi-search"></i>
            </div>
        </div>
    </div>

    <!-- Conversation list -->
    <nav id="convList" class="flex-1 px-4 space-y-2 text-sm overflow-y-auto">
        <section>
            <div class="flex items-center justify-between mb-1">
                <h4 class="text-xs uppercase tracking-wider text-gray-500">Direct Messages</h4>
                <span id="dmCount" class="text-[11px] text-gray-400">0</span>
            </div>
            <div id="dmList" class="space-y-2"></div>
        </section>

        <section class="mt-2">
            <div class="flex items-center justify-between mb-1">
                <h4 class="text-xs uppercase tracking-wider text-gray-500">Groups</h4>
                <span id="groupCount" class="text-[11px] text-gray-400">0</span>
            </div>
            <div id="groupList" class="space-y-2"></div>
        </section>
    </nav>

    @include('modules.chats.hook.sidebar-hook')

</aside>
