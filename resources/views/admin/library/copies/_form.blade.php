<!-- Create/Edit Copy Modal -->
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="formModalLabel">Add Book Copy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="copyForm">
                    <input type="hidden" id="copyId">
                    
                    <div class="row g-3">
                        <!-- Book Selection -->
                        <div class="col-md-12">
                            <label for="library_item_id" class="form-label">Book <span class="text-danger">*</span></label>
                            <select class="form-select" id="library_item_id" required>
                                <option value="">Select Book</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->title }}
                                        @if($item->isbn) - ISBN: {{ $item->isbn }}@endif
                                        @if($item->edition) ({{ $item->edition }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Barcode -->
                        <div class="col-md-6">
                            <label for="barcode" class="form-label">Barcode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="barcode" maxlength="100" required>
                            <small class="text-muted">Unique identifier for this copy</small>
                        </div>

                        <!-- Call Number -->
                        <div class="col-md-6">
                            <label for="call_number" class="form-label">Call Number</label>
                            <input type="text" class="form-control" id="call_number" maxlength="100">
                            <small class="text-muted">Library classification number</small>
                        </div>

                        <!-- Shelf -->
                        <div class="col-md-6">
                            <label for="shelf_id" class="form-label">Shelf</label>
                            <select class="form-select" id="shelf_id">
                                <option value="">Select Shelf</option>
                                @foreach($shelves as $shelf)
                                    <option value="{{ $shelf->id }}">
                                        {{ $shelf->code ?? $shelf->location }}
                                        @if($shelf->description) - {{ Str::limit($shelf->description, 30) }}@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Acquired Date -->
                        <div class="col-md-6">
                            <label for="acquired_date" class="form-label">Acquired Date</label>
                            <input type="date" class="form-control" id="acquired_date">
                        </div>

                        <!-- Condition -->
                        <div class="col-md-6">
                            <label for="condition" class="form-label">Condition</label>
                            <select class="form-select" id="condition">
                                <option value="">Select Condition</option>
                                <option value="new">New</option>
                                <option value="good">Good</option>
                                <option value="fair">Fair</option>
                                <option value="poor">Poor</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" required>
                                <option value="available">Available</option>
                                <option value="on_loan">On Loan</option>
                                <option value="reserved">Reserved</option>
                                <option value="lost">Lost</option>
                                <option value="damaged">Damaged</option>
                                <option value="withdrawn">Withdrawn</option>
                            </select>
                        </div>

                        <!-- Change Note (for edit mode, shown when status/condition changes) -->
                        <div class="col-md-12" id="changeNoteContainer" style="display: none;">
                            <label for="change_note" class="form-label">Change Note (Optional)</label>
                            <textarea class="form-control" id="change_note" rows="2" maxlength="500" placeholder="Add a note about this status or condition change..."></textarea>
                            <small class="text-muted">Describe the reason for the status/condition change (max 500 characters)</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCopy()">
                    <i class="fas fa-save"></i> Save Copy
                </button>
            </div>
        </div>
    </div>
</div>

