@extends('layouts.app')

@section('title', 'Overdue Loans Report')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-exclamation-triangle text-danger"></i> Overdue Loans Report</h2>
        <button type="button" class="btn btn-info" onclick="refreshData()" title="Refresh Data">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>

    <!-- KPI Cards Row -->
    <div class="row mb-4" id="kpiCards">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-book"></i> Total Overdue Loans
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiTotalOverdue">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-exclamation-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-users"></i> Unique Borrowers
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiTotalBorrowers">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-user-friends fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-clock"></i> Max Days Overdue
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiMaxDays">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-calendar-times fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-copy"></i> Overdue Copies
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiTotalCopies">
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
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <!-- Overdue As Of Date -->
                <div class="col-md-2">
                    <label class="form-label">Overdue As Of</label>
                    <input type="date" class="form-control form-control-sm" id="filterOverdueAsOf" 
                           value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                </div>

                <!-- Borrower Type Filter -->
                <div class="col-md-2">
                    <label class="form-label">Borrower Type</label>
                    <select class="form-select form-select-sm" id="filterBorrowerType">
                        <option value="all">All Types</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="staff">Staff</option>
                        <option value="guest">Guest</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select class="form-select form-select-sm" id="filterCategory">
                        <option value="all">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Shelf Filter -->
                <div class="col-md-2">
                    <label class="form-label">Shelf</label>
                    <select class="form-select form-select-sm" id="filterShelf">
                        <option value="all">All Shelves</option>
                        @foreach($shelves as $shelf)
                            <option value="{{ $shelf->id }}">{{ $shelf->code }} ({{ $shelf->location }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Input -->
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control form-control-sm" id="filterSearch" 
                           placeholder="Borrower name, Book title, Barcode, Call number...">
                </div>

                <!-- Action Buttons -->
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-primary btn-sm w-100" onclick="applyFilters()">
                        <i class="fas fa-search"></i> Apply
                    </button>
                </div>
            </form>
            <div class="row mt-2">
                <div class="col-md-12">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="resetFilters()">
                        <i class="fas fa-redo"></i> Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="overdueLoansTable" class="table table-striped table-hover table-bordered" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Loan ID</th>
                            <th>Borrower</th>
                            <th>Book Title</th>
                            <th>Copy Info</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                            <th>Processed By</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Loan Modal (if needed) -->
<div class="modal fade" id="viewLoanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Loan Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewLoanContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<!-- DataTables Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    .badge-overdue-critical {
        background-color: #dc3545;
        color: white;
    }
    .badge-overdue-warning {
        background-color: #ffc107;
        color: #000;
    }
    .badge-overdue-info {
        background-color: #0dcaf0;
        color: #000;
    }
    .table td {
        vertical-align: middle;
    }
    .dt-buttons {
        margin-bottom: 1rem;
    }
    .dt-button {
        margin-right: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<!-- DataTables Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
let overdueLoansTable;

$(document).ready(function() {
    // Load KPI summary
    loadKPISummary();
    
    // Initialize DataTable
    initDataTable();

    // Filter change handlers
    $('#filterOverdueAsOf, #filterBorrowerType, #filterCategory, #filterShelf').on('change', function() {
        applyFilters();
    });

    // Search input with debounce
    let searchTimeout;
    $('#filterSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 500);
    });
});

// Load KPI Summary
function loadKPISummary() {
    $.ajax({
        url: '{{ route("admin.library.reports.overdue_loans.summary") }}',
        type: 'GET',
        data: {
            overdue_as_of: $('#filterOverdueAsOf').val(),
            borrower_type: $('#filterBorrowerType').val(),
            category_id: $('#filterCategory').val(),
            shelf_id: $('#filterShelf').val()
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#kpiTotalOverdue').text(data.total_overdue || 0);
                $('#kpiTotalBorrowers').text(data.total_borrowers || 0);
                $('#kpiTotalCopies').text(data.total_copies || 0);
                $('#kpiMaxDays').text(data.max_days_overdue || 0);
            }
        },
        error: function(xhr) {
            console.error('Failed to load KPI summary:', xhr);
            $('#kpiTotalOverdue').text('Error');
            $('#kpiTotalBorrowers').text('Error');
            $('#kpiTotalCopies').text('Error');
            $('#kpiMaxDays').text('Error');
        }
    });
}

// Initialize DataTable
function initDataTable() {
    overdueLoansTable = $('#overdueLoansTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.library.reports.overdue_loans.data") }}',
            type: 'GET',
            data: function(d) {
                d.overdue_as_of = $('#filterOverdueAsOf').val();
                d.borrower_type = $('#filterBorrowerType').val();
                d.category_id = $('#filterCategory').val();
                d.shelf_id = $('#filterShelf').val();
                // DataTables search is handled separately
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Ajax Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Loading Data',
                    text: 'Failed to load overdue loans. Please try again.'
                });
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'loan_id', name: 'id' },
            { data: 'borrower_info', name: 'borrower_info', orderable: false },
            { data: 'book_info', name: 'book_info', orderable: false },
            { data: 'copy_info', name: 'copy_info', orderable: false },
            { data: 'due_date', name: 'due_date' },
            { data: 'days_overdue', name: 'days_overdue', orderable: false },
            { data: 'processed_by', name: 'processed_by', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[5, 'asc']], // Sort by due_date ASC (oldest first)
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip', // Buttons, filter, table, info, pagination
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-sm btn-secondary',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7] // Exclude actions column
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-success',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7]
                },
                filename: 'Overdue_Loans_Report_' + new Date().toISOString().slice(0, 10)
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-danger',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7]
                },
                filename: 'Overdue_Loans_Report_' + new Date().toISOString().slice(0, 10),
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Overdue Loans Report - ' + new Date().toLocaleDateString()
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-sm btn-info',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7]
                }
            }
        ],
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading overdue loans...',
            emptyTable: 'No overdue loans found.',
            zeroRecords: 'No matching overdue loans found. Try adjusting your filters.'
        }
    });

    // Handle DataTables search (global search)
    $('#filterSearch').on('keyup', function() {
        overdueLoansTable.search(this.value).draw();
    });
}

// Apply filters
function applyFilters() {
    overdueLoansTable.ajax.reload(function() {
        // Reload KPI summary after table reload
        loadKPISummary();
    });
}

// Reset filters
function resetFilters() {
    $('#filterOverdueAsOf').val('{{ \Carbon\Carbon::today()->format("Y-m-d") }}');
    $('#filterBorrowerType').val('all');
    $('#filterCategory').val('all');
    $('#filterShelf').val('all');
    $('#filterSearch').val('');
    applyFilters();
}

// Refresh data
function refreshData() {
    overdueLoansTable.ajax.reload();
    loadKPISummary();
    Swal.fire({
        icon: 'success',
        title: 'Refreshed',
        text: 'Data has been refreshed.',
        timer: 1500,
        showConfirmButton: false
    });
}

// View loan details
function viewLoan(loanId) {
    $.ajax({
        url: `/admin/library/loans/${loanId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const loan = response.data;
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Loan Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Loan ID:</th>
                                    <td>#${loan.id}</td>
                                </tr>
                                <tr>
                                    <th>Borrower:</th>
                                    <td>${loan.borrower_name} <span class="badge bg-info">${loan.borrower_type}</span></td>
                                </tr>
                                <tr>
                                    <th>Book Title:</th>
                                    <td>${loan.book_title || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Barcode:</th>
                                    <td>${loan.barcode || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Borrowed At:</th>
                                    <td>${loan.borrowed_at || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Due Date:</th>
                                    <td><span class="text-danger">${loan.due_date || 'N/A'}</span></td>
                                </tr>
                                <tr>
                                    <th>Days Overdue:</th>
                                    <td>${loan.days_overdue || 0} days</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Staff Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Processed By:</th>
                                    <td>${loan.processed_by || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Received By:</th>
                                    <td>${loan.received_by || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>${loan.status || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Note:</th>
                                    <td>${loan.note || 'N/A'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                `;
                $('#viewLoanContent').html(html);
                $('#viewLoanModal').modal('show');
            } else {
                Swal.fire('Error', response.message || 'Failed to load loan details.', 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load loan details.', 'error');
        }
    });
}

// Send reminder (dummy action)
function sendReminder(loanId) {
    Swal.fire({
        icon: 'info',
        title: 'Feature Coming Soon',
        text: 'The reminder notification feature is currently under development. This will allow you to send automated reminders to borrowers about their overdue loans.',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3085d6'
    });
}
</script>
@endpush

