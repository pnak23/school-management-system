@extends('layouts.app')

@section('title', 'Library Copies (Barcode)')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-barcode"></i> Book Copies (Barcode)</h2>
        </div>
        <div class="col-md-6 text-end">
            @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Add Copy
            </button>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Barcode, Call Number, Title, ISBN...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="available">Available</option>
                        <option value="on_loan">On Loan</option>
                        <option value="reserved">Reserved</option>
                        <option value="lost">Lost</option>
                        <option value="damaged">Damaged</option>
                        <option value="withdrawn">Withdrawn</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Shelf</label>
                    <select id="shelfFilter" class="form-select">
                        <option value="all">All Shelves</option>
                        @foreach($shelves as $shelf)
                            <option value="{{ $shelf->id }}">{{ $shelf->code ?? $shelf->location }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Book</label>
                    <select id="itemFilter" class="form-select">
                        <option value="all">All Books</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ Str::limit($item->title, 40) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Active Status</label>
                    <select id="activeFilter" class="form-select">
                        <option value="active">Active Only</option>
                        <option value="all">All Records</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTable -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="copiesTable" class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Barcode</th>
                            <th>Call Number</th>
                            <th>Book Title</th>
                            <th>Shelf</th>
                            <th>Acquired</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th>Active</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include Form Modal -->
@include('admin.library.copies._form')

<!-- Delete Confirmation Modal (Admin) -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Copy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Choose delete option:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-warning" onclick="performSoftDelete()">
                        <i class="fas fa-ban"></i> Deactivate (Soft Delete)
                    </button>
                    <button type="button" class="btn btn-danger" onclick="performForceDelete()">
                        <i class="fas fa-trash"></i> Permanent Delete
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Copy Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Copy Details</h5>
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

@push('scripts')
<script>
let copiesTable;
let currentDeleteId = null;
const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};

$(document).ready(function() {
    loadCopies();

    // Filter change handlers
    $('#searchInput').on('keyup', debounce(function() {
        copiesTable.search(this.value).draw();
    }, 500));

    $('#statusFilter, #shelfFilter, #itemFilter, #activeFilter').on('change', function() {
        copiesTable.ajax.reload();
    });
});

// Load copies DataTable
function loadCopies() {
    copiesTable = $('#copiesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.library.copies.index") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.shelf_id = $('#shelfFilter').val();
                d.library_item_id = $('#itemFilter').val();
                d.is_active = $('#activeFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'barcode', name: 'barcode' },
            { data: 'call_number', name: 'call_number', defaultContent: '-' },
            { data: 'item_info', name: 'item.title', orderable: false },
            { data: 'shelf_location', name: 'shelf.code', orderable: false },
            { data: 'acquired_date', name: 'acquired_date', defaultContent: '-' },
            { data: 'condition_badge', name: 'condition', orderable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'active_badge', name: 'is_active', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        pageLength: 25,
        responsive: true
    });
}

// Open create modal
function openCreateModal() {
    $('#copyForm')[0].reset();
    $('#copyId').val('');
    $('#change_note').val('');
    $('#changeNoteContainer').hide(); // Hide change note for new copies
    $('#formModalLabel').text('Add Book Copy');
    $('#formModal').modal('show');
}

// Open edit modal
function openEditModal(id) {
    $.ajax({
        url: `/admin/library/copies/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const copy = response.data;
                $('#copyId').val(copy.id);
                $('#library_item_id').val(copy.library_item_id);
                $('#barcode').val(copy.barcode);
                $('#call_number').val(copy.call_number);
                $('#shelf_id').val(copy.shelf_id);
                $('#acquired_date').val(copy.acquired_date);
                $('#condition').val(copy.condition);
                $('#status').val(copy.status);
                $('#change_note').val('');
                $('#changeNoteContainer').show(); // Show change note for edits
                $('#formModalLabel').text('Edit Book Copy');
                $('#formModal').modal('show');
                
                // Store original values to detect changes
                $('#status').data('original', copy.status);
                $('#condition').data('original', copy.condition);
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load copy data.', 'error');
        }
    });
}

// Save copy (create or update)
function saveCopy() {
    const id = $('#copyId').val();
    const url = id ? `/admin/library/copies/${id}` : '{{ route("admin.library.copies.store") }}';
    const method = id ? 'PUT' : 'POST';

    const formData = {
        library_item_id: $('#library_item_id').val(),
        barcode: $('#barcode').val(),
        call_number: $('#call_number').val(),
        shelf_id: $('#shelf_id').val(),
        acquired_date: $('#acquired_date').val(),
        condition: $('#condition').val(),
        status: $('#status').val(),
        change_note: $('#change_note').val() // Include change note for history
    };

    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();

    $.ajax({
        url: url,
        type: method,
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#formModal').modal('hide');
                Swal.fire('Success', response.message, 'success');
                copiesTable.ajax.reload();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    const input = $(`#${field}`);
                    input.addClass('is-invalid');
                    input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                });
            } else {
                const message = xhr.responseJSON?.message || 'Failed to save copy.';
                Swal.fire('Error', message, 'error');
            }
        }
    });
}

// Toggle copy active status
function toggleCopyActive(id) {
    Swal.fire({
        title: 'Toggle Status',
        text: 'Are you sure you want to toggle this copy status?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, toggle it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/library/copies/${id}/toggle-status`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success');
                        copiesTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to toggle status.';
                    Swal.fire('Error', message, 'error');
                }
            });
        }
    });
}

// Delete copy
function deleteCopy(id) {
    currentDeleteId = id;

    if (isAdmin) {
        // Admin: show modal with soft/permanent options
        $('#deleteModal').modal('show');
    } else {
        // Manager: simple confirm for soft delete
        Swal.fire({
            title: 'Deactivate Copy',
            text: 'Are you sure you want to deactivate this copy?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, deactivate',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ffc107'
        }).then((result) => {
            if (result.isConfirmed) {
                performSoftDelete();
            }
        });
    }
}

// Perform soft delete
function performSoftDelete() {
    if (!currentDeleteId) return;

    $.ajax({
        url: `/admin/library/copies/${currentDeleteId}`,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#deleteModal').modal('hide');
                Swal.fire('Success', response.message, 'success');
                copiesTable.ajax.reload();
                currentDeleteId = null;
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Failed to deactivate copy.';
            Swal.fire('Error', message, 'error');
        }
    });
}

// Perform permanent delete (admin only)
function performForceDelete() {
    if (!currentDeleteId) return;

    Swal.fire({
        title: 'Permanent Delete',
        text: 'This action cannot be undone! Are you sure?',
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete permanently',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/library/copies/${currentDeleteId}/force-delete`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#deleteModal').modal('hide');
                        Swal.fire('Deleted', response.message, 'success');
                        copiesTable.ajax.reload();
                        currentDeleteId = null;
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to delete copy permanently.';
                    Swal.fire('Error', message, 'error');
                }
            });
        }
    });
}

// View copy details
function viewCopy(id) {
    $.ajax({
        url: `/admin/library/copies/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const copy = response.data;
                const item = copy.item || {};
                const shelf = copy.shelf || {};
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Barcode:</strong> ${copy.barcode || 'N/A'}</p>
                            <p><strong>Call Number:</strong> ${copy.call_number || 'N/A'}</p>
                            <p><strong>Book Title:</strong> ${item.title || 'N/A'}</p>
                            <p><strong>ISBN:</strong> ${item.isbn || 'N/A'}</p>
                            <p><strong>Edition:</strong> ${item.edition || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Shelf:</strong> ${shelf.code || shelf.location || 'N/A'}</p>
                            <p><strong>Acquired Date:</strong> ${copy.acquired_date || 'N/A'}</p>
                            <p><strong>Condition:</strong> <span class="badge bg-primary">${copy.condition || 'N/A'}</span></p>
                            <p><strong>Status:</strong> <span class="badge bg-success">${copy.status || 'N/A'}</span></p>
                            <p><strong>Active:</strong> ${copy.is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</p>
                        </div>
                    </div>
                `;
                
                $('#viewContent').html(html);
                $('#viewModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load copy details.', 'error');
        }
    });
}

// View copy status/condition change history
function viewHistory(id) {
    $.ajax({
        url: `/admin/library/copies/${id}/history`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const copy = response.data.copy;
                const history = response.data.history;
                
                let html = `
                    <div class="mb-3">
                        <h6 class="text-primary">Current Status</h6>
                        <p>
                            <strong>Barcode:</strong> ${copy.barcode}<br>
                            <strong>Status:</strong> <span class="badge bg-success">${copy.current_status}</span><br>
                            <strong>Condition:</strong> <span class="badge bg-primary">${copy.current_condition || 'N/A'}</span>
                        </p>
                    </div>
                    <hr>
                    <h6 class="text-secondary">Change History (Last 20 Records)</h6>
                `;
                
                if (history.length === 0) {
                    html += '<p class="text-muted">No history records found.</p>';
                } else {
                    html += '<div class="list-group">';
                    history.forEach(function(record) {
                        html += `
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${record.action}</h6>
                                    <small class="text-muted">${record.changed_at}</small>
                                </div>
                                <p class="mb-1">${record.change_summary}</p>
                                ${record.note ? `<small class="text-info"><i class="fas fa-sticky-note"></i> ${record.note}</small><br>` : ''}
                                <small class="text-muted"><i class="fas fa-user"></i> ${record.changed_by}</small>
                            </div>
                        `;
                    });
                    html += '</div>';
                }
                
                Swal.fire({
                    title: 'Copy History',
                    html: html,
                    width: '700px',
                    confirmButtonText: 'Close',
                    customClass: {
                        container: 'history-modal'
                    }
                });
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load history.', 'error');
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
</script>
@endpush

