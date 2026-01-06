@extends('layouts.app')

@section('title', 'Collection Summary / Availability')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-book text-primary"></i> Collection Summary / Availability</h2>
        <button type="button" class="btn btn-info" onclick="refreshData()" title="Refresh Data">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>

    <!-- KPI Cards Row -->
    <div class="row mb-4" id="kpiCards">
        <!-- Total Titles -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-book"></i> Total Titles
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiTotalTitles">
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

        <!-- Total Copies -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-copy"></i> Total Copies
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiTotalCopies">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-copy fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Copies -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-check-circle"></i> Available Copies
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiAvailableCopies">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Availability Rate -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-percentage"></i> Availability Rate
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiAvailabilityRate">
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
    </div>

    <!-- Status Badges Row -->
    <div class="row mb-4">
        <div class="col-md-4 col-sm-6 mb-2">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-book-reader"></i> Borrowed:</span>
                        <span class="badge bg-warning fs-6" id="kpiBorrowedCopies">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-2">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-exclamation-triangle"></i> Lost:</span>
                        <span class="badge bg-danger fs-6" id="kpiLostCopies">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-2">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-tools"></i> Damaged:</span>
                        <span class="badge bg-secondary fs-6" id="kpiDamagedCopies">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
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

                <!-- Publisher Filter -->
                <div class="col-md-2">
                    <label class="form-label">Publisher</label>
                    <select class="form-select form-select-sm" id="filterPublisher">
                        <option value="all">All Publishers</option>
                        @foreach($publishers as $publisher)
                            <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Language Filter -->
                <div class="col-md-2">
                    <label class="form-label">Language</label>
                    <select class="form-select form-select-sm" id="filterLanguage">
                        <option value="all">All Languages</option>
                        @foreach($languages as $language)
                            <option value="{{ $language }}">{{ $language }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Published Year From -->
                <div class="col-md-2">
                    <label class="form-label">Published Year From</label>
                    <input type="number" class="form-control form-control-sm" id="filterPublishedYearFrom" 
                           placeholder="Year" min="1900" max="{{ date('Y') + 1 }}">
                </div>

                <!-- Published Year To -->
                <div class="col-md-2">
                    <label class="form-label">Published Year To</label>
                    <input type="number" class="form-control form-control-sm" id="filterPublishedYearTo" 
                           placeholder="Year" min="1900" max="{{ date('Y') + 1 }}">
                </div>

                <!-- Acquired Date From -->
                <div class="col-md-2">
                    <label class="form-label">Acquired From</label>
                    <input type="date" class="form-control form-control-sm" id="filterAcquiredFrom">
                </div>

                <!-- Acquired Date To -->
                <div class="col-md-2">
                    <label class="form-label">Acquired To</label>
                    <input type="date" class="form-control form-control-sm" id="filterAcquiredTo">
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select form-select-sm" id="filterStatus">
                        <option value="all">All Status</option>
                        <option value="available">Available</option>
                        <option value="borrowed">Borrowed</option>
                        <option value="lost">Lost</option>
                        <option value="damaged">Damaged</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control form-control-sm" id="filterSearch" 
                           placeholder="Title, ISBN, Call number, Barcode, Category, Shelf, Publisher...">
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
                <table id="collectionSummaryTable" class="table table-striped table-hover table-bordered" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Title ID</th>
                            <th>Title</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Publisher</th>
                            <th>Language</th>
                            <th>Published Year</th>
                            <th class="text-end">Total Copies</th>
                            <th class="text-end">Available</th>
                            <th class="text-end">Borrowed</th>
                            <th class="text-end">Lost</th>
                            <th class="text-end">Damaged</th>
                            <th class="text-end">Availability Rate</th>
                            <th>Primary Shelf</th>
                            <th width="120">Actions</th>
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

<!-- View Item Modal (if needed) -->
<div class="modal fade" id="viewItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Item Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewItemContent">
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
    .table td {
        vertical-align: middle;
    }
    .dt-buttons {
        margin-bottom: 1rem;
    }
    .dt-button {
        margin-right: 0.5rem;
    }
    .text-end {
        text-align: right !important;
    }
    .badge {
        font-size: 0.875rem;
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
let collectionSummaryTable;

$(document).ready(function() {
    // Load KPI summary
    loadKPISummary();
    
    // Initialize DataTable
    initDataTable();

    // Filter change handlers
    $('#filterCategory, #filterShelf, #filterPublisher, #filterLanguage, #filterStatus').on('change', function() {
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
        url: '{{ route("admin.library.reports.collection_summary.summary") }}',
        type: 'GET',
        data: {
            category_id: $('#filterCategory').val(),
            shelf_id: $('#filterShelf').val(),
            publisher_id: $('#filterPublisher').val(),
            language: $('#filterLanguage').val(),
            published_year_from: $('#filterPublishedYearFrom').val(),
            published_year_to: $('#filterPublishedYearTo').val(),
            acquired_from: $('#filterAcquiredFrom').val(),
            acquired_to: $('#filterAcquiredTo').val(),
            status: $('#filterStatus').val(),
            search: {
                value: $('#filterSearch').val()
            }
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#kpiTotalTitles').text(data.total_titles || 0);
                $('#kpiTotalCopies').text(data.total_copies || 0);
                $('#kpiAvailableCopies').text(data.available_copies || 0);
                
                // Availability Rate with 2 decimals
                const rate = parseFloat(data.availability_rate_percent || 0).toFixed(2);
                $('#kpiAvailabilityRate').text(rate + '%');
                
                // Status badges
                $('#kpiBorrowedCopies').html(data.borrowed_copies || 0);
                $('#kpiLostCopies').html(data.lost_copies || 0);
                $('#kpiDamagedCopies').html(data.damaged_copies || 0);
            } else {
                console.error('Failed to load KPI summary:', response);
                showKPIError();
            }
        },
        error: function(xhr) {
            console.error('Failed to load KPI summary:', xhr);
            showKPIError();
        }
    });
}

// Show error in KPI cards
function showKPIError() {
    $('#kpiTotalTitles').text('Error');
    $('#kpiTotalCopies').text('Error');
    $('#kpiAvailableCopies').text('Error');
    $('#kpiAvailabilityRate').text('Error');
    $('#kpiBorrowedCopies').html('Error');
    $('#kpiLostCopies').html('Error');
    $('#kpiDamagedCopies').html('Error');
}

// Initialize DataTable
function initDataTable() {
    collectionSummaryTable = $('#collectionSummaryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.library.reports.collection_summary.data") }}',
            type: 'GET',
            data: function(d) {
                d.category_id = $('#filterCategory').val();
                d.shelf_id = $('#filterShelf').val();
                d.publisher_id = $('#filterPublisher').val();
                d.language = $('#filterLanguage').val();
                d.published_year_from = $('#filterPublishedYearFrom').val();
                d.published_year_to = $('#filterPublishedYearTo').val();
                d.acquired_from = $('#filterAcquiredFrom').val();
                d.acquired_to = $('#filterAcquiredTo').val();
                d.status = $('#filterStatus').val();
                // DataTables search is handled separately
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Ajax Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Loading Data',
                    text: 'Failed to load collection summary. Please try again.'
                });
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'title_id', name: 'title_id', orderable: false },
            { data: 'title', name: 'title' },
            { data: 'isbn', name: 'isbn', orderable: false },
            { data: 'category', name: 'category', orderable: false },
            { data: 'publisher', name: 'publisher', orderable: false },
            { data: 'language', name: 'language', orderable: false },
            { data: 'published_year', name: 'published_year', orderable: false },
            { data: 'total_copies', name: 'total_copies', orderable: false, className: 'text-end' },
            { data: 'available', name: 'available', orderable: false, className: 'text-end' },
            { data: 'borrowed', name: 'borrowed', orderable: false, className: 'text-end' },
            { data: 'lost', name: 'lost', orderable: false, className: 'text-end' },
            { data: 'damaged', name: 'damaged', orderable: false, className: 'text-end' },
            { data: 'availability_rate', name: 'availability_rate', orderable: false, className: 'text-end' },
            { data: 'primary_shelf', name: 'primary_shelf', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, width: '120px' }
        ],
        order: [[1, 'asc']], // Sort by Title ID ASC
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip', // Buttons, filter, table, info, pagination
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-sm btn-secondary',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14] // Exclude actions column
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-success',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                },
                filename: 'Collection_Summary_Report_' + new Date().toISOString().slice(0, 10)
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-danger',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                },
                filename: 'Collection_Summary_Report_' + new Date().toISOString().slice(0, 10),
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Collection Summary / Availability Report - ' + new Date().toLocaleDateString()
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-sm btn-info',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                }
            }
        ],
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading collection summary...',
            emptyTable: 'No items found.',
            zeroRecords: 'No matching items found. Try adjusting your filters.'
        },
        columnDefs: [
            {
                targets: [8, 9, 10, 11, 12, 13], // Numeric columns
                className: 'text-end'
            }
        ]
    });

    // Handle DataTables search (global search)
    $('#filterSearch').on('keyup', function() {
        collectionSummaryTable.search(this.value).draw();
    });
}

// Apply filters
function applyFilters() {
    collectionSummaryTable.ajax.reload(function() {
        // Reload KPI summary after table reload
        loadKPISummary();
    });
}

// Reset filters
function resetFilters() {
    $('#filterCategory').val('all');
    $('#filterShelf').val('all');
    $('#filterPublisher').val('all');
    $('#filterLanguage').val('all');
    $('#filterPublishedYearFrom').val('');
    $('#filterPublishedYearTo').val('');
    $('#filterAcquiredFrom').val('');
    $('#filterAcquiredTo').val('');
    $('#filterStatus').val('all');
    $('#filterSearch').val('');
    applyFilters();
}

// Refresh data
function refreshData() {
    collectionSummaryTable.ajax.reload();
    loadKPISummary();
    Swal.fire({
        icon: 'success',
        title: 'Refreshed',
        text: 'Data has been refreshed.',
        timer: 1500,
        showConfirmButton: false
    });
}

// View item details
function viewItem(itemId) {
    $.ajax({
        url: '{{ route("admin.library.items.show", ":id") }}'.replace(':id', itemId),
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const item = response.data;
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Item Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Title ID:</th>
                                    <td>#${item.id}</td>
                                </tr>
                                <tr>
                                    <th>Title:</th>
                                    <td>${item.title || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>ISBN:</th>
                                    <td>${item.isbn || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>${item.category_name || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Publisher:</th>
                                    <td>${item.publisher_name || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Language:</th>
                                    <td>${item.language || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Published Year:</th>
                                    <td>${item.published_year || 'N/A'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Collection Summary</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Total Copies:</th>
                                    <td><strong>${item.copies_count || 0}</strong></td>
                                </tr>
                                <tr>
                                    <th>Available:</th>
                                    <td><span class="badge bg-success">${item.available_copies || 0}</span></td>
                                </tr>
                                <tr>
                                    <th>Availability Rate:</th>
                                    <td>
                                        <span class="badge bg-info">
                                            ${item.copies_count > 0 ? ((item.available_copies / item.copies_count) * 100).toFixed(2) : 0}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Authors:</th>
                                    <td>${item.authors_count || 0} author(s)</td>
                                </tr>
                            </table>
                            <div class="mt-2">
                                <a href="${'{{ route("admin.library.copies.index") }}'}?item_id=${item.id}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-book"></i> View All Copies
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                $('#viewItemContent').html(html);
                $('#viewItemModal').modal('show');
            } else {
                Swal.fire('Error', response.message || 'Failed to load item details.', 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load item details.', 'error');
        }
    });
}
</script>
@endpush

