<div id="popupModal" class="fixed inset-0 z-[1060] invisible opacity-0 transition-all duration-300 ease-in-out">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="hidePopupModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-11/12 max-w-md">
        <div class="bg-white shadow-2xl" style="border: 1px solid #d1d5db; border-radius: 1.5rem;">
            <div class="flex items-center justify-between p-5 border-b border-gray-200">
                <div class="flex items-center">
                    <div id="modalIcon" class="w-9 h-9 rounded-full flex items-center justify-center mr-3">
                        <i id="modalIconClass" class="text-lg"></i>
                    </div>
                    <h3 id="modalTitle" class="text-lg font-semibold text-gray-800"></h3>
                </div>
                <button onclick="hidePopupModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>
            <div class="px-5 py-4">
                <p id="modalMessage" class="text-gray-600 text-sm leading-relaxed"></p>
            </div>
            <div class="flex gap-3 p-5 bg-gray-50" style="border-bottom-left-radius: 1.5rem; border-bottom-right-radius: 1.5rem;">
                <button id="modalCancelBtn" onclick="hidePopupModal()" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button id="modalConfirmBtn" class="flex-1 px-4 py-2.5 text-sm font-medium text-white rounded-xl transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>

<script>
let modalResolve = null;

function showPopupModal(options = {}) {
    const {
        title = 'Notification',
        message = '',
        type = 'info', // success, error, warning, info, confirm
        confirmText = 'OK',
        cancelText = 'Cancel'
    } = options;
    
    let showCancel = options.showCancel || false;
    
    const modal = document.getElementById('popupModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalIcon = document.getElementById('modalIcon');
    const modalIconClass = document.getElementById('modalIconClass');
    const modalConfirmBtn = document.getElementById('modalConfirmBtn');
    const modalCancelBtn = document.getElementById('modalCancelBtn');

    modalTitle.textContent = title;
    modalMessage.textContent = message;
    modalConfirmBtn.textContent = confirmText;
    modalCancelBtn.textContent = cancelText;
    
    switch(type) {
        case 'success':
            modalIcon.className = 'w-9 h-9 rounded-full flex items-center justify-center mr-3 bg-green-100';
            modalIconClass.className = 'bi bi-check-circle text-green-600 text-lg';
            modalConfirmBtn.className = 'flex-1 px-4 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-xl transition-colors';
            break;
        case 'error':
            modalIcon.className = 'w-9 h-9 rounded-full flex items-center justify-center mr-3 bg-red-100';
            modalIconClass.className = 'bi bi-exclamation-circle text-red-600 text-lg';
            modalConfirmBtn.className = 'flex-1 px-4 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors';
            break;
        case 'warning':
            modalIcon.className = 'w-9 h-9 rounded-full flex items-center justify-center mr-3 bg-yellow-100';
            modalIconClass.className = 'bi bi-exclamation-triangle text-yellow-600 text-lg';
            modalConfirmBtn.className = 'flex-1 px-4 py-2.5 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-xl transition-colors';
            break;
        case 'confirm':
            modalIcon.className = 'w-9 h-9 rounded-full flex items-center justify-center mr-3 bg-blue-100';
            modalIconClass.className = 'bi bi-question-circle text-blue-600 text-lg';
            modalConfirmBtn.className = 'flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors';
            break;
        default: // info
            modalIcon.className = 'w-9 h-9 rounded-full flex items-center justify-center mr-3 bg-blue-100';
            modalIconClass.className = 'bi bi-info-circle text-blue-600 text-lg';
            modalConfirmBtn.className = 'flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors';
    }
    
    modalCancelBtn.style.display = showCancel ? 'block' : 'none';
    
    modal.classList.remove('invisible', 'opacity-0');
    document.body.style.overflow = 'hidden';
    
    if (type === 'confirm') {
        return new Promise((resolve) => {
            modalResolve = resolve;
            modalConfirmBtn.onclick = () => {
                hidePopupModal();
                resolve(true);
            };
            modalCancelBtn.onclick = () => {
                hidePopupModal();
                resolve(false);
            };
        });
    } else {
        modalConfirmBtn.onclick = hidePopupModal;
    }
}

function hidePopupModal() {
    const modal = document.getElementById('popupModal');
    modal.classList.add('invisible', 'opacity-0');
    modal.classList.remove('visible', 'opacity-100');
    document.body.style.overflow = 'auto';
    
    if (modalResolve) {
        modalResolve(false);
        modalResolve = null;
    }
}

function showSuccess(message, title = 'Success') {
    showPopupModal({
        type: 'success',
        title: title,
        message: message
    });
}

function showError(message, title = 'Error') {
    showPopupModal({
        type: 'error',
        title: title,
        message: message
    });
}

function showWarning(message, title = 'Warning') {
    showPopupModal({
        type: 'warning',
        title: title,
        message: message
    });
}

function showInfo(message, title = 'Information') {
    showPopupModal({
        type: 'info',
        title: title,
        message: message
    });
}

function showConfirm(message, title = 'Confirm Action') {
    return showPopupModal({
        type: 'confirm',
        title: title,
        message: message,
        confirmText: 'Yes',
        cancelText: 'No',
        showCancel: true
    });
}
</script>
