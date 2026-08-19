<div id="newConversationModal" class="bottom-sheet" style="display: none;">
    <div class="bottom-sheet-overlay"></div>
    <div class="bottom-sheet-content">
        <div class="bottom-sheet-header">
            <div class="bottom-sheet-handle"></div>
            <h5 class="font-semibold text-lg mb-0">New Conversation</h5>
            <button type="button" class="bottom-sheet-close" onclick="closeBottomSheet('newConversationModal')">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>
        <div class="bottom-sheet-body">
                <div class="mb-4">
                    <label class="form-label text-sm font-medium">Search Users</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ri-search-line"></i>
                        </span>
                        <input type="text" class="form-control" id="userSearch" placeholder="Search by name or email...">
                    </div>
                </div>
                <div class="user-list" style="max-height: 300px; overflow-y: auto;">
                    <p class="text-xs text-textmuted dark:text-textmuted/50 font-medium uppercase mb-3">AVAILABLE USERS</p>
                    
                    @foreach (App\Models\User::where('role', '<>', 'Sub-Client')->limit(20)->get() as $user)
                        <div class="user-item mb-2 p-3 border border-defaultborder dark:border-defaultborder/10 rounded-md hover:bg-gray-50 cursor-pointer" 
                             data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                        onerror="this.src='/user.png'" 
                                        class="w-10 h-10 rounded-full object-cover"
                                        alt="{{ $user->name }}">
                                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm mb-0 truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-textmuted dark:text-textmuted/50 truncate">{{ $user->email }}</p>
                                </div>
                                <div class="text-primary">
                                    <i class="ri-message-line"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
        </div>
        <div class="bottom-sheet-footer">
            <button type="button" class="btn btn-secondary w-full mb-2" onclick="closeBottomSheet('newConversationModal')">Cancel</button>
            <button type="button" class="btn btn-primary w-full" id="startConversation" disabled>Start Conversation</button>
        </div>
    </div>
</div>

<style>
.bottom-sheet {
    position: fixed;
    inset: 0;
    z-index: 9999;
}

.bottom-sheet-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s ease;
}

.bottom-sheet-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-radius: 20px 20px 0 0;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.3s ease;
}

.bottom-sheet-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}

.bottom-sheet-handle {
    position: absolute;
    top: 8px;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 4px;
    background: #d1d5db;
    border-radius: 2px;
}

.bottom-sheet-close {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    color: #6b7280;
}

.bottom-sheet-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}

.bottom-sheet-footer {
    padding: 16px 20px;
    border-top: 1px solid #e5e7eb;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
</style>

<script>
function openBottomSheet(id) {
    const sheet = document.getElementById(id);
    if (sheet) {
        sheet.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeBottomSheet(id) {
    const sheet = document.getElementById(id);
    if (sheet) {
        sheet.style.display = 'none';
        document.body.style.overflow = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const userItems = document.querySelectorAll('#newConversationModal .user-item');
    const startBtn = document.getElementById('startConversation');
    const searchInput = document.getElementById('userSearch');
    const overlay = document.querySelector('#newConversationModal .bottom-sheet-overlay');
    let selectedUser = null;

    if (overlay) {
        overlay.addEventListener('click', () => closeBottomSheet('newConversationModal'));
    }

    userItems.forEach(item => {
        item.addEventListener('click', function() {
            userItems.forEach(i => i.classList.remove('bg-primary/10', 'border-primary'));
            this.classList.add('bg-primary/10', 'border-primary');
            
            selectedUser = {
                id: this.dataset.userId,
                name: this.dataset.userName
            };
            
            if (startBtn) startBtn.disabled = false;
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            userItems.forEach(item => {
                const userName = item.dataset.userName.toLowerCase();
                const userEmail = item.querySelector('.text-textmuted').textContent.toLowerCase();
                
                item.style.display = (userName.includes(searchTerm) || userEmail.includes(searchTerm)) ? 'block' : 'none';
            });
        });
    }

    if (startBtn) {
        startBtn.addEventListener('click', function() {
            if (selectedUser) {
                window.location.href = `/chat/${selectedUser.id}`;
            }
        });
    }
});
</script>
