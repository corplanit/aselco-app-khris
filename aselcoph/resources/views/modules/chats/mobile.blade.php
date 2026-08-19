<div class="mobile-chat-wrapper bg-white" style="min-height: 100vh;">
    <div class="sticky top-10 z-10 bg-white px-0 py-4 border-b border-defaultborder dark:border-defaultborder/10">
        <div class="input-group">
            <span class="input-group-text">
                <i class="ri-search-line"></i>
            </span>
            <input type="text" class="form-control" placeholder="Search conversations...">
        </div>
    </div>

    {{-- Action Buttons --}}
    <nav class="flex rtl:space-x-reverse px-4 pb-3 pt-2 border-b border-defaultborder dark:border-defaultborder/10 flex-wrap sm:flex-nowrap gap-2" aria-label="Tabs" role="tablist">
        <button type="button" class="border border-defaultborder dark:border-defaultborder/10 py-2 px-4 inline-flex items-center gap-2 text-sm font-medium text-center text-gray-700 rounded-md hover:bg-gray-50 transition" onclick="openBottomSheet('newConversationModal')">
            <i class="bi bi-plus-circle"></i> New
        </button>
        <button type="button" class="border border-defaultborder dark:border-defaultborder/10 py-2 px-4 inline-flex items-center gap-2 text-sm font-medium text-center text-gray-700 rounded-md hover:bg-gray-50 transition" onclick="openBottomSheet('newGroupModal')">
            <i class="ri-group-line"></i> Group
        </button>
    </nav>

    {{-- Filter Tabs --}}
    <nav class="flex rtl:space-x-reverse px-4 py-3 border-b border-defaultborder dark:border-defaultborder/10 gap-1" aria-label="Filter Tabs" role="tablist">
        <button type="button" 
                class="mobile-filter-tab active flex-1 py-2 px-3 text-sm font-medium text-center rounded-md transition" 
                data-filter="all"
                onclick="switchMobileFilter('all')">
            All
        </button>
        <button type="button" 
                class="mobile-filter-tab flex-1 py-2 px-3 text-sm font-medium text-center rounded-md transition" 
                data-filter="unread"
                onclick="switchMobileFilter('unread')">
            Unread
        </button>
        <button type="button" 
                class="mobile-filter-tab flex-1 py-2 px-3 text-sm font-medium text-center rounded-md transition" 
                data-filter="groups"
                onclick="switchMobileFilter('groups')">
            Groups
        </button>
    </nav>

    <!-- Mobile Conversation List -->
    @include('modules.chats.mobile-conversation-list')
</div>

<style>
/* Filter Tab Styles */
.mobile-filter-tab {
    background-color: transparent;
    color: #6b7280;
    border: none; 
}

.mobile-filter-tab:hover {
    background-color: #f3f4f6;
}

.mobile-filter-tab.active {
    background-color: #2563eb;
    color: white;
}

.mobile-filter-tab.active:hover {
    background-color: #1d4ed8;
}
</style>

<!-- Mobile Conversation View -->
@include('modules.chats.mobile-conversation')

<!-- Sheets -->
@include('modules.chats.bottom-sheets.new-conversation')
@include('modules.chats.bottom-sheets.new-group')
