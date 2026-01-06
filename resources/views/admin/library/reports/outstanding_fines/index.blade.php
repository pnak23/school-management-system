@extends('layouts.app')

@section('title', 'Outstanding Fines Report')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-dollar-sign text-danger"></i> Outstanding Fines Report</h2>
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
                                <i class="fas fa-money-bill-wave"></i> Total Outstanding Amount
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiTotalOutstanding">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-percentage"></i> Collection Rate
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiCollectionRate">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-chart-line fa-3x opacity-50"></i>
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
                                <i class="fas fa-exclamation-circle"></i> Unpaid Count
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiUnpaidCount">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-times-circle fa-3x opacity-50"></i>
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
                                <i class="fas fa-hourglass-half"></i> Partial Count
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiPartialCount">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
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
                <!-- Assessed From Date -->
                <div class="col-md-2">
                    <label class="form-label">Assessed From</label>
                    <input type="date" class="form-control form-control-sm" id="filterAssessedFrom" 
                           value="{{ \Carbon\Carbon::today()->subDays(30)->format('Y-m-d') }}">
                </div>

                <!-- Assessed To Date -->
                <div class="col-md-2">
                    <label class="form-label">Assessed To</label>
                    <input type="date" class="form-control form-control-sm" id="filterAssessedTo" 
                           value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select form-select-sm" id="filterStatus">
                        <option value="all">All Status</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>

                <!-- Fine Type Filter -->
                <div class="col-md-2">
                    <label class="form-label">Fine Type</label>
                    <select class="form-select form-select-sm" id="filterFineType">
                        <option value="all">All Types</option>
                        @foreach($fineTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
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

                <!-- Min Outstanding -->
                <div class="col-md-2">
                    <label class="form-label">Min Outstanding ($)</label>
                    <input type="number" class="form-control form-control-sm" id="filterMinOutstanding" 
                           step="0.01" min="0" placeholder="0.00">
                </div>

                <!-- Max Outstanding -->
                <div class="col-md-2">
                    <label class="form-label">Max Outstanding ($)</label>
                    <input type="number" class="form-control form-control-sm" id="filterMaxOutstanding" 
                           step="0.01" min="0" placeholder="0.00">
                </div>

                <!-- Search Input -->
                <div class="col-md-10">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control form-control-sm" id="filterSearch" 
                           placeholder="Fine ID, Loan ID, User name/email, Borrower, Book title, Barcode, Note...">
                </div>

                <!-- Action Buttons -->
                <div class="col-md-2 d-flex align-items-end">
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
                <table id="outstandingFinesTable" class="table table-striped table-hover table-bordered" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Fine ID</th>
                            <th>Loan ID</th>
                            <th>Payer User</th>
                            <th>Borrower</th>
                            <th>Book Title</th>
                            <th>Copy Info</th>
                            <th>Fine Type</th>
                            <th>Amount</th>
                            <th>Paid</th>
                            <th>Outstanding</th>
                            <th>Status</th>
                            <th>Assessed At</th>
                            <th>Paid At</th>
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

<!-- View Loan Modal -->
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
    .badge-unpaid {
        background-color: #dc3545;
        color: white;
    }
    .badge-partial {
        background-color: #ffc107;
        color: #000;
    }
    .badge-paid {
        background-color: #198754;
        color: white;
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
    .currency {
        font-weight: 600;
    }
    .currency.positive {
        color: #198754;
    }
    .currency.negative {
        color: #dc3545;
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
let outstandingFinesTable;
const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};

$(document).ready(function() {
    // Load KPI summary
    loadKPISummary();
    
    // Initialize DataTable
    initDataTable();

    // Filter change handlers
    $('#filterAssessedFrom, #filterAssessedTo, #filterStatus, #filterFineType, #filterBorrowerType, #filterMinOutstanding, #filterMaxOutstanding').on('change', function() {
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
        url: '{{ route("admin.library.reports.outstanding_fines.summary") }}',
        type: 'GET',
        data: {
            assessed_from: $('#filterAssessedFrom').val(),
            assessed_to: $('#filterAssessedTo').val(),
            status: $('#filterStatus').val(),
            fine_type: $('#filterFineType').val(),
            borrower_type: $('#filterBorrowerType').val(),
            min_outstanding: $('#filterMinOutstanding').val(),
            max_outstanding: $('#filterMaxOutstanding').val()
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                // Format currency
                $('#kpiTotalOutstanding').text(formatCurrency(data.total_outstanding_amount || 0));
                $('#kpiCollectionRate').text((data.collection_rate_percent || 0) + '%');
                $('#kpiUnpaidCount').text(data.unpaid_count || 0);
                $('#kpiPartialCount').text(data.partial_count || 0);
            }
        },
        error: function(xhr) {
            console.error('Failed to load KPI summary:', xhr);
            $('#kpiTotalOutstanding').text('Error');
            $('#kpiCollectionRate').text('Error');
            $('#kpiUnpaidCount').text('Error');
            $('#kpiPartialCount').text('Error');
        }
    });
}

// Format currency
function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2) + ' $';
}

// Initialize DataTable
function initDataTable() {
    outstandingFinesTable = $('#outstandingFinesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.library.reports.outstanding_fines.data") }}',
            type: 'GET',
            data: function(d) {
                d.assessed_from = $('#filterAssessedFrom').val();
                d.assessed_to = $('#filterAssessedTo').val();
                d.status = $('#filterStatus').val();
                d.fine_type = $('#filterFineType').val();
                d.borrower_type = $('#filterBorrowerType').val();
                d.min_outstanding = $('#filterMinOutstanding').val();
                d.max_outstanding = $('#filterMaxOutstanding').val();
                // DataTables search is handled separately
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Ajax Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Loading Data',
                    text: 'Failed to load outstanding fines. Please try again.'
                });
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'fine_id', name: 'id' },
            { data: 'loan_id', name: 'loan_id', orderable: false },
            { data: 'payer_user', name: 'payer_user', orderable: false },
            { data: 'borrower_info', name: 'borrower_info', orderable: false },
            { data: 'book_info', name: 'book_info', orderable: false },
            { data: 'copy_info', name: 'copy_info', orderable: false },
            { data: 'fine_type', name: 'fine_type' },
            { data: 'amount', name: 'amount' },
            { data: 'paid_amount', name: 'paid_amount' },
            { data: 'outstanding_amount', name: 'outstanding_amount', orderable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false },
            { data: 'assessed_at', name: 'assessed_at' },
            { data: 'paid_at', name: 'paid_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[12, 'desc']], // Sort by assessed_at DESC (newest first)
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip', // Buttons, filter, table, info, pagination
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-sm btn-secondary',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13] // Exclude actions column
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-success',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
                },
                filename: 'Outstanding_Fines_Report_' + new Date().toISOString().slice(0, 10)
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-danger',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
                },
                filename: 'Outstanding_Fines_Report_' + new Date().toISOString().slice(0, 10),
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Outstanding Fines Report - ' + new Date().toLocaleDateString()
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-sm btn-info',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
                }
            }
        ],
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading outstanding fines...',
            emptyTable: 'No outstanding fines found.',
            zeroRecords: 'No matching outstanding fines found. Try adjusting your filters.'
        }
    });

    // Handle DataTables search (global search)
    $('#filterSearch').on('keyup', function() {
        outstandingFinesTable.search(this.value).draw();
    });
}

// Apply filters
function applyFilters() {
    outstandingFinesTable.ajax.reload(function() {
        // Reload KPI summary after table reload
        loadKPISummary();
    });
}

// Reset filters
function resetFilters() {
    $('#filterAssessedFrom').val('{{ \Carbon\Carbon::today()->subDays(30)->format("Y-m-d") }}');
    $('#filterAssessedTo').val('{{ \Carbon\Carbon::today()->format("Y-m-d") }}');
    $('#filterStatus').val('all');
    $('#filterFineType').val('all');
    $('#filterBorrowerType').val('all');
    $('#filterMinOutstanding').val('');
    $('#filterMaxOutstanding').val('');
    $('#filterSearch').val('');
    applyFilters();
}

// Refresh data
function refreshData() {
    outstandingFinesTable.ajax.reload();
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
                                    <td>${loan.due_date || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>${loan.status || 'Active'}</td>
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

// Mark fine as paid
function markAsPaid(fineId) {
    if (!isAdmin) {
        Swal.fire('Unauthorized', 'Only administrators can mark fines as paid.', 'error');
        return;
    }

    Swal.fire({
        title: 'Mark as Paid?',
        text: 'This will mark the fine as fully paid. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, mark as paid',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: `{{ url('admin/library/reports/outstanding-fines') }}/${fineId}/mark-paid`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json'
            }).then(function(response) {
                if (response.success) {
                    return response;
                } else {
                    throw new Error(response.message || 'Failed to mark fine as paid.');
                }
            }).catch(function(error) {
                Swal.showValidationMessage(error.responseJSON?.message || error.message || 'Request failed');
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Fine Marked as Paid',
                text: result.value.message || 'The fine has been marked as fully paid.',
                timer: 2000,
                showConfirmButton: false
            });
            
            // Refresh table and KPI
            outstandingFinesTable.ajax.reload();
            loadKPISummary();
        }
    });
}
</script>
@endpush
















