@extends('layouts.app')

@section('title', 'Daily Visit Statistics + Open Sessions')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-calendar-day text-primary"></i> Daily Visit Statistics + Open Sessions</h2>
        <button type="button" class="btn btn-info" onclick="refreshData()" title="Refresh Data">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>

    <!-- KPI Cards Row -->
    <div class="row mb-4" id="kpiCards">
        <!-- Total Visits -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-users"></i> Total Visits
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiTotalVisits">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Morning Visits -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-sun"></i> Morning Visits
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiMorningVisits">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-sun fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Afternoon Visits -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-cloud-sun"></i> Afternoon Visits
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiAfternoonVisits">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-cloud-sun fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Open Sessions -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-door-open"></i> Open Sessions
                            </h6>
                            <h2 class="mb-0 mt-2" id="kpiOpenSessions">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-door-open fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Smaller KPI Badges Row -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-user"></i> User Visits:</span>
                        <span class="badge bg-primary fs-6" id="kpiUserVisits">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-user-friends"></i> Guest Visits:</span>
                        <span class="badge bg-warning fs-6" id="kpiGuestVisits">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-check-circle"></i> Checked-out:</span>
                        <span class="badge bg-success fs-6" id="kpiCheckedOut">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-clock"></i> Avg Session:</span>
                        <span class="badge bg-info fs-6" id="kpiAvgSession">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today Operations Panel -->
    <div class="card mb-4 bg-light">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-tachometer-alt"></i> Today Operations Panel</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Open Sessions Count -->
                <div class="col-md-3 mb-3">
                    <div class="text-center p-3 bg-white rounded shadow-sm">
                        <h6 class="text-muted mb-2">Open Sessions Now</h6>
                        <h1 class="display-4 text-danger mb-0" id="operationsOpenSessions">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </h1>
                    </div>
                </div>

                <!-- Last 10 Check-ins -->
                <div class="col-md-4 mb-3">
                    <div class="bg-white rounded shadow-sm p-3" style="max-height: 300px; overflow-y: auto;">
                        <h6 class="text-primary mb-3"><i class="fas fa-sign-in-alt"></i> Last 10 Check-ins</h6>
                        <div id="operationsCheckIns">
                            <div class="text-center py-3">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last 10 Check-outs -->
                <div class="col-md-5 mb-3">
                    <div class="bg-white rounded shadow-sm p-3" style="max-height: 300px; overflow-y: auto;">
                        <h6 class="text-success mb-3"><i class="fas fa-sign-out-alt"></i> Last 10 Check-outs</h6>
                        <div id="operationsCheckOuts">
                            <div class="text-center py-3">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </div>
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
                <!-- Date Filter (Single Date) -->
                <div class="col-md-2">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control form-control-sm" id="filterDate" 
                           value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                </div>

                <!-- Date Range (Optional) -->
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateTo">
                </div>

                <!-- Session Filter -->
                <div class="col-md-2">
                    <label class="form-label">Session</label>
                    <select class="form-select form-select-sm" id="filterSession">
                        <option value="all">All Sessions</option>
                        <option value="morning">Morning</option>
                        <option value="afternoon">Afternoon</option>
                    </select>
                </div>

                <!-- Visitor Type Filter -->
                <div class="col-md-2">
                    <label class="form-label">Visitor Type</label>
                    <select class="form-select form-select-sm" id="filterVisitorType">
                        <option value="all">All Types</option>
                        <option value="user">User</option>
                        <option value="guest">Guest</option>
                    </select>
                </div>

                <!-- Checked-in By Staff Filter -->
                <div class="col-md-2">
                    <label class="form-label">Checked-in By</label>
                    <select class="form-select form-select-sm" id="filterCheckedInBy">
                        <option value="all">All Staff</option>
                        @foreach($staffList as $staff)
                            <option value="{{ $staff->id }}">
                                {{ $staff->english_name ?? $staff->khmer_name }} ({{ $staff->staff_code ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Input -->
                <div class="col-md-8">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control form-control-sm" id="filterSearch" 
                           placeholder="Visit ID, Visitor name, Email, Phone, Purpose, Note...">
                </div>

                <!-- Action Buttons -->
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-primary btn-sm me-2" onclick="applyFilters()">
                        <i class="fas fa-search"></i> Apply
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="resetFilters()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabs Card -->
    <div class="card">
        <div class="card-header bg-light">
            <ul class="nav nav-tabs card-header-tabs" id="visitTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="visits-tab" data-bs-toggle="tab" data-bs-target="#visits" 
                            type="button" role="tab" aria-controls="visits" aria-selected="true">
                        <i class="fas fa-list"></i> Visits List
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="open-sessions-tab" data-bs-toggle="tab" data-bs-target="#open-sessions" 
                            type="button" role="tab" aria-controls="open-sessions" aria-selected="false">
                        <i class="fas fa-door-open"></i> Open Sessions
                        <span class="badge bg-danger ms-2" id="openSessionsBadge">0</span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="visitTabsContent">
                <!-- Visits List Tab -->
                <div class="tab-pane fade show active" id="visits" role="tabpanel" aria-labelledby="visits-tab">
                    <div class="table-responsive">
                        <table id="visitsTable" class="table table-striped table-hover table-bordered" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Visit ID</th>
                                    <th>Visit Date</th>
                                    <th>Check-in Time</th>
                                    <th>Check-out Time</th>
                                    <th>Session</th>
                                    <th>Visitor Type</th>
                                    <th>Visitor Name</th>
                                    <th>Phone</th>
                                    <th>Purpose</th>
                                    <th>Checked-in By</th>
                                    <th>Checked-out By</th>
                                    <th>Status</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Open Sessions Tab -->
                <div class="tab-pane fade" id="open-sessions" role="tabpanel" aria-labelledby="open-sessions-tab">
                    <div class="table-responsive">
                        <table id="openSessionsTable" class="table table-striped table-hover table-bordered" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Visit ID</th>
                                    <th>Check-in Time</th>
                                    <th>Session</th>
                                    <th>Visitor Type</th>
                                    <th>Visitor Name</th>
                                    <th>Purpose</th>
                                    <th>Checked-in By</th>
                                    <th class="text-end">Duration</th>
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
    </div>
</div>

<!-- View Visit Modal -->
<div class="modal fade" id="viewVisitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Visit Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewVisitContent">
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
    .nav-tabs .nav-link.active {
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
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
let visitsTable;
let openSessionsTable;
let openSessionsPollingInterval = null;

$(document).ready(function() {
    // Load KPI summary
    loadKPISummary();
    
    // Load Today Operations Panel
    loadTodayOperations();
    
    // Initialize DataTables
    initVisitsTable();
    initOpenSessionsTable();

    // Filter change handlers
    $('#filterDate, #filterDateFrom, #filterDateTo, #filterSession, #filterVisitorType, #filterCheckedInBy').on('change', function() {
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

    // Start polling for open sessions (every 60 seconds)
    startOpenSessionsPolling();
    
    // Auto-refresh Today Operations Panel every 30 seconds
    setInterval(loadTodayOperations, 30000);
});

// Load KPI Summary
function loadKPISummary() {
    $.ajax({
        url: '{{ route("admin.library.reports.daily_visits.summary") }}',
        type: 'GET',
        data: {
            date: $('#filterDate').val(),
            date_from: $('#filterDateFrom').val(),
            date_to: $('#filterDateTo').val(),
            session: $('#filterSession').val(),
            visitor_type: $('#filterVisitorType').val(),
            checked_in_by_staff_id: $('#filterCheckedInBy').val(),
            search: {
                value: $('#filterSearch').val()
            }
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#kpiTotalVisits').text(data.total_visits || 0);
                $('#kpiMorningVisits').text(data.visits_morning || 0);
                $('#kpiAfternoonVisits').text(data.visits_afternoon || 0);
                $('#kpiOpenSessions').text(data.open_sessions_count || 0);
                
                // Update badge
                $('#openSessionsBadge').text(data.open_sessions_count || 0);
                
                // Smaller KPIs
                $('#kpiUserVisits').html(data.total_users_visits || 0);
                $('#kpiGuestVisits').html(data.total_guest_visits || 0);
                $('#kpiCheckedOut').html(data.checked_out_count || 0);
                
                // Avg session minutes
                if (data.avg_session_minutes && data.avg_session_minutes > 0) {
                    const hours = Math.floor(data.avg_session_minutes / 60);
                    const minutes = Math.round(data.avg_session_minutes % 60);
                    if (hours > 0) {
                        $('#kpiAvgSession').html(hours + 'h ' + minutes + 'm');
                    } else {
                        $('#kpiAvgSession').html(minutes + 'm');
                    }
                } else {
                    $('#kpiAvgSession').html('N/A');
                }
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
    $('#kpiTotalVisits').text('Error');
    $('#kpiMorningVisits').text('Error');
    $('#kpiAfternoonVisits').text('Error');
    $('#kpiOpenSessions').text('Error');
    $('#kpiUserVisits').html('Error');
    $('#kpiGuestVisits').html('Error');
    $('#kpiCheckedOut').html('Error');
    $('#kpiAvgSession').html('Error');
}

// Load Today Operations Panel
function loadTodayOperations() {
    $.ajax({
        url: '{{ route("admin.library.reports.daily_visits.today_operations") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                // Update open sessions count
                $('#operationsOpenSessions').text(data.open_sessions_count || 0);
                
                // Update last check-ins
                let checkInsHtml = '';
                if (data.last_check_ins && data.last_check_ins.length > 0) {
                    data.last_check_ins.forEach(function(item) {
                        const badgeClass = item.type === 'User' ? 'primary' : 'warning';
                        checkInsHtml += `
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div>
                                    <strong>${item.time}</strong>
                                    <br>
                                    <small class="text-muted">${item.name || 'Unknown'}</small>
                                </div>
                                <span class="badge bg-${badgeClass}">${item.type}</span>
                            </div>
                        `;
                    });
                } else {
                    checkInsHtml = '<p class="text-muted text-center mb-0">No check-ins today</p>';
                }
                $('#operationsCheckIns').html(checkInsHtml);
                
                // Update last check-outs
                let checkOutsHtml = '';
                if (data.last_check_outs && data.last_check_outs.length > 0) {
                    data.last_check_outs.forEach(function(item) {
                        const badgeClass = item.type === 'User' ? 'primary' : 'warning';
                        checkOutsHtml += `
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div>
                                    <strong>${item.time}</strong>
                                    <br>
                                    <small class="text-muted">${item.name || 'Unknown'}</small>
                                </div>
                                <span class="badge bg-${badgeClass}">${item.type}</span>
                            </div>
                        `;
                    });
                } else {
                    checkOutsHtml = '<p class="text-muted text-center mb-0">No check-outs today</p>';
                }
                $('#operationsCheckOuts').html(checkOutsHtml);
            } else {
                console.error('Failed to load operations panel:', response);
                $('#operationsOpenSessions').text('Error');
                $('#operationsCheckIns').html('<p class="text-danger text-center">Error loading data</p>');
                $('#operationsCheckOuts').html('<p class="text-danger text-center">Error loading data</p>');
            }
        },
        error: function(xhr) {
            console.error('Failed to load operations panel:', xhr);
            $('#operationsOpenSessions').text('Error');
            $('#operationsCheckIns').html('<p class="text-danger text-center">Error loading data</p>');
            $('#operationsCheckOuts').html('<p class="text-danger text-center">Error loading data</p>');
        }
    });
}

// Initialize Visits DataTable
function initVisitsTable() {
    visitsTable = $('#visitsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.library.reports.daily_visits.data") }}',
            type: 'GET',
            data: function(d) {
                d.date = $('#filterDate').val();
                d.date_from = $('#filterDateFrom').val();
                d.date_to = $('#filterDateTo').val();
                d.session = $('#filterSession').val();
                d.visitor_type = $('#filterVisitorType').val();
                d.checked_in_by_staff_id = $('#filterCheckedInBy').val();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Ajax Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Loading Data',
                    text: 'Failed to load visits data. Please try again.'
                });
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'visit_id', name: 'visit_id', orderable: false },
            { data: 'visit_date', name: 'visit_date' },
            { data: 'check_in_time', name: 'check_in_time' },
            { data: 'check_out_time', name: 'check_out_time' },
            { data: 'session', name: 'session', orderable: false },
            { data: 'visitor_type', name: 'visitor_type', orderable: false },
            { data: 'visitor_name', name: 'visitor_name', orderable: false },
            { data: 'phone', name: 'phone', orderable: false },
            { data: 'purpose', name: 'purpose', orderable: false },
            { data: 'checked_in_by', name: 'checked_in_by', orderable: false },
            { data: 'checked_out_by', name: 'checked_out_by', orderable: false },
            { data: 'status', name: 'status', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, width: '120px' }
        ],
        order: [[2, 'desc']], // Sort by visit_date DESC
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-sm btn-secondary',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-success',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                },
                filename: 'Daily_Visits_Report_' + new Date().toISOString().slice(0, 10)
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-danger',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                },
                filename: 'Daily_Visits_Report_' + new Date().toISOString().slice(0, 10),
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Daily Visit Statistics Report - ' + new Date().toLocaleDateString()
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-sm btn-info',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                }
            }
        ],
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading visits...',
            emptyTable: 'No visits found.',
            zeroRecords: 'No matching visits found. Try adjusting your filters.'
        }
    });

    // Handle DataTables search
    $('#filterSearch').on('keyup', function() {
        visitsTable.search(this.value).draw();
    });
}

// Initialize Open Sessions DataTable
function initOpenSessionsTable() {
    openSessionsTable = $('#openSessionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.library.reports.daily_visits.open_sessions") }}',
            type: 'GET',
            data: function(d) {
                d.date = $('#filterDate').val();
                d.date_from = $('#filterDateFrom').val();
                d.date_to = $('#filterDateTo').val();
                d.session = $('#filterSession').val();
                d.visitor_type = $('#filterVisitorType').val();
                d.checked_in_by_staff_id = $('#filterCheckedInBy').val();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Ajax Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Loading Data',
                    text: 'Failed to load open sessions. Please try again.'
                });
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'visit_id', name: 'visit_id', orderable: false },
            { data: 'check_in_time', name: 'check_in_time' },
            { data: 'session', name: 'session', orderable: false },
            { data: 'visitor_type', name: 'visitor_type', orderable: false },
            { data: 'visitor_name', name: 'visitor_name', orderable: false },
            { data: 'purpose', name: 'purpose', orderable: false },
            { data: 'checked_in_by', name: 'checked_in_by', orderable: false },
            { data: 'duration', name: 'duration', orderable: false, className: 'text-end' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, width: '150px' }
        ],
        order: [[2, 'desc']], // Sort by check_in_time DESC
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-sm btn-secondary',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8]
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-success',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8]
                },
                filename: 'Open_Sessions_Report_' + new Date().toISOString().slice(0, 10)
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-danger',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8]
                },
                filename: 'Open_Sessions_Report_' + new Date().toISOString().slice(0, 10),
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Open Sessions Report - ' + new Date().toLocaleDateString()
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-sm btn-info',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8]
                }
            }
        ],
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading open sessions...',
            emptyTable: 'No open sessions found.',
            zeroRecords: 'No matching open sessions found. Try adjusting your filters.'
        }
    });
}

// Apply filters
function applyFilters() {
    visitsTable.ajax.reload(function() {
        // Reload KPI summary after table reload
        loadKPISummary();
    });
    
    // Reload open sessions table if tab is active
    if ($('#open-sessions-tab').hasClass('active')) {
        openSessionsTable.ajax.reload();
    }
}

// Reset filters
function resetFilters() {
    $('#filterDate').val('{{ \Carbon\Carbon::today()->format("Y-m-d") }}');
    $('#filterDateFrom').val('');
    $('#filterDateTo').val('');
    $('#filterSession').val('all');
    $('#filterVisitorType').val('all');
    $('#filterCheckedInBy').val('all');
    $('#filterSearch').val('');
    applyFilters();
}

// Refresh data
function refreshData() {
    visitsTable.ajax.reload();
    openSessionsTable.ajax.reload();
    loadKPISummary();
    loadTodayOperations();
    Swal.fire({
        icon: 'success',
        title: 'Refreshed',
        text: 'Data has been refreshed.',
        timer: 1500,
        showConfirmButton: false
    });
}

// View visit details
function viewVisit(visitId) {
    $.ajax({
        url: `/admin/library/visits/${visitId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const visit = response.data;
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Visit Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Visit ID:</th>
                                    <td>#${visit.id}</td>
                                </tr>
                                <tr>
                                    <th>Visit Date:</th>
                                    <td>${visit.visit_date || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Check-in Time:</th>
                                    <td>${visit.check_in_time || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Check-out Time:</th>
                                    <td>${visit.check_out_time || '<span class="badge bg-warning">Open</span>'}</td>
                                </tr>
                                <tr>
                                    <th>Session:</th>
                                    <td><span class="badge bg-info">${visit.session || 'N/A'}</span></td>
                                </tr>
                                <tr>
                                    <th>Purpose:</th>
                                    <td>${visit.purpose || 'N/A'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Visitor Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Visitor Type:</th>
                                    <td>${visit.visitor_type || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Visitor Name:</th>
                                    <td>${visit.visitor_name || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td>${visit.phone || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Checked-in By:</th>
                                    <td>${visit.checked_in_by || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Checked-out By:</th>
                                    <td>${visit.checked_out_by || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Note:</th>
                                    <td>${visit.note || 'N/A'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                `;
                $('#viewVisitContent').html(html);
                $('#viewVisitModal').modal('show');
            } else {
                Swal.fire('Error', response.message || 'Failed to load visit details.', 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load visit details.', 'error');
        }
    });
}

// Force checkout
function forceCheckout(visitId) {
    Swal.fire({
        title: 'Force Checkout?',
        text: 'Are you sure you want to force checkout this visit? This will set the check-out time to now.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, force checkout',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.library.reports.daily_visits.force_checkout", ":id") }}'.replace(':id', visitId),
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Checkout Successful',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Reload both tables and KPI
                        visitsTable.ajax.reload();
                        openSessionsTable.ajax.reload();
                        loadKPISummary();
                    } else {
                        Swal.fire('Error', response.message || 'Failed to force checkout.', 'error');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to force checkout.';
                    Swal.fire('Error', message, 'error');
                }
            });
        }
    });
}

// Start polling for open sessions (every 60 seconds)
function startOpenSessionsPolling() {
    // Only poll if open sessions tab is active
    if ($('#open-sessions-tab').hasClass('active')) {
        openSessionsTable.ajax.reload();
        loadKPISummary();
    }
    
    // Set interval
    if (openSessionsPollingInterval) {
        clearInterval(openSessionsPollingInterval);
    }
    
    openSessionsPollingInterval = setInterval(function() {
        // Only reload if open sessions tab is active
        if ($('#open-sessions-tab').hasClass('active')) {
            openSessionsTable.ajax.reload(null, false); // false = don't reset paging
            loadKPISummary();
        }
    }, 60000); // 60 seconds
}

// Stop polling when tab is switched away
$('#visitTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    const target = $(e.target).data('bs-target');
    if (target === '#open-sessions') {
        // Tab switched to open sessions - reload and start polling
        openSessionsTable.ajax.reload();
        loadKPISummary();
        startOpenSessionsPolling();
    }
});
</script>
@endpush

