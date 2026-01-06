@extends('layouts.app')

@section('title', 'Library Fines Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-money-bill-wave text-warning"></i> Library Fines Management</h2>
        </div>
        <div class="col-md-6 text-end">
            @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Add Fine
            </button>
            @endif
        </div>
    </div>

    <!-- Dashboard Statistics Cards -->
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-list"></i> Total Fines
                            </h6>
                            <h2 class="mb-0 mt-2" id="statTotal">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
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
                                <i class="fas fa-exclamation-circle"></i> Unpaid Fines
                            </h6>
                            <h2 class="mb-0 mt-2" id="statUnpaid">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                            <small id="statUnpaidAmount" class="d-block mt-1"></small>
                        </div>
                        <div>
                            <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
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
                                <i class="fas fa-check-circle"></i> Paid Fines
                            </h6>
                            <h2 class="mb-0 mt-2" id="statPaid">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                            <small id="statPaidAmount" class="d-block mt-1"></small>
                        </div>
                        <div>
                            <i class="fas fa-check fa-3x opacity-50"></i>
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
                                <i class="fas fa-ban"></i> Waived Fines
                            </h6>
                            <h2 class="mb-0 mt-2" id="statWaived">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-hand-holding-usd fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <small class="d-block mt-2">
                        <button type="button" class="btn btn-sm btn-light" onclick="fetchFinesStats()" title="Refresh Stats">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Type, Note, User...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="filterStatus" class="form-select">
                        <option value="all">All Status</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                        <option value="waived">Waived</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fine Type</label>
                    <select id="filterFineType" class="form-select">
                        <option value="all">All Types</option>
                        <option value="overdue">Overdue</option>
                        <option value="lost">Lost</option>
                        <option value="damaged">Damaged</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Assessed From</label>
                    <input type="date" id="filterAssessedFrom" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Assessed To</label>
                    <input type="date" id="filterAssessedTo" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Active Status</label>
                    <select id="filterActive" class="form-select">
                        <option value="active">Active</option>
                        <option value="all">All</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTable -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="finesTable" class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Loan</th>
                            <th>User</th>
                            <th>Fine Type</th>
                            <th>Amount</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Assessed At</th>
                            <th>Paid At</th>
                            <th>Active</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('admin.library.fines._form')
@include('admin.library.fines._pay_modal')

@endsection

@push('styles')
<style>
    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }
    
    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    
    .form-control.is-invalid:focus,
    .form-select.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }
    
    .is-invalid ~ .invalid-feedback {
        display: block;
    }
</style>
@endpush

@push('scripts')
<script>
let finesTable;
const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};
const canWrite = {{ auth()->user()->hasAnyRole(['admin', 'manager', 'staff']) ? 'true' : 'false' }};
const canDelete = {{ auth()->user()->hasAnyRole(['admin', 'manager']) ? 'true' : 'false' }};

// CSRF Token setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    // Initialize DataTable
    loadFines();
    fetchFinesStats();

    // Filter change handlers
    $('#searchInput').on('keyup', debounce(function() {
        finesTable.search(this.value).draw();
    }, 500));

    $('#filterStatus, #filterFineType, #filterActive, #filterAssessedFrom, #filterAssessedTo').on('change', function() {
        finesTable.ajax.reload();
    });
});

// Load fines DataTable
function loadFines() {
    finesTable = $('#finesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.library.fines.index") }}',
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.fine_type = $('#filterFineType').val();
                d.assessed_from = $('#filterAssessedFrom').val();
                d.assessed_to = $('#filterAssessedTo').val();
                d.is_active = $('#filterActive').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'loan_info', name: 'loan_id' },
            { data: 'user_info', name: 'user.name' },
            { data: 'fine_type_badge', name: 'fine_type', orderable: false },
            { data: 'amount_display', name: 'amount' },
            { data: 'paid_display', name: 'paid_amount' },
            { data: 'balance_display', name: 'balance', orderable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'assessed_at', name: 'assessed_at' },
            { data: 'paid_at', name: 'paid_at' },
            { data: 'active_badge', name: 'is_active', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[8, 'desc']], // Order by assessed_at desc
        pageLength: 25,
        responsive: true,
        language: {
            processing: "កំពុងដំណើរការ...",
            search: "ស្វែងរក:",
            lengthMenu: "បង្ហាញ _MENU_ ទិន្នន័យ",
            info: "បង្ហាញ _START_ ដល់ _END_ នៃ _TOTAL_ ទិន្នន័យ",
            infoEmpty: "បង្ហាញ 0 ដល់ 0 នៃ 0 ទិន្នន័យ",
            infoFiltered: "(ស្វែងរកពីទិន្នន័យសរុប _MAX_)",
            loadingRecords: "កំពុងផ្ទុក...",
            zeroRecords: "គ្មានទិន្នន័យ",
            emptyTable: "គ្មានទិន្នន័យក្នុងតារាង",
            paginate: {
                first: "ដំបូង",
                previous: "ថយក្រោយ",
                next: "បន្ទាប់",
                last: "ចុងក្រោយ"
            }
        }
    });
}

// Fetch fines statistics
function fetchFinesStats() {
    $.ajax({
        url: '{{ route("admin.library.fines.stats") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#statTotal').html(data.total || 0);
                $('#statUnpaid').html(data.unpaid || 0);
                $('#statUnpaidAmount').html(formatCurrency(data.unpaid_amount));
                $('#statPaid').html(data.paid || 0);
                $('#statPaidAmount').html(formatCurrency(data.total_paid));
                $('#statWaived').html(data.waived || 0);
            } else {
                console.error('Stats response error:', response);
                $('#statTotal').html('<small class="text-danger">Error</small>');
                $('#statUnpaid').html('<small class="text-danger">Error</small>');
                $('#statPaid').html('<small class="text-danger">Error</small>');
                $('#statWaived').html('<small class="text-danger">Error</small>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to fetch fines stats:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            $('#statTotal').html('<small class="text-danger">Error</small>');
            $('#statUnpaid').html('<small class="text-danger">Error</small>');
            $('#statPaid').html('<small class="text-danger">Error</small>');
            $('#statWaived').html('<small class="text-danger">Error</small>');
        }
    });
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US').format(amount || 0) + ' ៛';
}

// Clear filters
function clearFilters() {
    $('#searchInput').val('');
    $('#filterStatus').val('all');
    $('#filterFineType').val('all');
    $('#filterAssessedFrom').val('');
    $('#filterAssessedTo').val('');
    $('#filterActive').val('active');
    finesTable.search('').ajax.reload();
}

// Debounce function
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

// Open create modal
function openCreateModal() {
    $('#fineForm')[0].reset();
    $('#fineId').val('');
    $('#fineModalLabel').text('Add New Fine');
    $('#paid_amount').val('0');
    $('#status').val('unpaid');
    
    // Clear validation errors
    clearValidationErrors();
    
    // Set default assessed_at to now
    const now = new Date();
    const dateStr = now.toISOString().slice(0, 16);
    $('#assessed_at').val(dateStr);
    
    $('#fineModal').modal('show');
}

// Open edit modal
function openEditModal(id) {
    // Clear validation errors
    clearValidationErrors();
    
    $.ajax({
        url: `/admin/library/fines/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const fine = response.data;
                
                $('#fineId').val(fine.id);
                $('#loan_id').val(fine.loan_id);
                $('#user_id').val(fine.user_id);
                $('#fine_type').val(fine.fine_type);
                $('#amount').val(fine.amount);
                $('#paid_amount').val(fine.paid_amount);
                $('#status').val(fine.status);
                $('#assessed_at').val(fine.assessed_at ? fine.assessed_at.slice(0, 16) : '');
                $('#paid_at').val(fine.paid_at ? fine.paid_at.slice(0, 16) : '');
                $('#note').val(fine.note);
                
                $('#fineModalLabel').text('Edit Fine');
                $('#fineModal').modal('show');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load fine details.', 'error');
        }
    });
}

// Clear validation errors
function clearValidationErrors() {
    // Remove is-invalid class from all inputs
    $('.form-control, .form-select').removeClass('is-invalid');
    
    // Clear all error messages
    $('.invalid-feedback').text('').hide();
}

// Display validation errors on form fields
function displayValidationErrors(errors) {
    clearValidationErrors();
    
    for (const field in errors) {
        // Add is-invalid class to the field
        $(`#${field}`).addClass('is-invalid');
        
        // Display error message
        const errorElement = $(`#${field}_error`);
        if (errorElement.length) {
            errorElement.text(errors[field][0]).show();
        }
    }
}

// Save fine (create or update)
function saveFine() {
    // Clear previous validation errors
    clearValidationErrors();
    
    const fineId = $('#fineId').val();
    const isEdit = fineId !== '';
    const url = isEdit ? `/admin/library/fines/${fineId}` : '/admin/library/fines';
    const method = isEdit ? 'PUT' : 'POST';

    const formData = {
        loan_id: $('#loan_id').val(),
        user_id: $('#user_id').val(),
        fine_type: $('#fine_type').val(),
        amount: $('#amount').val(),
        paid_amount: $('#paid_amount').val() || 0,
        status: $('#status').val(),
        assessed_at: $('#assessed_at').val(),
        paid_at: $('#paid_at').val() || null,
        note: $('#note').val()
    };

    $.ajax({
        url: url,
        type: method,
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#fineModal').modal('hide');
                Swal.fire('Success', response.message, 'success');
                finesTable.ajax.reload();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                // Display validation errors inline on form
                displayValidationErrors(xhr.responseJSON.errors);
                
                // Also show summary in SweetAlert
                const errors = xhr.responseJSON.errors;
                let errorList = '<div class="text-start"><strong>Please fix the following errors:</strong><ul>';
                
                for (const field in errors) {
                    const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    errors[field].forEach(error => {
                        errorList += `<li>${fieldName}: ${error}</li>`;
                    });
                }
                
                errorList += '</ul></div>';
                
                Swal.fire({
                    title: 'Validation Error',
                    html: errorList,
                    icon: 'error',
                    width: '500px',
                    confirmButtonText: 'Fix Errors'
                });
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                // Server error message
                Swal.fire('Error', xhr.responseJSON.message, 'error');
            } else {
                // Generic error
                Swal.fire('Error', 'An error occurred. Please check your input and try again.', 'error');
            }
        }
    });
}

// View fine details
function viewFine(id) {
    $.ajax({
        url: `/admin/library/fines/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const fine = response.data;
                
                let html = `
                    <table class="table table-sm">
                        <tr><th>ID:</th><td>#${fine.id}</td></tr>
                        <tr><th>Loan:</th><td>${fine.loan_info || 'N/A'}</td></tr>
                        <tr><th>Book:</th><td>${fine.book_title || 'N/A'}</td></tr>
                        <tr><th>Borrower:</th><td>${fine.borrower_name || 'N/A'}</td></tr>
                        <tr><th>User:</th><td>${fine.user_name || 'N/A'} (${fine.user_email || 'N/A'})</td></tr>
                        <tr><th>Fine Type:</th><td><span class="badge bg-info">${fine.fine_type}</span></td></tr>
                        <tr><th>Amount:</th><td><strong>${Number(fine.amount).toLocaleString()} ៛</strong></td></tr>
                        <tr><th>Paid:</th><td>${Number(fine.paid_amount).toLocaleString()} ៛</td></tr>
                        <tr><th>Balance:</th><td class="text-${fine.balance > 0 ? 'danger' : 'success'}"><strong>${Number(fine.balance).toLocaleString()} ៛</strong></td></tr>
                        <tr><th>Status:</th><td><span class="badge bg-${fine.status === 'paid' ? 'success' : fine.status === 'waived' ? 'info' : 'danger'}">${fine.status.toUpperCase()}</span></td></tr>
                        <tr><th>Assessed At:</th><td>${fine.assessed_at || 'N/A'}</td></tr>
                        <tr><th>Paid At:</th><td>${fine.paid_at || 'N/A'}</td></tr>
                        <tr><th>Note:</th><td>${fine.note || '-'}</td></tr>
                        <tr><th>Active:</th><td><span class="badge bg-${fine.is_active ? 'success' : 'secondary'}">${fine.is_active ? 'Active' : 'Inactive'}</span></td></tr>
                        <tr><th>Created By:</th><td>${fine.created_by || 'N/A'} <small>(${fine.created_at})</small></td></tr>
                        <tr><th>Updated By:</th><td>${fine.updated_by || 'N/A'} <small>(${fine.updated_at})</small></td></tr>
                    </table>
                `;

                // Add action buttons
                if (canWrite && fine.balance > 0) {
                    html += `
                        <div class="text-end mt-3">
                            <button class="btn btn-success btn-sm" onclick="Swal.close(); openPayModal(${fine.id});">
                                <i class="fas fa-dollar-sign"></i> Pay Now
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="Swal.close(); waiveFine(${fine.id});">
                                <i class="fas fa-ban"></i> Waive Fine
                            </button>
                        </div>
                    `;
                }

                Swal.fire({
                    title: 'Fine Details',
                    html: html,
                    width: '600px',
                    showCloseButton: true,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load fine details.', 'error');
        }
    });
}

// Toggle fine active status
function toggleFineActive(id) {
    Swal.fire({
        title: 'Toggle Status',
        text: 'Are you sure you want to toggle this fine\'s active status?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, toggle it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/library/fines/${id}/toggle`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success');
                        finesTable.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to toggle status.', 'error');
                }
            });
        }
    });
}

// Delete fine
function deleteFine(id) {
    if (isAdmin) {
        // Admin modal with 3 options
        Swal.fire({
            title: 'Delete Fine',
            html: `
                <p class="mb-3">Choose how to delete this fine:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-warning" onclick="performSoftDelete(${id})">
                        <i class="fas fa-eye-slash"></i> Soft Delete (Deactivate)
                    </button>
                    <button class="btn btn-danger" onclick="performForceDelete(${id})">
                        <i class="fas fa-trash"></i> Permanent Delete
                    </button>
                    <button class="btn btn-secondary" onclick="Swal.close()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            width: '400px'
        });
    } else {
        // Manager: simple confirm for soft delete
        Swal.fire({
            title: 'Delete Fine',
            text: 'Are you sure you want to delete (deactivate) this fine?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                performSoftDelete(id);
            }
        });
    }
}

// Perform soft delete
function performSoftDelete(id) {
    $.ajax({
        url: `/admin/library/fines/${id}`,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.close();
            if (response.success) {
                Swal.fire('Deleted', response.message, 'success');
                finesTable.ajax.reload();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to delete fine.', 'error');
        }
    });
}

// Perform permanent delete (admin only)
function performForceDelete(id) {
    Swal.fire({
        title: 'Permanent Delete',
        text: 'This action cannot be undone. Are you absolutely sure?',
        icon: 'error',
        input: 'text',
        inputPlaceholder: 'Type DELETE to confirm',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete permanently',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        preConfirm: (value) => {
            if (value !== 'DELETE') {
                Swal.showValidationMessage('Please type DELETE to confirm');
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/library/fines/${id}/force-delete`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted', response.message, 'success');
                        finesTable.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to delete fine permanently.', 'error');
                }
            });
        }
    });
}
</script>
@endpush

