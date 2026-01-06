@extends('layouts.app')

@section('title', 'Library Reservations (Book Hold/Queue)')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-bookmark"></i> Library Reservations</h2>
            <p class="text-muted">Manage book reservations and queue system</p>
        </div>
        <div class="col-md-6 text-end">
            @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff', 'user']))
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> New Reservation
            </button>
            @endif
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filters
        </div>
        <div class="card-body">
            <form id="filterForm">
                <div class="row g-2">
                    <!-- Status Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="filter_status" name="status">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="ready">Ready</option>
                            <option value="fulfilled">Fulfilled</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="expired">Expired (Ready)</option>
                        </select>
                    </div>

                    <!-- Item Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Book</label>
                        <select class="form-select" id="filter_library_item_id" name="library_item_id">
                            <option value="all">All Books</option>
                            @foreach($libraryItems as $item)
                                <option value="{{ $item->id }}">{{ $item->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" id="filter_date_from" name="date_from">
                    </div>

                    <!-- Date To -->
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" id="filter_date_to" name="date_to">
                    </div>

                    <!-- Filter Buttons -->
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary me-2" onclick="applyFilters()">
                            <i class="fas fa-search"></i> Apply
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="reservationsTable" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Queue</th>
                            <th>User</th>
                            <th>Book Title</th>
                            <th>Status</th>
                            <th>Reserved At</th>
                            <th>Expires At</th>
                            <th>Assigned Copy</th>
                            <th>Actions</th>
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

<!-- Create Reservation Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> New Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm">
                @csrf
                <div class="modal-body">
                    <!-- User Selection -->
                    <div class="mb-3">
                        <label class="form-label">User <span class="text-danger">*</span></label>
                        <select class="form-select" id="create_user_id" name="user_id" required>
                            <option value="">Search for a user...</option>
                        </select>
                        <small class="text-muted">Start typing to search users</small>
                    </div>

                    <!-- Book Selection -->
                    <div class="mb-3">
                        <label class="form-label">Book <span class="text-danger">*</span></label>
                        <select class="form-select" id="create_library_item_id" name="library_item_id" required>
                            <option value="">Search for a book...</option>
                        </select>
                        <small class="text-muted">Start typing to search books</small>
                    </div>

                    <!-- Note -->
                    <div class="mb-3">
                        <label class="form-label">Note (Optional)</label>
                        <textarea class="form-control" id="create_note" name="note" rows="3" placeholder="Enter any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Copy Modal -->
<div class="modal fade" id="assignCopyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Assign Copy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignCopyForm">
                @csrf
                <input type="hidden" id="assign_reservation_id" name="reservation_id">
                <input type="hidden" id="assign_library_item_id" name="library_item_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Book:</strong> <span id="assign_book_title"></span><br>
                        <strong>User:</strong> <span id="assign_user_name"></span><br>
                        <strong>Queue:</strong> <span id="assign_queue_no"></span>
                    </div>

                    <!-- Available Copy Selection -->
                    <div class="mb-3">
                        <label class="form-label">Select Available Copy <span class="text-danger">*</span></label>
                        <select class="form-select" id="assign_copy_id" name="assigned_copy_id" required>
                            <option value="">-- Select Copy --</option>
                        </select>
                        <small class="text-muted">Only available copies are shown</small>
                    </div>

                    <!-- Expires In Days -->
                    <div class="mb-3">
                        <label class="form-label">Hold Duration (Days) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="assign_expires_in_days" name="expires_in_days" value="2" min="1" max="14" required>
                        <small class="text-muted">User must pick up within this period (default: 2 days)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Assign Copy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Reservation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>User:</strong> <span id="view_user_name"></span></p>
                        <p><strong>Email:</strong> <span id="view_user_email"></span></p>
                        <p><strong>Book:</strong> <span id="view_book_title"></span></p>
                        <p><strong>Queue:</strong> <span id="view_queue_no"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <span id="view_status"></span></p>
                        <p><strong>Reserved At:</strong> <span id="view_reserved_at"></span></p>
                        <p><strong>Expires At:</strong> <span id="view_expires_at"></span></p>
                        <p><strong>Assigned Copy:</strong> <span id="view_assigned_copy"></span></p>
                    </div>
                    <div class="col-md-12">
                        <p><strong>Note:</strong></p>
                        <p id="view_note" class="text-muted"></p>
                    </div>
                    <div class="col-md-12">
                        <hr>
                        <small class="text-muted">
                            <strong>Created by:</strong> <span id="view_created_by"></span><br>
                            <strong>Updated by:</strong> <span id="view_updated_by"></span>
                        </small>
                    </div>
                </div>
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
// Define global functions first (before document.ready)
let reservationsTable;

window.applyFilters = function() {
    if (reservationsTable) {
        reservationsTable.ajax.reload();
    }
};

window.resetFilters = function() {
    $('#filterForm')[0].reset();
    if (reservationsTable) {
        reservationsTable.ajax.reload();
    }
};

window.openCreateModal = function() {
    $('#createForm')[0].reset();
    
    // Reset Select2 fields if they exist
    if (typeof $.fn.select2 !== 'undefined') {
        $('#create_library_item_id').val(null).trigger('change');
        $('#create_user_id').val(null).trigger('change');
        
        // Auto-select current logged-in user
        @if(!auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
        // For regular users, pre-select themselves automatically
        setTimeout(function() {
            const option = new Option('{{ auth()->user()->name }} ({{ auth()->user()->email }})', '{{ auth()->id() }}', true, true);
            $('#create_user_id').append(option).trigger('change');
            $('#create_user_id').prop('disabled', true); // Disable so they can't change
        }, 100);
        @else
        // For admin/manager/staff, load current user as default but allow changing
        setTimeout(function() {
            const option = new Option('{{ auth()->user()->name }} ({{ auth()->user()->email }})', '{{ auth()->id() }}', true, true);
            $('#create_user_id').append(option).trigger('change');
        }, 100);
        @endif
    }
    
    $('#createModal').modal('show');
};

$(document).ready(function() {
    // Wait for Select2 to load
    function initializeSelect2() {
        if (typeof $.fn.select2 === 'undefined') {
            console.log('⏳ Waiting for Select2 to load...');
            setTimeout(initializeSelect2, 100);
            return;
        }

        console.log('✅ Initializing Select2...');

        // Initialize Select2 for user selection with AJAX search
        $('#create_user_id').select2({
            dropdownParent: $('#createModal'),
            placeholder: 'Search for a user...',
            width: '100%',
            allowClear: true,
            ajax: {
                url: '{{ route("admin.library.reservations.search-users") }}',
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
            minimumInputLength: 0, // Allow loading current user without typing
            templateResult: formatUser,
            templateSelection: formatUserSelection
        });

        // Initialize Select2 for book selection with AJAX search
        $('#create_library_item_id').select2({
            dropdownParent: $('#createModal'),
            placeholder: 'Search for a book...',
            width: '100%',
            allowClear: true,
            ajax: {
                url: '{{ route("admin.library.loans.search-books") }}',
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
                            results: data.results.map(book => ({
                                id: book.id,
                                text: book.text,
                                isbn: book.isbn,
                                available_copies: book.available_copies
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
            minimumInputLength: 2,
            templateResult: formatBook,
            templateSelection: formatBookSelection
        });

        // Format user result
        function formatUser(user) {
            if (user.loading) {
                return user.text;
            }
            return $('<span>' + user.text + '</span>');
        }

        function formatUserSelection(user) {
            return user.text || user.id;
        }

        // Format book result
        function formatBook(book) {
            if (book.loading) {
                return book.text;
            }
            
            let $result = $('<div>');
            $result.text(book.text);
            
            if (book.available_copies !== undefined) {
                let badge = book.available_copies > 0 
                    ? '<span class="badge bg-success ms-2">' + book.available_copies + ' available</span>'
                    : '<span class="badge bg-warning ms-2">No copies available</span>';
                $result.append(badge);
            }
            
            return $result;
        }

        function formatBookSelection(book) {
            return book.text || book.id;
        }
    }

    // Initialize Select2 with retry
    initializeSelect2();

    // Initialize DataTable
    function initDataTable() {
        reservationsTable = $('#reservationsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.library.reservations.index") }}',
                type: 'GET',
                data: function(d) {
                    d.status = $('#filter_status').val();
                    d.library_item_id = $('#filter_library_item_id').val();
                    d.date_from = $('#filter_date_from').val();
                    d.date_to = $('#filter_date_to').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables Ajax Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Loading Data',
                        text: 'Failed to load reservations. Please try again.'
                    });
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'queue_no', name: 'queue_no', orderable: false },
                { data: 'user_name', name: 'user.name' },
                { data: 'book_title', name: 'libraryItem.title' },
                { data: 'status_badge', name: 'status' },
                { data: 'reserved_at', name: 'reserved_at' },
                { data: 'expires_at', name: 'expires_at' },
                { data: 'copy_barcode', name: 'assignedCopy.barcode', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[5, 'desc']], // Sort by reserved_at DESC
            pageLength: 25,
            responsive: true,
            language: {
                processing: '<i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading...'
            }
        });
    }

    initDataTable();

    // Submit Create Form
    $('#createForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route("admin.library.reservations.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000
                    });
                    reservationsTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                let message = 'Failed to create reservation.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
                });
            }
        });
    });

    // View Reservation
    window.viewReservation = function(id) {
        $.ajax({
            url: '/admin/library/reservations/' + id,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#view_user_name').text(data.user_name || 'N/A');
                    $('#view_user_email').text(data.user_email || 'N/A');
                    $('#view_book_title').text(data.book_title || 'N/A');
                    $('#view_queue_no').html(data.queue_no ? '<span class="badge bg-warning text-dark">#' + data.queue_no + '</span>' : '-');
                    
                    // Status badge
                    let statusBadge = '';
                    if (data.status === 'pending') statusBadge = '<span class="badge bg-warning">Pending</span>';
                    else if (data.status === 'ready') statusBadge = '<span class="badge bg-info">Ready</span>';
                    else if (data.status === 'fulfilled') statusBadge = '<span class="badge bg-success">Fulfilled</span>';
                    else if (data.status === 'cancelled') statusBadge = '<span class="badge bg-secondary">Cancelled</span>';
                    $('#view_status').html(statusBadge);
                    
                    $('#view_reserved_at').text(data.reserved_at || 'N/A');
                    $('#view_expires_at').text(data.expires_at || '-');
                    $('#view_assigned_copy').html(data.assigned_copy_barcode ? '<span class="badge bg-info">' + data.assigned_copy_barcode + '</span>' : '-');
                    $('#view_note').text(data.note || 'No notes');
                    $('#view_created_by').text(data.created_by || 'N/A');
                    $('#view_updated_by').text(data.updated_by || 'N/A');
                    
                    $('#viewModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load reservation details.'
                });
            }
        });
    };

    // Open Assign Copy Modal
    window.openAssignCopyModal = function(id) {
        $.ajax({
            url: '/admin/library/reservations/' + id,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#assign_reservation_id').val(data.id);
                    $('#assign_library_item_id').val(data.library_item_id);
                    $('#assign_book_title').text(data.book_title);
                    $('#assign_user_name').text(data.user_name);
                    $('#assign_queue_no').html(data.queue_no ? '#' + data.queue_no : '-');
                    
                    // Load available copies
                    loadAvailableCopies(data.library_item_id);
                    
                    $('#assignCopyModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load reservation data.'
                });
            }
        });
    };

    // Load available copies for assignment
    function loadAvailableCopies(itemId) {
        $.ajax({
            url: '/admin/library/reservations/' + itemId + '/available-copies',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#assign_copy_id');
                    select.empty().append('<option value="">-- Select Copy --</option>');
                    
                    if (response.data.length === 0) {
                        select.append('<option value="" disabled>No available copies found</option>');
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Copies Available',
                            text: 'There are no available copies for this book at the moment.'
                        });
                        return;
                    }
                    
                    response.data.forEach(copy => {
                        const location = copy.location || 'N/A';
                        const shelfCode = copy.shelf_code || '';
                        const condition = copy.condition ? ' [' + copy.condition + ']' : '';
                        const text = copy.barcode + ' - ' + shelfCode + ' (' + location + ')' + condition;
                        select.append(new Option(text, copy.id));
                    });
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to load available copies.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            }
        });
    }

    // Submit Assign Copy Form
    $('#assignCopyForm').on('submit', function(e) {
        e.preventDefault();

        const reservationId = $('#assign_reservation_id').val();
        const formData = {
            _token: '{{ csrf_token() }}',
            assigned_copy_id: $('#assign_copy_id').val(),
            expires_in_days: $('#assign_expires_in_days').val()
        };

        $.ajax({
            url: '/admin/library/reservations/' + reservationId + '/assign-copy',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#assignCopyModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000
                    });
                    reservationsTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                let message = 'Failed to assign copy.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
                });
            }
        });
    });

    // Fulfill Reservation
    window.fulfillReservation = function(id) {
        Swal.fire({
            title: 'Mark as Fulfilled?',
            text: 'This indicates the user has picked up the book.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Yes, Fulfill',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/library/reservations/' + id + '/fulfill',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Fulfilled!',
                                text: response.message,
                                timer: 2000
                            });
                            reservationsTable.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to fulfill reservation.'
                        });
                    }
                });
            }
        });
    };

    // Cancel Reservation
    window.cancelReservation = function(id) {
        Swal.fire({
            title: 'Cancel Reservation?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Enter cancellation reason (optional)...',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-times"></i> Yes, Cancel',
            cancelButtonText: 'No, Go Back'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/library/reservations/' + id + '/cancel',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        note: result.value || 'Cancelled by user'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cancelled!',
                                text: response.message,
                                timer: 2000
                            });
                            reservationsTable.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to cancel reservation.'
                        });
                    }
                });
            }
        });
    };

    // Delete Reservation
    window.deleteReservation = function(id) {
        Swal.fire({
            title: 'Delete Reservation?',
            text: 'This will soft delete the reservation.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/library/reservations/' + id,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000
                            });
                            reservationsTable.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to delete reservation.'
                        });
                    }
                });
            }
        });
    };
});
</script>
@endpush

