{{-- Manage Phones Modal --}}
<div class="modal fade" id="phonesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Manage Phone Numbers</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="phonesStudentId">
                
                {{-- Add Phone Form --}}
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-plus"></i> Add New Phone</h6>
                    </div>
                    <div class="card-body">
                        <form id="addPhoneForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="newPhone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" id="newPhone" name="phone" class="form-control" required>
                                    <p id="newPhoneError" class="text-danger small mt-1 d-none"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="newPhoneNote" class="form-label">Note</label>
                                    <input type="text" id="newPhoneNote" name="note" class="form-control">
                                    <p id="newPhoneNoteError" class="text-danger small mt-1 d-none"></p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" id="newPhonePrimary" name="is_primary" class="form-check-input">
                                    <label for="newPhonePrimary" class="form-check-label">Set as primary phone</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Phone Number
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Phones List --}}
                <div>
                    <h6 class="mb-3"><i class="fas fa-list"></i> Existing Phone Numbers</h6>
                    <div id="phonesList" class="list-group">
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-spinner fa-spin"></i> Loading phones...
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Phone Modal --}}
<div class="modal fade" id="editPhoneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Phone Number</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPhoneForm">
                    <input type="hidden" id="editPhoneId">
                    <input type="hidden" id="editPhoneStudentId">
                    
                    <div class="mb-3">
                        <label for="editPhone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" id="editPhone" name="phone" class="form-control" required>
                        <p id="editPhoneError" class="text-danger small mt-1 d-none"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editPhoneNote" class="form-label">Note</label>
                        <input type="text" id="editPhoneNote" name="note" class="form-control">
                        <p id="editPhoneNoteError" class="text-danger small mt-1 d-none"></p>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" id="editPhonePrimary" name="is_primary" class="form-check-input">
                            <label for="editPhonePrimary" class="form-check-label">Set as primary phone</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePhoneEdit()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>




