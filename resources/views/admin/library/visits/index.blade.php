@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-door-open text-info"></i> Library Visits (Entry/Exit)</h2>
                @if(Auth::user()->hasAnyRole(['admin', 'manager', 'staff']))
                <div>
                    <button class="btn btn-success me-2" onclick="openCheckInModal()">
                        <i class="fas fa-sign-in-alt"></i> Check-in Visitor
                    </button>
                    <button class="btn btn-warning" onclick="openCheckOutModal()">
                        <i class="fas fa-sign-out-alt"></i> Check-out Visitor
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <i class="fas fa-filter"></i> Filters
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" id="filterDateFrom" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" id="filterDateTo" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Session</label>
                    <select id="filterSession" class="form-select form-select-sm">
                        <option value="all">All Sessions</option>
                        <option value="morning">Morning</option>
                        <option value="afternoon">Afternoon</option>
                        <option value="evening">Evening</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Purpose</label>
                    <select id="filterPurpose" class="form-select form-select-sm">
                        <option value="all">All Purposes</option>
                        <option value="read">Read</option>
                        <option value="study">Study</option>
                        <option value="borrow">Borrow</option>
                        <option value="return">Return</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Visitor Type</label>
                    <select id="filterVisitorType" class="form-select form-select-sm">
                        <option value="all">All Types</option>
                        <option value="user">User</option>
                        <option value="guest">Guest</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Currently Inside</label>
                    <select id="filterCurrentlyInside" class="form-select form-select-sm">
                        <option value="all">All</option>
                        <option value="open">Open (Inside)</option>
                        <option value="closed">Closed (Left)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="active" selected>Active Only</option>
                        <option value="all">All</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm w-100" onclick="loadVisits()">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Visits Table Card -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Visits List
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="visitsTable" class="table table-striped table-bordered table-hover" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Visitor</th>
                            <th>Date</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Session</th>
                            <th>Purpose</th>
                            <th>Checked-in by</th>
                            <th>Checked-out by</th>
                            <th>Status</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Check-in Modal -->
<div class="modal fade" id="checkInModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="checkInForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-sign-in-alt"></i> Check-in Visitor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Visitor Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="checkInVisitorType" name="visitor_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="user">User (Student/Teacher/Staff)</option>
                            <option value="guest">Guest</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3" id="checkInUserSelect" style="display:none;">
                        <label class="form-label">Select User <span class="text-danger">*</span></label>
                        <select class="form-select" id="checkInUserId" name="user_id">
                            <option value="">-- Search and select user --</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3" id="checkInGuestSelect" style="display:none;">
                        <label class="form-label">Select Guest <span class="text-danger">*</span></label>
                        <select class="form-select" id="checkInGuestId" name="guest_id">
                            <option value="">-- Search and select guest --</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Session <span class="text-danger">*</span></label>
                        <select class="form-select" name="session" required>
                            <option value="">-- Select Session --</option>
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                            <option value="evening">Evening</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Purpose <span class="text-danger">*</span></label>
                        <select class="form-select" name="purpose" required>
                            <option value="">-- Select Purpose --</option>
                            <option value="read">Read</option>
                            <option value="study">Study</option>
                            <option value="borrow">Borrow Book</option>
                            <option value="return">Return Book</option>
                            <option value="other">Other</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="note" rows="2" placeholder="Optional note..."></textarea>
                    </div>
                    
                    <!-- Quick Start Reading Section -->
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary bg-opacity-10">
                            <h6 class="mb-0 text-primary">
                                <i class="fas fa-book-reader"></i> Quick Start Reading (Optional)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="startReadingNow" name="start_reading_now" value="1">
                                <label class="form-check-label fw-bold" for="startReadingNow">
                                    <i class="fas fa-play-circle text-success"></i> Start Reading Immediately
                                </label>
                                <small class="form-text text-muted d-block mt-1">
                                    ចាប់ផ្តើមអានភ្លាមៗបន្ទាប់ពី check-in (Auto-create reading log)
                                </small>
                            </div>
                            
                            <div id="quickReadingFields" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Select Book/Item <span class="text-danger">*</span></label>
                                    <select class="form-select" id="readingLibraryItemId" name="library_item_id">
                                        <option value="">-- Search by title or ISBN --</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">
                                        ជ្រើសរើសសៀវភៅដែលនឹងអាន (Search book to read)
                                    </small>
                                </div>
                                
                                <div class="mb-0">
                                    <label class="form-label">Copy Barcode (Optional)</label>
                                    <select class="form-select" id="readingCopyId" name="copy_id">
                                        <option value="">-- Select book first --</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        ជម្រើសបន្ថែម: Barcode ច្បាប់ (Optional: Specific copy)
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-sign-in-alt"></i> Check-in
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Check-out Modal -->
<div class="modal fade" id="checkOutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="checkOutForm">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-sign-out-alt"></i> Check-out Visitor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Visitor Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="checkOutVisitorType" name="visitor_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="user">User (Student/Teacher/Staff)</option>
                            <option value="guest">Guest</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3" id="checkOutUserSelect" style="display:none;">
                        <label class="form-label">Select User <span class="text-danger">*</span></label>
                        <select class="form-select" id="checkOutUserId" name="user_id">
                            <option value="">-- Search and select user --</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3" id="checkOutGuestSelect" style="display:none;">
                        <label class="form-label">Select Guest <span class="text-danger">*</span></label>
                        <select class="form-select" id="checkOutGuestId" name="guest_id">
                            <option value="">-- Search and select guest --</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div id="checkOutVisitDetails" class="alert alert-info" style="display:none;">
                        <h6>Open Visit Found:</h6>
                        <div id="checkOutDetailsContent"></div>
                    </div>

                    <div id="checkOutNoVisit" class="alert alert-warning" style="display:none;">
                        <i class="fas fa-exclamation-triangle"></i> No open visit found for this visitor today.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="checkOutSubmitBtn" disabled>
                        <i class="fas fa-sign-out-alt"></i> Check-out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Visit Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalContent">
                <div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editVisitId">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Visit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Session <span class="text-danger">*</span></label>
                        <select class="form-select" name="session" required>
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                            <option value="evening">Evening</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Purpose <span class="text-danger">*</span></label>
                        <select class="form-select" name="purpose" required>
                            <option value="read">Read</option>
                            <option value="study">Study</option>
                            <option value="borrow">Borrow Book</option>
                            <option value="return">Return Book</option>
                            <option value="other">Other</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let visitsTable;
const isAdmin = {{ Auth::user()->hasRole('admin') ? 'true' : 'false' }};
const isManager = {{ Auth::user()->hasRole('manager') ? 'true' : 'false' }};

$(document).ready(function() {
    loadVisits();
    initializeCheckInModal();
    initializeCheckOutModal();
});

// Load DataTable
function loadVisits() {
    if ($.fn.DataTable.isDataTable('#visitsTable')) {
        $('#visitsTable').DataTable().destroy();
    }

    visitsTable = $('#visitsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.library.visits.index') }}',
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.date_from = $('#filterDateFrom').val();
                d.date_to = $('#filterDateTo').val();
                d.session = $('#filterSession').val();
                d.purpose = $('#filterPurpose').val();
                d.visitor_type = $('#filterVisitorType').val();
                d.currently_inside = $('#filterCurrentlyInside').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'visitor', name: 'visitor' },
            { data: 'visit_date', name: 'visit_date' },
            { data: 'check_in_time', name: 'check_in_time' },
            { data: 'check_out_time', name: 'check_out_time' },
            { data: 'session', name: 'session' },
            { data: 'purpose', name: 'purpose' },
            { data: 'checked_in_by', name: 'checked_in_by' },
            { data: 'checked_out_by', name: 'checked_out_by' },
            { data: 'status_badge', name: 'status_badge' },
            { data: 'is_active', name: 'is_active', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[2, 'desc'], [3, 'desc']],
        pageLength: 25,
        responsive: true
    });
}

// Check-in Modal
function openCheckInModal() {
    $('#checkInForm')[0].reset();
    $('#checkInForm .is-invalid').removeClass('is-invalid');
    $('#checkInUserSelect, #checkInGuestSelect').hide();
    $('#checkInModal').modal('show');
    
    setTimeout(() => {
        $('#checkInVisitorType').focus();
    }, 500);
}

function initializeCheckInModal() {
    // Visitor type change
    $('#checkInVisitorType').on('change', function() {
        const type = $(this).val();
        $('#checkInUserSelect, #checkInGuestSelect').hide();
        $('#checkInUserId, #checkInGuestId').val('').prop('required', false);
        
        if (type === 'user') {
            $('#checkInUserSelect').show();
            $('#checkInUserId').prop('required', true);
            initUserSelect2('#checkInUserId');
        } else if (type === 'guest') {
            $('#checkInGuestSelect').show();
            $('#checkInGuestId').prop('required', true);
            initGuestSelect2('#checkInGuestId');
        }
    });
    
    // NEW: Quick Start Reading checkbox toggle
    $('#startReadingNow').on('change', function() {
        const isChecked = $(this).is(':checked');
        
        if (isChecked) {
            // Show reading fields
            $('#quickReadingFields').slideDown();
            $('#readingLibraryItemId').prop('required', true);
            
            // Initialize Select2 for book search
            if (!$('#readingLibraryItemId').hasClass('select2-hidden-accessible')) {
                $('#readingLibraryItemId').select2({
                    dropdownParent: $('#checkInModal'),
                    placeholder: 'Search by title or ISBN...',
                    ajax: {
                        url: '{{ route('admin.library.reading-logs.search-items') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return { q: params.term };
                        },
                        processResults: function(data) {
                            return { results: data.results };
                        }
                    }
                });
            }
        } else {
            // Hide reading fields
            $('#quickReadingFields').slideUp();
            $('#readingLibraryItemId').prop('required', false).val(null).trigger('change');
            $('#readingCopyId').prop('required', false).val(null).trigger('change');
        }
    });
    
    // NEW: When book selected, enable copy search
    $('#readingLibraryItemId').on('change', function() {
        const itemId = $(this).val();
        const $copyField = $('#readingCopyId');
        
        // Clear current value
        $copyField.val(null).trigger('change');
        
        // Destroy existing Select2 if any
        if ($copyField.hasClass('select2-hidden-accessible')) {
            $copyField.select2('destroy');
        }
        
        if (itemId) {
            // Enable and initialize copy search
            $copyField.prop('disabled', false);
            $copyField.select2({
                dropdownParent: $('#checkInModal'),
                placeholder: 'Search by barcode...',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.library.reading-logs.search-copies') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { 
                            q: params.term,
                            item_id: itemId
                        };
                    },
                    processResults: function(data) {
                        return { results: data.results };
                    }
                }
            });
        } else {
            // Disable copy field
            $copyField.prop('disabled', true);
            $copyField.select2({
                dropdownParent: $('#checkInModal'),
                placeholder: 'Select book first...',
                disabled: true
            });
        }
    });
    
    // Form submit
    $('#checkInForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route('admin.library.visits.check-in') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#checkInModal').modal('hide');
                    
                    // Build success message
                    let successHtml = '<strong>' + response.message + '</strong>';
                    
                    // If reading started, show additional info
                    if (response.data && response.data.reading_started) {
                        successHtml += '<hr class="my-2">';
                        successHtml += '<div class="alert alert-success text-start mb-0">';
                        successHtml += '<h6 class="alert-heading"><i class="fas fa-book-reader"></i> Reading Started!</h6>';
                        successHtml += '<p class="mb-1"><strong>Book:</strong> ' + response.data.book_title + '</p>';
                        successHtml += '<p class="mb-0"><small class="text-muted">Reading log created automatically.</small></p>';
                        successHtml += '</div>';
                        successHtml += '<div class="mt-3">';
                        successHtml += '<a href="{{ route('admin.library.reading-logs.index') }}" class="btn btn-sm btn-primary">';
                        successHtml += '<i class="fas fa-eye"></i> View Reading Logs';
                        successHtml += '</a>';
                        successHtml += '</div>';
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'ជោគជ័យ! Check-in Successfully!',
                        html: successHtml,
                        confirmButtonText: 'OK',
                        width: '600px'
                    });
                    loadVisits();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Check if it's a duplicate session error or validation errors
                    if (xhr.responseJSON.errors) {
                        displayValidationErrors('#checkInForm', xhr.responseJSON.errors);
                    } else if (xhr.responseJSON.message) {
                        // Duplicate session or other business logic error
                        $('#checkInModal').modal('hide');
                        
                        let errorHtml = '<div class="text-start">' +
                                       '<p class="mb-3"><strong>' + xhr.responseJSON.message + '</strong></p>';
                        
                        // If existing session data is provided, show it
                        if (xhr.responseJSON.data && xhr.responseJSON.data.existing_session) {
                            const session = xhr.responseJSON.data.existing_session;
                            errorHtml += '<div class="alert alert-info text-start mb-3">' +
                                        '<h6 class="alert-heading"><i class="fas fa-door-open"></i> Open Session Details:</h6>' +
                                        '<hr class="my-2">' +
                                        '<p class="mb-1"><strong>Check-in Time:</strong> ' + session.check_in_time + '</p>' +
                                        '<p class="mb-1"><strong>Session:</strong> ' + session.session + '</p>' +
                                        '<p class="mb-1"><strong>Purpose:</strong> ' + session.purpose + '</p>' +
                                        '<p class="mb-1"><strong>Checked-in by:</strong> ' + session.checked_in_by + '</p>' +
                                        '<p class="mb-0"><strong>Duration:</strong> ' + session.duration + '</p>' +
                                        '</div>';
                        }
                        
                        errorHtml += '<hr>' +
                                    '<p class="mb-1"><i class="fas fa-info-circle text-info"></i> <strong>ហេតុផល (Reason):</strong></p>' +
                                    '<p class="mb-2">អ្នកទស្សនានេះមានវេន (session) ដែលនៅបើកនៅថ្ងៃនេះរួចហើយ។</p>' +
                                    '<p class="mb-0"><i class="fas fa-lightbulb text-warning"></i> <strong>សូមធ្វើការ Check-out ជាមុនសិន!</strong></p>' +
                                    '</div>';
                        
                        Swal.fire({
                            icon: 'warning',
                            title: 'មិនអាចធ្វើការ Check-in បាន!',
                            html: errorHtml,
                            confirmButtonText: 'យល់ព្រម (OK)',
                            confirmButtonColor: '#f39c12',
                            width: '600px',
                            customClass: {
                                htmlContainer: 'text-start'
                            }
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'កំហុស! Error!',
                        text: xhr.responseJSON?.message || 'Check-in failed. Please try again.',
                        confirmButtonText: 'បិទ (Close)'
                    });
                }
            }
        });
    });
}

// Check-out Modal
function openCheckOutModal() {
    $('#checkOutForm')[0].reset();
    $('#checkOutForm .is-invalid').removeClass('is-invalid');
    $('#checkOutUserSelect, #checkOutGuestSelect').hide();
    $('#checkOutVisitDetails, #checkOutNoVisit').hide();
    $('#checkOutSubmitBtn').prop('disabled', true);
    $('#checkOutModal').modal('show');
    
    setTimeout(() => {
        $('#checkOutVisitorType').focus();
    }, 500);
}

function initializeCheckOutModal() {
    // Visitor type change
    $('#checkOutVisitorType').on('change', function() {
        const type = $(this).val();
        $('#checkOutUserSelect, #checkOutGuestSelect').hide();
        $('#checkOutUserId, #checkOutGuestId').val('').prop('required', false);
        $('#checkOutVisitDetails, #checkOutNoVisit').hide();
        $('#checkOutSubmitBtn').prop('disabled', true);
        
        if (type === 'user') {
            $('#checkOutUserSelect').show();
            $('#checkOutUserId').prop('required', true);
            initUserSelect2('#checkOutUserId');
        } else if (type === 'guest') {
            $('#checkOutGuestSelect').show();
            $('#checkOutGuestId').prop('required', true);
            initGuestSelect2('#checkOutGuestId');
        }
    });
    
    // User/Guest selection change - find open visit
    $('#checkOutUserId, #checkOutGuestId').on('change', function() {
        const visitorType = $('#checkOutVisitorType').val();
        const visitorId = $(this).val();
        
        if (!visitorId) {
            $('#checkOutVisitDetails, #checkOutNoVisit').hide();
            $('#checkOutSubmitBtn').prop('disabled', true);
            return;
        }
        
        findOpenVisit(visitorType, visitorId);
    });
    
    // Form submit
    $('#checkOutForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route('admin.library.visits.check-out') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000
                    });
                    $('#checkOutModal').modal('hide');
                    loadVisits();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    displayValidationErrors('#checkOutForm', errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Check-out failed.'
                    });
                }
            }
        });
    });
}

function findOpenVisit(visitorType, visitorId) {
    const data = {
        visitor_type: visitorType,
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    if (visitorType === 'user') {
        data.user_id = visitorId;
    } else {
        data.guest_id = visitorId;
    }
    
    $.ajax({
        url: '{{ route('admin.library.visits.find-open') }}',
        method: 'POST',
        data: data,
        success: function(response) {
            if (response.success) {
                $('#checkOutVisitDetails').show();
                $('#checkOutNoVisit').hide();
                $('#checkOutSubmitBtn').prop('disabled', false);
                
                const visit = response.data;
                $('#checkOutDetailsContent').html(`
                    <p class="mb-1"><strong>Visitor:</strong> ${visit.visitor_name}</p>
                    <p class="mb-1"><strong>Date:</strong> ${visit.visit_date}</p>
                    <p class="mb-1"><strong>Check-in:</strong> ${visit.check_in_time}</p>
                    <p class="mb-1"><strong>Session:</strong> ${visit.session}</p>
                    <p class="mb-1"><strong>Purpose:</strong> ${visit.purpose}</p>
                    <p class="mb-1"><strong>Checked-in by:</strong> ${visit.checked_in_by}</p>
                    <p class="mb-0"><strong>Duration:</strong> ${visit.duration}</p>
                `);
            }
        },
        error: function(xhr) {
            $('#checkOutVisitDetails').hide();
            $('#checkOutNoVisit').show();
            $('#checkOutSubmitBtn').prop('disabled', true);
        }
    });
}

// View Visit
function viewVisit(id) {
    $('#viewModalContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    $('#viewModal').modal('show');
    
    $.get('{{ route('admin.library.visits.show', ':id') }}'.replace(':id', id), function(response) {
        if (response.success) {
            const visit = response.data;
            $('#viewModalContent').html(`
                <table class="table table-bordered">
                    <tr><th width="30%">Visitor Name</th><td>${visit.visitor_name}</td></tr>
                    <tr><th>Visitor Type</th><td><span class="badge bg-${visit.visitor_type === 'user' ? 'primary' : 'info'}">${visit.visitor_type === 'user' ? 'User' : 'Guest'}</span></td></tr>
                    <tr><th>Visit Date</th><td>${visit.visit_date}</td></tr>
                    <tr><th>Check-in Time</th><td>${visit.check_in_time}</td></tr>
                    <tr><th>Check-out Time</th><td>${visit.check_out_time || '<span class="text-muted">Still Inside</span>'}</td></tr>
                    <tr><th>Session</th><td><span class="text-capitalize">${visit.session}</span></td></tr>
                    <tr><th>Purpose</th><td><span class="text-capitalize">${visit.purpose}</span></td></tr>
                    <tr><th>Checked-in by</th><td>${visit.checked_in_by}</td></tr>
                    <tr><th>Checked-out by</th><td>${visit.checked_out_by}</td></tr>
                    <tr><th>Duration</th><td>${visit.duration || 'N/A'}</td></tr>
                    <tr><th>Note</th><td>${visit.note || '<span class="text-muted">N/A</span>'}</td></tr>
                    <tr><th>Status</th><td><span class="badge bg-${visit.is_open ? 'success' : 'secondary'}">${visit.is_open ? 'Open' : 'Closed'}</span></td></tr>
                    <tr><th>Active</th><td><span class="badge bg-${visit.is_active ? 'success' : 'secondary'}">${visit.is_active ? 'Active' : 'Inactive'}</span></td></tr>
                    <tr><th>Created by</th><td>${visit.created_by}</td></tr>
                    <tr><th>Updated by</th><td>${visit.updated_by}</td></tr>
                    <tr><th>Created at</th><td>${visit.created_at}</td></tr>
                    <tr><th>Updated at</th><td>${visit.updated_at}</td></tr>
                </table>
            `);
        }
    }).fail(function() {
        $('#viewModalContent').html('<div class="alert alert-danger">Failed to load visit details.</div>');
    });
}

// Edit Visit
function openEditModal(id) {
    $('#editForm')[0].reset();
    $('#editForm .is-invalid').removeClass('is-invalid');
    $('#editVisitId').val(id);
    
    $.get('{{ route('admin.library.visits.show', ':id') }}'.replace(':id', id), function(response) {
        if (response.success) {
            const visit = response.data;
            $('#editForm select[name="session"]').val(visit.session);
            $('#editForm select[name="purpose"]').val(visit.purpose);
            $('#editForm textarea[name="note"]').val(visit.note);
            $('#editModal').modal('show');
        }
    }).fail(function() {
        Swal.fire('Error', 'Failed to load visit details.', 'error');
    });
}

$('#editForm').on('submit', function(e) {
    e.preventDefault();
    const id = $('#editVisitId').val();
    const formData = $(this).serialize();
    
    $.ajax({
        url: '{{ route('admin.library.visits.update', ':id') }}'.replace(':id', id),
        method: 'PUT',
        data: formData,
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000
                });
                $('#editModal').modal('hide');
                loadVisits();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                displayValidationErrors('#editForm', xhr.responseJSON.errors);
            } else {
                Swal.fire('Error', xhr.responseJSON?.message || 'Update failed.', 'error');
            }
        }
    });
});

// Delete Visit
function deleteVisit(id) {
    if (isAdmin) {
        // Admin: show 3-option modal
        Swal.fire({
            title: 'Delete Visit',
            html: `
                <p>Choose delete action:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-warning" onclick="performSoftDelete(${id})">Soft Delete (Deactivate)</button>
                    <button class="btn btn-danger" onclick="performPermanentDelete(${id})">Permanent Delete</button>
                    <button class="btn btn-secondary" onclick="Swal.close()">Cancel</button>
                </div>
            `,
            showConfirmButton: false,
            showCancelButton: false
        });
    } else if (isManager) {
        // Manager: soft delete only
        Swal.fire({
            title: 'Deactivate Visit?',
            text: 'This will mark the visit as inactive.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, deactivate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performSoftDelete(id);
            }
        });
    }
}

function performSoftDelete(id) {
    $.ajax({
        url: '{{ route('admin.library.visits.destroy', ':id') }}'.replace(':id', id),
        method: 'DELETE',
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                Swal.fire('Deactivated!', response.message, 'success');
                loadVisits();
            }
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Deactivation failed.', 'error');
        }
    });
}

function performPermanentDelete(id) {
    Swal.fire({
        title: 'Permanently Delete?',
        text: 'This action cannot be undone!',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete permanently',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route('admin.library.visits.force-delete', ':id') }}'.replace(':id', id),
                method: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        loadVisits();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Deletion failed.', 'error');
                }
            });
        }
    });
}

// Toggle Active Status
function toggleVisitActive(id) {
    $.ajax({
        url: '{{ route('admin.library.visits.toggle-status', ':id') }}'.replace(':id', id),
        method: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                loadVisits();
            }
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Toggle failed.', 'error');
            loadVisits();
        }
    });
}

// Initialize User Select2
function initUserSelect2(selector) {
    if (typeof $.fn.select2 === 'undefined') {
        console.warn('Select2 not loaded, using basic select');
        return;
    }
    
    $(selector).select2({
        ajax: {
            url: '{{ route('admin.library.loans.search-borrowers') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    type: 'student' // You can make this dynamic if needed
                };
            },
            processResults: function(data) {
                // Data is already in the correct format from the controller
                return {
                    results: data
                };
            },
            cache: true
        },
        placeholder: 'Search by name, code, or sex...',
        minimumInputLength: 1,
        allowClear: true,
        dropdownParent: $(selector).closest('.modal')
    });
}

// Initialize Guest Select2
function initGuestSelect2(selector) {
    if (typeof $.fn.select2 === 'undefined') {
        console.warn('Select2 not loaded, using basic select');
        return;
    }
    
    $(selector).select2({
        ajax: {
            url: '{{ route('admin.library.loans.search-borrowers') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    type: 'guest'
                };
            },
            processResults: function(data) {
                // Data is already in the correct format from the controller
                return {
                    results: data
                };
            },
            cache: true
        },
        placeholder: 'Search by name, phone, or ID card...',
        minimumInputLength: 1,
        allowClear: true,
        dropdownParent: $(selector).closest('.modal')
    });
}

// Display validation errors
function displayValidationErrors(formSelector, errors) {
    $(formSelector + ' .is-invalid').removeClass('is-invalid');
    $(formSelector + ' .invalid-feedback').text('');
    
    $.each(errors, function(field, messages) {
        const input = $(formSelector + ' [name="' + field + '"]');
        input.addClass('is-invalid');
        input.siblings('.invalid-feedback').text(messages[0]);
    });
}
</script>
@endpush

