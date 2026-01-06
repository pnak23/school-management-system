@extends('layouts.app')

@section('title', 'Library Guests')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-user-friends"></i> Library Guests</h2>
        </div>
        <div class="col-md-6 text-end">
            @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Add Guest
            </button>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-3">
            <label class="form-label">Status Filter</label>
            <select id="statusFilter" class="form-select">
                <option value="active">Active Only</option>
                <option value="all">All</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <!-- DataTable -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="guestsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Phone</th>
                            <th>ID Card No</th>
                            <th>Active</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('admin.library.guests._form')
@include('admin.library.guests._view')

<!-- Delete Options Modal (Admin Only) -->
<div class="modal fade" id="deleteOptionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash"></i> Delete Guest</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Choose how to delete this guest:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-warning" onclick="confirmSoftDelete()">
                        <i class="fas fa-toggle-off"></i> Soft Delete (Deactivate)
                        <br><small>Guest will be hidden but data preserved</small>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="confirmPermanentDelete()">
                        <i class="fas fa-trash-alt"></i> Permanent Delete
                        <br><small>⚠️ Guest will be permanently removed</small>
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let guestsTable;
let currentGuestId = null;
const canWrite = {{ auth()->user()->hasAnyRole(['admin', 'manager', 'staff']) ? 'true' : 'false' }};
const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};

$(document).ready(function() {
    loadGuests();
    initUserSelect2();

    // Filter change handler
    $('#statusFilter').on('change', function() {
        guestsTable.ajax.reload();
    });
});

// Load guests DataTable
function loadGuests() {
    guestsTable = $('#guestsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.library.guests.index") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'full_name', name: 'full_name' },
            { data: 'phone', name: 'phone' },
            { data: 'id_card_no', name: 'id_card_no' },
            { data: 'is_active', name: 'is_active', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[5, 'desc']],
        pageLength: 25,
        responsive: true
    });
}

// Open create modal
function openCreateModal() {
    $('#guestModalLabel').text('Add New Guest');
    $('#guestForm')[0].reset();
    $('#guestId').val('');
    
    // Reset user Select2
    if ($('#user_id').hasClass('select2-hidden-accessible')) {
        $('#user_id').val(null).trigger('change');
    }
    
    clearValidationErrors();
    $('#guestModal').modal('show');
}

// Open edit modal
function openEditModal(id) {
    $('#guestModalLabel').text('Edit Guest');
    clearValidationErrors();
    
    $.ajax({
        url: `/admin/library/guests/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const guest = response.data;
                $('#guestId').val(guest.id);
                $('#full_name').val(guest.full_name);
                $('#phone').val(guest.phone);
                $('#id_card_no').val(guest.id_card_no);
                $('#note').val(guest.note);
                
                // Set user Select2 value
                if ($('#user_id').hasClass('select2-hidden-accessible')) {
                    if (guest.user_id && guest.user) {
                        // Clear existing options and add the selected user
                        $('#user_id').empty();
                        const userOption = new Option(
                            guest.user.name + ' (' + guest.user.email + ')',
                            guest.user_id,
                            true,
                            true
                        );
                        $('#user_id').append(userOption).trigger('change');
                    } else {
                        $('#user_id').val(null).trigger('change');
                    }
                } else {
                    // If Select2 not initialized yet, just set the value
                    $('#user_id').val(guest.user_id || '');
                }
                
                $('#guestModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load guest data.', 'error');
        }
    });
}

// View guest details
function viewGuest(id) {
    $.ajax({
        url: `/admin/library/guests/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const guest = response.data;
                
                const statusBadge = guest.is_active 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-secondary">Inactive</span>';
                
                let html = `
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Full Name</th>
                                    <td>${guest.full_name}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>${guest.phone || '<span class="text-muted">N/A</span>'}</td>
                                </tr>
                                <tr>
                                    <th>ID Card No</th>
                                    <td>${guest.id_card_no || '<span class="text-muted">N/A</span>'}</td>
                                </tr>
                                <tr>
                                    <th>Note</th>
                                    <td>${guest.note || '<span class="text-muted">N/A</span>'}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>${statusBadge}</td>
                                </tr>
                                <tr>
                                    <th>Created By</th>
                                    <td>${guest.created_by} <small class="text-muted">(${guest.created_at})</small></td>
                                </tr>
                                <tr>
                                    <th>Updated By</th>
                                    <td>${guest.updated_by} <small class="text-muted">(${guest.updated_at})</small></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                `;
                
                $('#viewGuestContent').html(html);
                $('#viewGuestModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load guest details.', 'error');
        }
    });
}

// Save guest (create or update)
function saveGuest() {
    const id = $('#guestId').val();
    const url = id ? `/admin/library/guests/${id}` : '{{ route("admin.library.guests.store") }}';
    const method = id ? 'PUT' : 'POST';
    
    clearValidationErrors();
    
    const formData = {
        full_name: $('#full_name').val(),
        phone: $('#phone').val(),
        id_card_no: $('#id_card_no').val(),
        user_id: $('#user_id').val() || null,
        note: $('#note').val(),
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
                $('#guestModal').modal('hide');
                guestsTable.ajax.reload();
                Swal.fire('Success', response.message, 'success');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                displayValidationErrors(xhr.responseJSON.errors);
            } else {
                Swal.fire('Error', 'An error occurred while saving guest.', 'error');
            }
        }
    });
}

// Delete guest
function deleteGuest(id) {
    currentGuestId = id;
    
    if (isAdmin) {
        // Admin: show custom modal with 3 options
        $('#deleteOptionsModal').modal('show');
    } else {
        // Manager: simple confirm for soft delete
        Swal.fire({
            title: 'Deactivate Guest?',
            text: 'This will hide the guest but preserve data.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, deactivate'
        }).then((result) => {
            if (result.isConfirmed) {
                performSoftDelete(id);
            }
        });
    }
}

// Soft delete confirmation (from modal)
function confirmSoftDelete() {
    $('#deleteOptionsModal').modal('hide');
    performSoftDelete(currentGuestId);
}

// Permanent delete confirmation (from modal)
function confirmPermanentDelete() {
    $('#deleteOptionsModal').modal('hide');
    
    Swal.fire({
        title: 'Permanent Delete?',
        text: 'This action cannot be undone!',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, delete permanently',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            performPermanentDelete(currentGuestId);
        }
    });
}

// Perform soft delete
function performSoftDelete(id) {
    $.ajax({
        url: `/admin/library/guests/${id}`,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                guestsTable.ajax.reload();
                Swal.fire('Success', response.message, 'success');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to deactivate guest.', 'error');
        }
    });
}

// Perform permanent delete
function performPermanentDelete(id) {
    $.ajax({
        url: `/admin/library/guests/${id}/force-delete`,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                guestsTable.ajax.reload();
                Swal.fire('Success', response.message, 'success');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to delete guest permanently.', 'error');
        }
    });
}

// Toggle active status
function toggleGuestActive(id) {
    $.ajax({
        url: `/admin/library/guests/${id}/toggle`,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                guestsTable.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Updated',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', response.message, 'error');
                guestsTable.ajax.reload();
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to update status.', 'error');
            guestsTable.ajax.reload();
        }
    });
}

// Validation error helpers
function displayValidationErrors(errors) {
    for (const [field, messages] of Object.entries(errors)) {
        const input = $(`#${field}`);
        input.addClass('is-invalid');
        
        const feedback = input.siblings('.invalid-feedback');
        if (feedback.length) {
            feedback.text(messages[0]);
        } else {
            input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
        }
    }
}

function clearValidationErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

// Initialize Select2 for user dropdown with AJAX search
function initUserSelect2() {
    // Wait for Select2 to load
    function initializeSelect2() {
        if (typeof $.fn.select2 === 'undefined') {
            console.log('⏳ Waiting for Select2 to load...');
            setTimeout(initializeSelect2, 100);
            return;
        }

        console.log('✅ Initializing Select2 for user dropdown...');

        // Initialize Select2 for user selection with AJAX search
        $('#user_id').select2({
            dropdownParent: $('#guestModal'),
            placeholder: 'Search for a user...',
            width: '100%',
            allowClear: true,
            ajax: {
                url: '{{ route("admin.library.guests.search-users") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term || '',
                        page: params.page || 1
                    };
                },
                processResults: function(data) {
                    if (data.success && data.results) {
                        return {
                            results: data.results.map(user => ({
                                id: user.id,
                                text: user.text
                            })),
                            pagination: {
                                more: data.pagination ? data.pagination.more : false
                            }
                        };
                    }
                    return { results: [] };
                },
                cache: true
            },
            minimumInputLength: 0, // Allow loading users without typing
            theme: 'bootstrap-5'
        });
    }

    // Start initialization
    initializeSelect2();
}
</script>

<style>
.is-invalid {
    border-color: #dc3545 !important;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>
@endpush

