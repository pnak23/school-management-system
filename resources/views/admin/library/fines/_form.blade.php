<!-- Fine Form Modal (Create/Edit) -->
<div class="modal fade" id="fineModal" tabindex="-1" aria-labelledby="fineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fineModalLabel">Add New Fine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="fineForm">
                    <input type="hidden" id="fineId">

                    <!-- Loan Selection -->
                    <div class="mb-3">
                        <label for="loan_id" class="form-label">Loan <span class="text-danger">*</span></label>
                        <select class="form-select" id="loan_id" required>
                            <option value="">Select Loan...</option>
                            @foreach($loans as $loan)
                                <option value="{{ $loan->id }}">
                                    #{{ $loan->id }} - {{ $loan->copy->item->title ?? 'N/A' }} 
                                    ({{ $loan->borrower_name ?? 'Unknown' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="loan_id_error"></div>
                        <small class="text-muted">Select the loan this fine is related to</small>
                    </div>

                    <!-- User Selection -->
                    <div class="mb-3">
                        <label for="user_id" class="form-label">User (Responsible) <span class="text-danger">*</span></label>
                        <select class="form-select" id="user_id" required>
                            <option value="">Select User...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="user_id_error"></div>
                        <small class="text-muted">User responsible for paying this fine</small>
                    </div>

                    <!-- Fine Type -->
                    <div class="mb-3">
                        <label for="fine_type" class="form-label">Fine Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="fine_type" required>
                            <option value="">Select Type...</option>
                            <option value="overdue">Overdue</option>
                            <option value="lost">Lost Book</option>
                            <option value="damaged">Damaged Book</option>
                            <option value="other">Other</option>
                        </select>
                        <div class="invalid-feedback" id="fine_type_error"></div>
                    </div>

                    <!-- Amount and Paid Amount Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Total Amount (៛) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" step="0.01" min="0" required>
                                <div class="invalid-feedback" id="amount_error"></div>
                                <small class="text-muted">Total fine amount</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paid_amount" class="form-label">Paid Amount (៛)</label>
                                <input type="number" class="form-control" id="paid_amount" step="0.01" min="0" value="0">
                                <div class="invalid-feedback" id="paid_amount_error"></div>
                                <small class="text-muted">Amount already paid</small>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" required>
                            <option value="unpaid">Unpaid</option>
                            <option value="paid">Paid</option>
                            <option value="waived">Waived</option>
                        </select>
                        <div class="invalid-feedback" id="status_error"></div>
                        <small class="text-muted">Note: Partial payments keep status as 'Unpaid' until fully paid</small>
                    </div>

                    <!-- Dates Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="assessed_at" class="form-label">Assessed At</label>
                                <input type="datetime-local" class="form-control" id="assessed_at">
                                <small class="text-muted">When fine was assessed (defaults to now)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paid_at" class="form-label">Paid At</label>
                                <input type="datetime-local" class="form-control" id="paid_at">
                                <small class="text-muted">When fine was paid (if paid)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea class="form-control" id="note" rows="3" maxlength="1000"></textarea>
                        <small class="text-muted">Additional notes (max 1000 characters)</small>
                    </div>

                    <!-- Validation Errors Area -->
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveFine()">
                    <i class="fas fa-save"></i> Save Fine
                </button>
            </div>
        </div>
    </div>
</div>

