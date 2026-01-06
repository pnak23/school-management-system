@extends('layouts.app')

@section('title', 'Library Items / Books')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-book"></i> Library Items / Books</h2>
            <p class="text-muted">Manage library book records (ISBN + Edition)</p>
        </div>
        <div class="col-md-6 text-end">
            @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Add Book
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
                                <i class="fas fa-book"></i> Total Items
                            </h6>
                            <h2 class="mb-0 mt-2" id="statTotal">
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
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-check-circle"></i> Active Items
                            </h6>
                            <h2 class="mb-0 mt-2" id="statActive">
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
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-ban"></i> Inactive Items
                            </h6>
                            <h2 class="mb-0 mt-2" id="statInactive">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-book-dead fa-3x opacity-50"></i>
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
                                <i class="fas fa-copy"></i> With Copies
                            </h6>
                            <h2 class="mb-0 mt-2" id="statWithCopies">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-layer-group fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <small class="d-block mt-2">
                        <button type="button" class="btn btn-sm btn-light" onclick="fetchItemsStats()" title="Refresh Stats">
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
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Title, ISBN, Edition...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select id="categoryFilter" class="form-select">
                        <option value="all">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Publisher</label>
                    <select id="publisherFilter" class="form-select">
                        <option value="all">All Publishers</option>
                        @foreach($publishers as $publisher)
                            <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" id="dateFromFilter" class="form-control">
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
                <table id="itemsTable" class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>ISBN</th>
                            <th>Edition</th>
                            <th>Category</th>
                            <th>Publisher</th>
                            <th>Authors</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include Item Form Modal -->
@include('admin.library.items._form')

<!-- View Item Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Library Item Details</h5>
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
<!-- DataTables Bootstrap 5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let itemsTable;
const canWrite = {{ auth()->user()->hasAnyRole(['admin', 'manager', 'staff']) ? 'true' : 'false' }};
const canDelete = {{ auth()->user()->hasAnyRole(['admin', 'manager']) ? 'true' : 'false' }};

// CSRF Token setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});



$(document).ready(function() {
    initDataTable();
    fetchItemsStats();
    
    // Filter change handlers
    $('#searchInput').on('keyup', debounce(function() {
        itemsTable.search(this.value).draw();
    }, 500));

    $('#statusFilter, #categoryFilter, #publisherFilter, #dateFromFilter').on('change', function() {
        itemsTable.ajax.reload();
    });
});

// Initialize DataTable
function initDataTable() {
    itemsTable = $('#itemsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.library.items.index") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.category = $('#categoryFilter').val();
                d.publisher = $('#publisherFilter').val();
                d.date_from = $('#dateFromFilter').val();
                d.date_to = $('#dateToFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'cover_display', name: 'cover_image', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'isbn', name: 'isbn' },
            { data: 'edition', name: 'edition' },
            { data: 'category_name', name: 'category.name', orderable: false },
            { data: 'publisher_name', name: 'publisher.name', orderable: false },
            { data: 'authors_display', name: 'authors', orderable: false, searchable: false },
            { data: 'status_badge', name: 'is_active', orderable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[9, 'desc']],
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

    // Handle view button
    $(document).on('click', '.btn-view-item', function() {
        const id = $(this).data('id');
        viewItem(id);
    });

    // Handle edit button
    $(document).on('click', '.btn-edit-item', function() {
        const id = $(this).data('id');
        editItem(id);
    });

    // Handle toggle status button
    $(document).on('click', '.btn-toggle-status', function() {
        const id = $(this).data('id');
        toggleStatus(id);
    });

    // Handle delete button
    $(document).on('click', '.btn-delete-item', function() {
        const id = $(this).data('id');
        deleteItem(id);
    });
}

// Fetch items statistics
function fetchItemsStats() {
    $.ajax({
        url: '{{ route("admin.library.items.stats") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#statTotal').html(data.total);
                $('#statActive').html(data.active);
                $('#statInactive').html(data.inactive);
                $('#statWithCopies').html(data.with_copies);
            }
        },
        error: function(xhr) {
            console.error('Failed to fetch items stats:', xhr);
            $('#statTotal').html('<small>Error</small>');
            $('#statActive').html('<small>Error</small>');
            $('#statInactive').html('<small>Error</small>');
            $('#statWithCopies').html('<small>Error</small>');
        }
    });
}

// Clear filters
function clearFilters() {
    $('#searchInput').val('');
    $('#statusFilter').val('active');
    $('#categoryFilter').val('all');
    $('#publisherFilter').val('all');
    $('#dateFromFilter').val('');
    $('#dateToFilter').val('');
    itemsTable.search('').ajax.reload();
}

// Open create modal
function openCreateModal() {
    $('#itemId').val('');
    $('#modalTitle').text('Add Book');
    
    // Check if form exists before resetting
    const itemForm = document.getElementById('itemForm');
    if (itemForm) {
        itemForm.reset();
    }
    
    $('#formErrors').addClass('d-none').html('');
    $('#coverPreview').html('');
    clearErrors();
    
    // Use Bootstrap 5 modal API
    const modalElement = document.getElementById('itemModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else {
        // Fallback to jQuery if Bootstrap not loaded
        $('#itemModal').modal('show');
    }
}

// View item
function viewItem(id) {
    $.ajax({
        url: `/admin/library/items/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const item = response.data;
                let coverHtml = '';
                if (item.cover_image) {
                    coverHtml = `<img src="{{ asset('storage') }}/${item.cover_image}" alt="Cover" class="img-thumbnail" style="max-width: 200px;">`;
                } else {
                    coverHtml = '<div class="bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 200px; height: 280px;"><i class="fas fa-book fa-3x"></i></div>';
                }
                
                const authors = item.authors && item.authors.length > 0 ? item.authors.map(a => `<span class="badge bg-secondary me-1">${a}</span>`).join('') : '-';
                
                let html = `
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            ${coverHtml}
                        </div>
                        <div class="col-md-8">
                            <h5 class="mb-3">${item.title}</h5>
                            <p><strong>ISBN:</strong> ${item.isbn || '-'}</p>
                            <p><strong>Edition:</strong> ${item.edition || '-'}</p>
                            <p><strong>Published Year:</strong> ${item.published_year || '-'}</p>
                            <p><strong>Language:</strong> ${item.language || '-'}</p>
                            <p><strong>Category:</strong> ${item.category_name || '-'}</p>
                            <p><strong>Publisher:</strong> ${item.publisher_name || '-'}</p>
                            <p><strong>Status:</strong> ${item.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}</p>
                            ${item.description ? '<p><strong>Description:</strong><br>' + item.description + '</p>' : ''}
                            <p><strong>Authors:</strong><br>${authors}</p>
                        </div>
                    </div>
                `;
                
                $('#viewContent').html(html);
                $('#viewModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load item details.', 'error');
        }
    });
}

// Edit item
function editItem(id) {
    $.ajax({
        url: `/admin/library/items/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const item = response.data;
                $('#itemId').val(item.id);
                $('#modalTitle').text('Edit Book');
                $('#title').val(item.title);
                $('#isbn').val(item.isbn);
                $('#edition').val(item.edition);
                $('#published_year').val(item.published_year);
                $('#language').val(item.language);
                $('#category_id').val(item.category_id);
                $('#publisher_id').val(item.publisher_id);
                $('#description').val(item.description);
                
                // Set authors (if using multi-select or similar)
                if (item.author_ids && item.author_ids.length > 0) {
                    // Handle author selection based on your form implementation
                }
                
                // Cover preview
                if (item.cover_image) {
                    $('#coverPreview').html(`<img src="{{ asset('storage') }}/${item.cover_image}" alt="Cover" class="img-thumbnail" style="max-width: 150px;">`);
                } else {
                    $('#coverPreview').html('');
                }
                
                clearErrors();
                
                // Use Bootstrap 5 modal API
                const modalElement = document.getElementById('itemModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else {
                    // Fallback to jQuery if Bootstrap not loaded
                    $('#itemModal').modal('show');
                }
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load item data.', 'error');
        }
    });
}

// Save item
function saveItem() {
    const formData = new FormData($('#itemForm')[0]);
    const itemId = $('#itemId').val();
    const url = itemId ? `/admin/library/items/${itemId}` : '/admin/library/items';
    const method = itemId ? 'PUT' : 'POST';
    
    // Add CSRF token to FormData
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    formData.append('_token', csrfToken);
    
    // Add _method for PUT
    if (method === 'PUT') {
        formData.append('_method', 'PUT');
    }
    
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
                
                // Use Bootstrap 5 modal API to hide
                const modalElement = document.getElementById('itemModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    } else {
                        // Fallback to jQuery if Bootstrap not loaded
                        $('#itemModal').modal('hide');
                    }
                } else {
                    $('#itemModal').modal('hide');
                }
                
                itemsTable.ajax.reload();
                fetchItemsStats();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                displayErrors(errors);
            } else {
                Swal.fire('Error', xhr.responseJSON.message || 'Failed to save item.', 'error');
            }
        }
    });
}

// Toggle status
function toggleStatus(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to change the status of this item?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/library/items/${id}/toggle-status`,
                type: 'POST',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success');
                        itemsTable.ajax.reload();
                        fetchItemsStats();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Failed to update status.', 'error');
                }
            });
        }
    });
}

// Delete item
function deleteItem(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete the item. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/library/items/${id}`,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        itemsTable.ajax.reload();
                        fetchItemsStats();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Failed to delete item.', 'error');
                }
            });
        }
    });
}

// Display errors
function displayErrors(errors) {
    $('#formErrors').removeClass('d-none').html('<ul class="mb-0"></ul>');
    const errorList = $('#formErrors ul');
    
    $.each(errors, function(field, messages) {
        $.each(messages, function(index, message) {
            errorList.append('<li>' + message + '</li>');
        });
        
        // Field-specific error
        const fieldError = $('#' + field + 'Error');
        if (fieldError.length) {
            fieldError.removeClass('d-none').text(messages[0]);
        }
    });
}

// Clear errors
function clearErrors() {
    $('#formErrors').addClass('d-none').html('');
    $('.text-danger.small').addClass('d-none').text('');
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
</script>
@endpush
