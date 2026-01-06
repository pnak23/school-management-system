@extends('layouts.app')

@section('title', 'Activity Logs - School Management System')

@push('styles')
<!-- DataTables Bootstrap 5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .modal-custom-width {
        max-width: 800px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Activity Logs</h1>
            <p class="text-gray-600 mt-1">Track all system activities and changes</p>
        </div>
        @if(auth()->check() && auth()->user()->hasRole('admin'))
        <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
                <button class="btn btn-danger dropdown-toggle" type="button" id="clearLogsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-trash-alt"></i> Clear Logs
                </button>
                <ul class="dropdown-menu" aria-labelledby="clearLogsDropdown">
                    <li><a class="dropdown-item" href="#" onclick="clearLogs('week'); return false;">
                        <i class="fas fa-calendar-week"></i> Older than 1 Week
                        <span class="badge bg-secondary ms-2" id="count-week">-</span>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="clearLogs('month'); return false;">
                        <i class="fas fa-calendar-alt"></i> Older than 1 Month
                        <span class="badge bg-secondary ms-2" id="count-month">-</span>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="clearLogs('3months'); return false;">
                        <i class="fas fa-calendar"></i> Older than 3 Months
                        <span class="badge bg-secondary ms-2" id="count-3months">-</span>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="clearLogs('6months'); return false;">
                        <i class="fas fa-calendar-check"></i> Older than 6 Months
                        <span class="badge bg-secondary ms-2" id="count-6months">-</span>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="clearLogs('year'); return false;">
                        <i class="fas fa-calendar-day"></i> Older than 1 Year
                        <span class="badge bg-secondary ms-2" id="count-year">-</span>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="clearLogs('all'); return false;">
                        <i class="fas fa-exclamation-triangle"></i> Delete All Logs
                        <span class="badge bg-danger ms-2" id="count-all">-</span>
                    </a></li>
                </ul>
            </div>
        </div>
        @endif
    </div>

    @if(auth()->check() && auth()->user()->hasRole('admin'))
    <div class="alert alert-info mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong><i class="fas fa-info-circle"></i> Log Statistics:</strong>
                <span class="ms-2">Total: <strong id="stat-total">-</strong></span>
                <span class="ms-3">Last Week: <strong id="stat-last-week">-</strong></span>
                <span class="ms-3">Last Month: <strong id="stat-last-month">-</strong></span>
            </div>
            <button class="btn btn-sm btn-outline-primary" onclick="loadStats()">
                <i class="fas fa-sync-alt"></i> Refresh Stats
            </button>
        </div>
    </div>
    @endif

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-6">
            <div class="table-responsive">
                <table class="table table-bordered data-table" id="activityLogTable">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Description</th>
                            <th>User</th>
                            <th>Subject</th>
                            <th>Properties</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Properties Modal -->
<div class="modal fade" id="propertiesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-custom-width">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Properties Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul id="propertiesList" class="list-unstyled"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Moment.js (Optional, for time formatting) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<script type="text/javascript">
// CSRF Token setup for AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Global variable for DataTable
var activityLogTable;

@if(auth()->check() && auth()->user()->hasRole('admin'))
// Load statistics function (admin only)
function loadStats() {
    $.get("{{ route('logs.stats') }}", function(response) {
        if (response.success) {
            var stats = response.data;
            $('#stat-total').text(stats.total.toLocaleString());
            $('#stat-last-week').text(stats.last_week.toLocaleString());
            $('#stat-last-month').text(stats.last_month.toLocaleString());
            
            // Update counts in dropdown
            $('#count-week').text(stats.older_than_week.toLocaleString());
            $('#count-month').text(stats.older_than_month.toLocaleString());
            $('#count-3months').text(stats.older_than_3months.toLocaleString());
            $('#count-6months').text(stats.older_than_6months.toLocaleString());
            $('#count-year').text(stats.older_than_year.toLocaleString());
            $('#count-all').text(stats.total.toLocaleString());
        }
    }).fail(function() {
        console.error('Failed to load statistics');
    });
}

// Clear logs by period function (admin only)
function clearLogs(period) {
    var periodNames = {
        'week': '1 Week',
        'month': '1 Month',
        '3months': '3 Months',
        '6months': '6 Months',
        'year': '1 Year',
        'all': 'ALL LOGS'
    };
    
    var periodName = periodNames[period] || period;
    var confirmMessage = period === 'all' 
        ? 'Are you sure you want to delete ALL activity logs? This action cannot be undone!'
        : 'Are you sure you want to delete logs older than ' + periodName + '? This action cannot be undone!';
    
    Swal.fire({
        title: 'Confirm Deletion',
        text: confirmMessage,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        input: period === 'all' ? 'text' : null,
        inputPlaceholder: period === 'all' ? 'Type "povmuny" to confirm' : null,
        inputValidator: function(value) {
            if (period === 'all' && value !== 'povmuny') {
                return 'You must type "povmuny" to confirm';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while logs are being deleted.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Get CSRF token
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            if (!csrfToken) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'CSRF token not found. Please refresh the page.'
                });
                return;
            }
            
            $.ajax({
                url: "{{ route('logs.delete-by-period') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: {
                    _token: csrfToken,
                    period: period
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Logs deleted successfully.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload table and stats
                        if (activityLogTable) {
                            activityLogTable.ajax.reload();
                        }
                        loadStats();
                    });
                },
                error: function(xhr) {
                    var errorMessage = 'Failed to delete logs.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                }
            });
        }
    });
}
@endif

$(document).ready(function() {
    // Initialize DataTable
    activityLogTable = $('#activityLogTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('logs.index') }}",
        columns: [
            { 
                data: 'DT_RowIndex', 
                name: 'DT_RowIndex', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'description', name: 'activity_log.description' },
            { data: 'user_name', name: 'user_name', orderable: false },
            { data: 'subject', name: 'subject', orderable: false },
            { data: 'properties', name: 'properties', orderable: false, searchable: false },
            { data: 'date', name: 'activity_log.created_at' },
        ],
        order: [[5, 'desc']],
        responsive: true,
        pageLength: 25,
        language: {
            processing: "Processing...",
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            loadingRecords: "Loading...",
            zeroRecords: "No matching records found",
            emptyTable: "No data available in table",
            paginate: {
                first: "First",
                previous: "Previous",
                next: "Next",
                last: "Last"
            }
        }
    });

    // View Properties
    $(document).on('click', '.viewProperties', function () {
        var activity_id = $(this).data('id');
        $.get("{{ route('logs.show', ':id') }}".replace(':id', activity_id), function (data) {
            $('#propertiesList').empty();
            
            if (Object.keys(data).length === 0) {
                $('#propertiesList').append('<li class="text-muted">No properties available</li>');
            } else {
                $.each(data, function(key, value) {
                    var keyTranslations = {
                        'attributes': 'New Data',
                        'old': 'Old Data',
                    };
                    var displayKey = keyTranslations[key] || key;
                    
                    var formattedValue = typeof value === 'object' ? JSON.stringify(value, null, 2) : value;
                    var property = $('<li class="mb-3"/>')
                        .html('<strong class="text-primary">' + displayKey + ':</strong><br><pre class="bg-light p-2 rounded mt-1" style="white-space: pre-wrap; word-wrap: break-word;">' + formattedValue + '</pre>');
                    $('#propertiesList').append(property);
                });
            }
            $('#propertiesModal').modal('show');
        }).fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load properties'
            });
        });
    });

    // Load stats on page load (admin only)
    @if(auth()->check() && auth()->user()->hasRole('admin'))
    loadStats();
    @endif
});
</script>
@endpush

