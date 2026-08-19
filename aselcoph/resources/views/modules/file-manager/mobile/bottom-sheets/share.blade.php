<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="translate-y-full transition-transform duration-300 ease-in-out absolute bottom-16 left-0 w-full bg-white rounded-t-3xl max-h-[70vh] overflow-y-auto">
            <div class="modal-header">
                <h5 class="modal-title">Share File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                    <label class="form-label">Share Link</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="shareLink" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyShareLink()">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Share via Email</label>
                    <input type="email" class="form-control" id="shareEmail" placeholder="Enter email address">
                </div>
                <div class="mb-3">
                    <label class="form-label">Access Level</label>
                    <select class="form-select" id="shareAccess">
                        <option value="view">View Only</option>
                        <option value="download">View & Download</option>
                        <option value="edit">Edit Access</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendShareEmail()">Share</button>
            </div>
        </div>
    </div>
</div>