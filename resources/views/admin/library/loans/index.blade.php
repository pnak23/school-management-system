@extends('layouts.app')

@section('title', 'Library Loans (Borrow/Return)')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-book-reader"></i> Library Loans (Borrow/Return)</h2>
        </div>
        <div class="col-md-6 text-end">
            @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
            <!-- Scanner Mode Toggle -->
            <div class="form-check form-switch d-inline-block me-3 align-middle">
                <input class="form-check-input" type="checkbox" id="scannerModeToggle" role="switch">
                <label class="form-check-label" for="scannerModeToggle">
                    <i class="fas fa-barcode"></i> Scanner Mode
                </label>
            </div>
            <!-- Loan Request Notifications -->
            <div class="btn-group me-2" role="group">
                <button type="button" class="btn btn-warning position-relative" onclick="showPendingRequests()" title="View Pending Loan Requests">
                    <i class="fas fa-bell"></i> Requests
                    <span id="pendingRequestsBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                        0
                    </span>
                </button>
                <button type="button" class="btn btn-danger position-relative" onclick="showOverdueLoans()" title="View Overdue Loans">
                    <i class="fas fa-exclamation-triangle"></i> Overdue
                    <span id="overdueLoansBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="display: none;">
                        0
                    </span>
                </button>
            </div>
            <!-- Send Notifications Button -->
            <button type="button" class="btn btn-info me-2" onclick="sendNotifications()" title="Check & Send Loan Notifications Now">
                <i class="fas fa-bell"></i> Send Alerts
            </button>
            <button type="button" class="btn btn-primary" onclick="openBorrowModal()">
                <i class="fas fa-plus"></i> Borrow Book
            </button>
            @endif
        </div>
    </div>

    <!-- Scanner Mode Help Text -->
    @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
    <div class="row mb-2">
        <div class="col-md-12">
            <div class="alert alert-info py-2" id="scannerModeHelp" style="display: none;">
                <small>
                    <i class="fas fa-info-circle"></i> 
                    <strong>Scanner Mode Active:</strong> Barcode input will autofocus and auto-lookup when scan completes. 
                    Focus will move to borrower search after successful lookup.
                </small>
            </div>
        </div>
    </div>
    @endif

    <!-- Dashboard Statistics Cards -->
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-arrow-right"></i> Borrowed Today
                            </h6>
                            <h2 class="mb-0 mt-2" id="statBorrowedToday">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-book-open fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-check-circle"></i> Returned Today
                            </h6>
                            <h2 class="mb-0 mt-2" id="statReturnedToday">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-book fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-book-reader"></i> Currently Borrowed
                            </h6>
                            <h2 class="mb-0 mt-2" id="statBorrowedActive">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-books fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-exclamation-triangle"></i> Overdue Now
                            </h6>
                            <h2 class="mb-0 mt-2" id="statOverdueActive">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <small class="d-block mt-2">
                        <button type="button" class="btn btn-sm btn-light" onclick="fetchLoanStats()" title="Refresh Stats">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Trends Chart Section (Include from partial) -->
    @include('admin.library.loans._chart_loan')

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Barcode, Title, Borrower...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="requested">Requested (Pending)</option>
                        <option value="borrowed" selected>Borrowed</option>
                        <option value="returned">Returned</option>
                        <option value="overdue">Overdue</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Borrower Type</label>
                    <select id="borrowerTypeFilter" class="form-select">
                        <option value="all">All Types</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="staff">Staff</option>
                        <option value="guest">Guest</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" id="dateFromFilter" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" id="dateToFilter" class="form-control">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-secondary w-100" onclick="clearFilters()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTable -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="loansTable" class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Barcode</th>
                            <th>Book Title</th>
                            <th>Borrower</th>
                            <th>Borrowed</th>
                            <th>Due Date</th>
                            <th>Returned</th>
                            <th>Status</th>
                            <th>Processed By</th>
                            <th>Received By</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('admin.library.loans._borrow_form')
@include('admin.library.loans._return_modal')

<!-- Edit Loan Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Loan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editLoanId">
                    
                    <div class="mb-3">
                        <label for="editDueDate" class="form-label">Due Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="editDueDate" required>
                    </div>

                    <div class="mb-3">
                        <label for="editNote" class="form-label">Note</label>
                        <textarea class="form-control" id="editNote" rows="3" maxlength="1000"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveEdit()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Loan Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Loan Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="viewContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@push('scripts')
<script>
let loansTable;
let loansTrendChart = null; // Chart instance
const canWrite = {{ auth()->user()->hasAnyRole(['admin', 'manager', 'staff']) ? 'true' : 'false' }};
const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};

// Scanner Mode state
let scannerModeEnabled = false;

$(document).ready(function() {
    loadLoans();
    
    // Load dashboard stats
    fetchLoanStats();
    
    // Load trends chart
    fetchLoanTrends();

    // Initialize Scanner Mode from localStorage
    initScannerMode();

    // Filter change handlers
    $('#searchInput').on('keyup', debounce(function() {
        loansTable.search(this.value).draw();
    }, 500));

    $('#statusFilter, #borrowerTypeFilter, #dateFromFilter, #dateToFilter').on('change', function() {
        loansTable.ajax.reload();
        // Update badge when filter changes
        fetchLoanStats();
    });
    
    // Update pending requests count every 30 seconds
    setInterval(function() {
        fetchLoanStats();
    }, 30000);
    
    // Trend chart filters
    $('#dashboardPeriod, #dashboardBorrowerType').on('change', function() {
        fetchLoanTrends();
    });
});

// ========================================
// DASHBOARD STATISTICS
// ========================================

// Fetch loan statistics
function fetchLoanStats() {
    $.ajax({
        url: '{{ route("admin.library.loans.stats") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                // Update stat cards with animation
                $('#statBorrowedToday').html(data.borrowed_today);
                $('#statReturnedToday').html(data.returned_today);
                $('#statBorrowedActive').html(data.borrowed_active);
                $('#statOverdueActive').html(data.overdue_active);
                
                // Update pending requests badge
                updatePendingRequestsBadge(data.pending_requests || 0);
                
                // Update overdue loans badge
                if (data.overdue_count !== undefined) {
                    updateOverdueLoansBadge(data.overdue_count || 0);
                }
            }
        },
        error: function(xhr) {
            console.error('Failed to fetch loan stats:', xhr);
            // Show error placeholders
            $('#statBorrowedToday').html('<small>Error</small>');
            $('#statReturnedToday').html('<small>Error</small>');
            $('#statBorrowedActive').html('<small>Error</small>');
            $('#statOverdueActive').html('<small>Error</small>');
        }
    });
}

// Update pending requests badge
function updatePendingRequestsBadge(count) {
    const badge = $('#pendingRequestsBadge');
    if (count > 0) {
        badge.text(count).show();
        // Add pulse animation
        badge.addClass('animate__animated animate__pulse');
        setTimeout(function() {
            badge.removeClass('animate__animated animate__pulse');
        }, 1000);
    } else {
        badge.hide();
    }
    
    // Also update header badge if function exists
    if (typeof updateHeaderPendingRequestsBadge === 'function') {
        updateHeaderPendingRequestsBadge();
    }
}

// Show pending loan requests
function showPendingRequests() {
    // Set status filter to 'requested'
    $('#statusFilter').val('requested');
    
    // Reload table with filter
    loansTable.ajax.reload();
    
    // Show notification
    Swal.fire({
        icon: 'info',
        title: 'Pending Requests',
        text: 'Showing all pending loan requests. Click "Approve" to approve them.',
        timer: 3000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
}

// Update overdue loans badge
function updateOverdueLoansBadge(count) {
    const badge = $('#overdueLoansBadge');
    if (count > 0) {
        badge.text(count).show();
        // Add pulse animation
        badge.addClass('animate__animated animate__pulse');
        setTimeout(function() {
            badge.removeClass('animate__animated animate__pulse');
        }, 1000);
    } else {
        badge.hide();
    }
    
    // Also update header badge if function exists
    if (typeof updateHeaderOverdueBadge === 'function') {
        updateHeaderOverdueBadge();
    }
}

// Show overdue loans
function showOverdueLoans() {
    // Set status filter to 'overdue'
    $('#statusFilter').val('overdue');
    
    // Reload table with filter
    loansTable.ajax.reload();
    
    // Show notification
    Swal.fire({
        icon: 'warning',
        title: 'Overdue Loans',
        text: 'Showing all overdue loans. Please contact borrowers to return books.',
        timer: 3000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
}

// ========================================
// TREND CHART FUNCTIONS
// ========================================

// Fetch loan trends data
function fetchLoanTrends() {
    const period = $('#dashboardPeriod').val() || 'week';
    const borrowerType = $('#dashboardBorrowerType').val() || '';
    
    $.ajax({
        url: '{{ route("admin.library.loans.trends") }}',
        type: 'GET',
        data: {
            period: period,
            borrower_type: borrowerType
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                renderTrendChart(data.labels, data.borrowed, data.returned, data.overdue);
            }
        },
        error: function(xhr) {
            console.error('Failed to fetch loan trends:', xhr);
            // Show error message on chart
            const ctx = document.getElementById('loansTrendChart');
            if (ctx) {
                ctx.getContext('2d').clearRect(0, 0, ctx.width, ctx.height);
            }
        }
    });
}

// Render trend chart
function renderTrendChart(labels, borrowedData, returnedData, overdueData) {
    const ctx = document.getElementById('loansTrendChart');
    
    if (!ctx) {
        console.error('Chart canvas not found');
        return;
    }
    
    // Destroy existing chart instance
    if (loansTrendChart) {
        loansTrendChart.destroy();
    }
    
    // Create new chart
    loansTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Borrowed',
                    data: borrowedData,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Returned',
                    data: returnedData,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Overdue',
                    data: overdueData,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

// ========================================
// SCANNER MODE FUNCTIONS
// ========================================

// Initialize Scanner Mode
function initScannerMode() {
    // Restore from localStorage
    const savedState = localStorage.getItem('libraryLoansScannerMode');
    scannerModeEnabled = savedState === '1';
    
    // Set toggle state
    $('#scannerModeToggle').prop('checked', scannerModeEnabled);
    
    // Show/hide help text
    if (scannerModeEnabled) {
        $('#scannerModeHelp').slideDown();
    }
    
    // Toggle event
    $('#scannerModeToggle').on('change', function() {
        scannerModeEnabled = this.checked;
        localStorage.setItem('libraryLoansScannerMode', scannerModeEnabled ? '1' : '0');
        
        if (scannerModeEnabled) {
            $('#scannerModeHelp').slideDown();
            Swal.fire({
                title: 'Scanner Mode Enabled',
                text: 'Barcode input will autofocus and auto-lookup when you scan.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            $('#scannerModeHelp').slideUp();
        }
    });
}

// Load loans DataTable
function loadLoans() {
    loansTable = $('#loansTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.library.loans.index") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.borrower_type = $('#borrowerTypeFilter').val();
                d.date_from = $('#dateFromFilter').val();
                d.date_to = $('#dateToFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'barcode', name: 'copy.barcode' },
            { data: 'book_title', name: 'copy.item.title', orderable: false },
            { data: 'borrower_info', name: 'borrower_type', orderable: false },
            { data: 'borrowed_at', name: 'borrowed_at' },
            { data: 'due_date', name: 'due_date' },
            { data: 'returned_at', name: 'returned_at' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'processed_by', name: 'processedByStaff.khmer_name', orderable: false },
            { data: 'received_by', name: 'receivedByStaff.khmer_name', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[4, 'desc']],
        pageLength: 25,
        responsive: true
    });
}

// Clear filters
function clearFilters() {
    $('#searchInput').val('');
    $('#statusFilter').val('borrowed');
    $('#borrowerTypeFilter').val('all');
    $('#dateFromFilter').val('');
    $('#dateToFilter').val('');
    loansTable.search('').ajax.reload();
}

// View loan details
function viewLoan(id) {
    $.ajax({
        url: `/admin/library/loans/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const loan = response.data;
                
                let statusBadge = '';
                if (loan.status === 'requested') statusBadge = '<span class="badge bg-warning">Requested (Pending)</span>';
                else if (loan.status === 'borrowed') statusBadge = '<span class="badge bg-primary">Borrowed</span>';
                else if (loan.status === 'returned') statusBadge = '<span class="badge bg-success">Returned</span>';
                else if (loan.status === 'overdue') {
                    const daysOverdue = Math.floor(loan.days_overdue); // Convert to integer
                    statusBadge = '<span class="badge bg-danger">Overdue (' + daysOverdue + ' ' + (daysOverdue === 1 ? 'day' : 'days') + ')</span>';
                }
                else statusBadge = '<span class="badge bg-secondary">' + loan.status + '</span>';
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Book Information</h6>
                            <p><strong>Barcode:</strong> ${loan.barcode}</p>
                            <p><strong>Title:</strong> ${loan.book_title}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Borrower Information</h6>
                            <p><strong>Type:</strong> ${loan.borrower_type.charAt(0).toUpperCase() + loan.borrower_type.slice(1)}</p>
                            <p><strong>Name:</strong> ${loan.borrower_name}</p>
                            ${loan.borrower_identifier ? '<p><strong>ID:</strong> ' + loan.borrower_identifier + '</p>' : ''}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Loan Details</h6>
                            <p><strong>Borrowed:</strong> ${loan.borrowed_at || 'N/A'}</p>
                            <p><strong>Due Date:</strong> ${loan.due_date || 'N/A'}</p>
                            <p><strong>Returned:</strong> ${loan.returned_at || '<span class="text-muted">Not returned</span>'}</p>
                            <p><strong>Status:</strong> ${statusBadge}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Staff Information</h6>
                            <p><strong>Processed By:</strong> ${loan.processed_by || '<span class="text-muted">N/A</span>'}</p>
                            <p><strong>Received By:</strong> ${loan.received_by || '<span class="text-muted">N/A</span>'}</p>
                        </div>
                    </div>
                    ${loan.note ? '<hr><p><strong>Note:</strong><br>' + loan.note + '</p>' : ''}
                `;
                
                $('#viewContent').html(html);
                $('#viewModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load loan details.', 'error');
        }
    });
}

// Open edit modal
function openEditModal(id) {
    $.ajax({
        url: `/admin/library/loans/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const loan = response.data;
                $('#editLoanId').val(loan.id);
                $('#editDueDate').val(loan.due_date);
                $('#editNote').val(loan.note);
                $('#editModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load loan data.', 'error');
        }
    });
}

// Save edit
function saveEdit() {
    const id = $('#editLoanId').val();
    const formData = {
        due_date: $('#editDueDate').val(),
        note: $('#editNote').val()
    };

    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();

    $.ajax({
        url: `/admin/library/loans/${id}`,
        type: 'PUT',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#editModal').modal('hide');
                Swal.fire('Success', response.message, 'success');
                loansTable.ajax.reload();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    const input = $(`#edit${field.charAt(0).toUpperCase() + field.slice(1)}`);
                    input.addClass('is-invalid');
                    input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                });
            } else {
                const message = xhr.responseJSON?.message || 'Failed to update loan.';
                Swal.fire('Error', message, 'error');
            }
        }
    });
}

// Approve loan request
function approveLoan(id) {
    Swal.fire({
        title: 'Approve Loan Request?',
        text: 'This will approve the loan request and change the book status to "on loan".',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, approve it',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/library/loans/${id}/approve`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Approved', response.message, 'success');
                        loansTable.ajax.reload();
                        fetchLoanStats(); // Refresh stats (will update badge)
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to approve loan.';
                    Swal.fire('Error', message, 'error');
                }
            });
        }
    });
}

// Delete loan (admin only)
function deleteLoan(id) {
    Swal.fire({
        title: 'Delete Loan?',
        text: 'This will permanently delete this loan record. If borrowed, copy status will be restored to available.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/library/loans/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted', response.message, 'success');
                        loansTable.ajax.reload();
                        
                        // Refresh dashboard stats
                        fetchLoanStats();
                        
                        // Refresh trend chart
                        fetchLoanTrends();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to delete loan.';
                    Swal.fire('Error', message, 'error');
                }
            });
        }
    });
}

// Debounce helper
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Send loan notifications manually (NEW)
function sendNotifications() {
    Swal.fire({
        title: 'Send Loan Alerts',
        text: 'Check all active loans and send due/overdue notifications now?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-paper-plane"></i> Send Now',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0dcaf0',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: '{{ route("admin.library.loans.trigger-notifications") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const response = result.value;
            
            if (response.success) {
                const data = response.data;
                
                Swal.fire({
                    title: 'Notifications Sent!',
                    html: `
                        <div class="text-start">
                            <p><strong>${response.message}</strong></p>
                            <table class="table table-sm">
                                <tr>
                                    <th><i class="fas fa-clock text-warning"></i> Due Soon:</th>
                                    <td><strong>${data.due_soon}</strong> notification(s)</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-exclamation-triangle text-danger"></i> Overdue:</th>
                                    <td><strong>${data.overdue}</strong> notification(s)</td>
                                </tr>
                            </table>
                            <small class="text-muted">Recipients: Librarians + Borrowers (if have accounts)</small>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire('Error', response.message || 'Failed to send notifications.', 'error');
            }
        }
    });
}
</script>
@endpush

