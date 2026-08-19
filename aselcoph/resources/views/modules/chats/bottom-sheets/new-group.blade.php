<!-- New Group Bottom Sheet -->
<div id="newGroupModal" class="bottom-sheet" style="display: none;">
    <div class="bottom-sheet-overlay"></div>
    <div class="bottom-sheet-content">
        <div class="bottom-sheet-header">
            <div class="bottom-sheet-handle"></div>
            <h5 class="font-semibold text-lg mb-0">Create New Group</h5>
            <button type="button" class="bottom-sheet-close" onclick="closeBottomSheet('newGroupModal')">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>
        <div class="bottom-sheet-body">
                <!-- Group Name -->
                <div class="mb-4">
                    <label class="form-label text-sm font-medium">Group Name</label>
                    <input type="text" class="form-control" id="groupName" placeholder="Enter group name..." maxlength="50">
                    <div class="text-xs text-textmuted dark:text-textmuted/50 mt-1">Maximum 50 characters</div>
                </div>

                <!-- Group Description -->
                <div class="mb-4">
                    <label class="form-label text-sm font-medium">Description (Optional)</label>
                    <textarea class="form-control" id="groupDescription" rows="3" placeholder="Enter group description..." maxlength="200"></textarea>
                    <div class="text-xs text-textmuted dark:text-textmuted/50 mt-1">Maximum 200 characters</div>
                </div>

                <!-- Add Members -->
                <div class="mb-4">
                    <label class="form-label text-sm font-medium">Add Members</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text">
                            <i class="ri-search-line"></i>
                        </span>
                        <input type="text" class="form-control" id="memberSearch" placeholder="Search users to add...">
                    </div>

                    <!-- Selected Members -->
                    <div id="selectedMembers" class="mb-3" style="display: none;">
                        <p class="text-xs text-textmuted dark:text-textmuted/50 font-medium uppercase mb-2">SELECTED MEMBERS</p>
                        <div id="selectedMembersList" class="flex flex-wrap gap-2"></div>
                    </div>

                    <!-- Available Users -->
                    <div class="available-users" style="max-height: 200px; overflow-y: auto;">
                        <p class="text-xs text-textmuted dark:text-textmuted/50 font-medium uppercase mb-3">AVAILABLE USERS</p>
                        
                        @foreach (App\Models\User::where('role', '<>', 'Sub-Client')->limit(15)->get() as $user)
                            <div class="member-item mb-2 p-2 border border-defaultborder dark:border-defaultborder/10 rounded-md hover:bg-gray-50 cursor-pointer" 
                                 data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                            onerror="this.src='/user.png'" 
                                            class="w-8 h-8 rounded-full object-cover"
                                            alt="{{ $user->name }}">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-sm mb-0 truncate">{{ $user->name }}</p>
                                        <p class="text-xs text-textmuted dark:text-textmuted/50 truncate">{{ $user->email }}</p>
                                    </div>
                                    <div class="member-checkbox">
                                        <input type="checkbox" class="form-check-input" value="{{ $user->id }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
        </div>
        <div class="bottom-sheet-footer">
            <button type="button" class="btn btn-secondary w-full mb-2" onclick="closeBottomSheet('newGroupModal')">Cancel</button>
            <button type="button" class="btn btn-primary w-full" id="createGroup" disabled>Create Group</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const groupNameInput = document.getElementById('groupName');
    const createBtn = document.getElementById('createGroup');
    const memberItems = document.querySelectorAll('#newGroupModal .member-item');
    const memberSearch = document.getElementById('memberSearch');
    const selectedMembersDiv = document.getElementById('selectedMembers');
    const selectedMembersList = document.getElementById('selectedMembersList');
    const overlay = document.querySelector('#newGroupModal .bottom-sheet-overlay');
    let selectedMembers = [];

    // Close on overlay click
    if (overlay) {
        overlay.addEventListener('click', () => closeBottomSheet('newGroupModal'));
    }

    // Group name validation
    groupNameInput.addEventListener('input', function() {
        validateForm();
    });

    // Member selection
    memberItems.forEach(item => {
        const checkbox = item.querySelector('.form-check-input');
        
        item.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                checkbox.checked = !checkbox.checked;
            }
            
            const userId = checkbox.value;
            const userName = item.dataset.userName;
            const userEmail = item.dataset.userEmail;
            
            if (checkbox.checked) {
                // Add to selected members
                if (!selectedMembers.find(m => m.id === userId)) {
                    selectedMembers.push({
                        id: userId,
                        name: userName,
                        email: userEmail
                    });
                }
                item.classList.add('bg-primary/10', 'border-primary');
            } else {
                // Remove from selected members
                selectedMembers = selectedMembers.filter(m => m.id !== userId);
                item.classList.remove('bg-primary/10', 'border-primary');
            }
            
            updateSelectedMembersList();
            validateForm();
        });
    });

    // Search members
    memberSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        memberItems.forEach(item => {
            const userName = item.dataset.userName.toLowerCase();
            const userEmail = item.dataset.userEmail.toLowerCase();
            
            if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    function updateSelectedMembersList() {
        if (selectedMembers.length > 0) {
            selectedMembersDiv.style.display = 'block';
            selectedMembersList.innerHTML = selectedMembers.map(member => `
                <span class="badge bg-primary/10 text-primary px-2 py-1 rounded-full text-xs">
                    ${member.name}
                    <button type="button" class="ml-1 text-primary hover:text-red-500" onclick="removeMember('${member.id}')">
                        <i class="ri-close-line"></i>
                    </button>
                </span>
            `).join('');
        } else {
            selectedMembersDiv.style.display = 'none';
        }
    }

    function validateForm() {
        const hasGroupName = groupNameInput.value.trim().length > 0;
        const hasMembers = selectedMembers.length > 0;
        
        createBtn.disabled = !(hasGroupName && hasMembers);
    }

    // Create group
    if (createBtn) {
        createBtn.addEventListener('click', function() {
            const groupName = groupNameInput.value.trim();
            const groupDescription = document.getElementById('groupDescription').value.trim();
            
            if (groupName && selectedMembers.length > 0) {
                console.log('Creating group:', {
                    name: groupName,
                    description: groupDescription,
                    members: selectedMembers
                });
                
                closeBottomSheet('newGroupModal');
                alert('Group created successfully!');
            }
        });
    }

    // Global function to remove member (called from onclick)
    window.removeMember = function(userId) {
        selectedMembers = selectedMembers.filter(m => m.id !== userId);
        
        // Uncheck the corresponding checkbox
        const checkbox = document.querySelector(`input[value="${userId}"]`);
        if (checkbox) {
            checkbox.checked = false;
            checkbox.closest('.member-item').classList.remove('bg-primary/10', 'border-primary');
        }
        
        updateSelectedMembersList();
        validateForm();
    };
});
</script>
