<!-- Guest Form Modal -->
<div class="modal fade" id="guestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="guestModalLabel">Add New Guest</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="guestForm">
                    <input type="hidden" id="guestId">
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="id_card_no" class="form-label">ID Card No</label>
                        <input type="text" class="form-control" id="id_card_no" name="id_card_no">
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">National ID, Passport, or other identification number</small>
                    </div>

                    <div class="mb-3">
                        <label for="user_id" class="form-label">Linked User</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">None</option>
                        </select>
                        <small class="text-muted">Search for a user by name or email</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveGuest()">
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

