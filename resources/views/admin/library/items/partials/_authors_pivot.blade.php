<!-- Authors Management Modal -->
<div class="modal fade" id="authorsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-users"></i> Manage Authors for: <span id="item-title"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="current-item-id">
                
                <!-- Add Author Button -->
                <div class="mb-3">
                    <button type="button" class="btn btn-success" onclick="openAddAuthorModal()">
                        <i class="fas fa-plus"></i> Add Author
                    </button>
                </div>

                <!-- Authors DataTable -->
                <div class="table-responsive">
                    <table id="itemAuthorsTable" class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Author Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Author Modal -->
<div class="modal fade" id="addAuthorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Add Author</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAuthorForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="author_id" class="form-label">Select Author <span class="text-danger">*</span></label>
                        <select class="form-select" id="author_id" name="author_id" required>
                            <option value="">Search for an author...</option>
                        </select>
                        <small class="text-muted">Start typing to search</small>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">-- Select Role --</option>
                            <option value="author">Author</option>
                            <option value="editor">Editor</option>
                            <option value="translator">Translator</option>
                            <option value="illustrator">Illustrator</option>
                            <option value="contributor">Contributor</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> If the author doesn't exist, please add them from the Authors page first.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Add Author
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Author Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editRoleForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_author_id">
                    <input type="hidden" id="edit_old_role">
                    
                    <div class="mb-3">
                        <label class="form-label">Author</label>
                        <input type="text" class="form-control" id="edit_author_name" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="edit_new_role" class="form-label">New Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_new_role" name="new_role" required>
                            <option value="">-- Select Role --</option>
                            <option value="author">Author</option>
                            <option value="editor">Editor</option>
                            <option value="translator">Translator</option>
                            <option value="illustrator">Illustrator</option>
                            <option value="contributor">Contributor</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let itemAuthorsTable;

// Open Authors Management Modal
function openAuthorsModal(itemId, itemTitle) {
    $('#current-item-id').val(itemId);
    $('#item-title').text(itemTitle);
    
    // Initialize or reload DataTable
    if (itemAuthorsTable) {
        itemAuthorsTable.ajax.url(`/admin/library/items/${itemId}/authors/data`).load();
    } else {
        initItemAuthorsTable(itemId);
    }
    
    $('#authorsModal').modal('show');
}

// Initialize Authors DataTable
function initItemAuthorsTable(itemId) {
    itemAuthorsTable = $('#itemAuthorsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `/admin/library/items/${itemId}/authors/data`,
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTables Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load authors data.'
                });
            }
        },
        columns: [
            { data: 'author_name', name: 'library_authors.name' },
            { data: 'phone', name: 'library_authors.phone' },
            { data: 'email', name: 'library_authors.email' },
            { data: 'role_badge', name: 'library_author_item.role', orderable: false },
            { data: 'status_badge', name: 'library_author_item.is_active', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 10,
        responsive: true,
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading...'
        }
    });
}

// Open Add Author Modal
function openAddAuthorModal() {
    $('#addAuthorForm')[0].reset();
    
    // Reset Select2
    if ($('#author_id').hasClass('select2-hidden-accessible')) {
        $('#author_id').val(null).trigger('change');
    }
    
    $('#addAuthorModal').modal('show');
}

// Initialize Select2 for author search
$(document).ready(function() {
    // Wait for Select2 to load
    function initAuthorSelect2() {
        if (typeof $.fn.select2 === 'undefined') {
            setTimeout(initAuthorSelect2, 100);
            return;
        }

        $('#author_id').select2({
            dropdownParent: $('#addAuthorModal'),
            placeholder: 'Search for an author...',
            allowClear: true,
            ajax: {
                url: '{{ route("admin.library.authors.search") }}',
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
                            results: data.results.map(author => ({
                                id: author.id,
                                text: author.text
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
            minimumInputLength: 0
        });
    }

    initAuthorSelect2();
});

// Submit Add Author Form
$('#addAuthorForm').on('submit', function(e) {
    e.preventDefault();
    
    const itemId = $('#current-item-id').val();
    const formData = {
        author_id: $('#author_id').val(),
        role: $('#role').val(),
        _token: '{{ csrf_token() }}'
    };

    $.ajax({
        url: `/admin/library/items/${itemId}/authors`,
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#addAuthorModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                itemAuthorsTable.ajax.reload(null, false);
            }
        },
        error: function(xhr) {
            let message = 'Failed to add author.';
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

// Edit Author Role
function editAuthorRole(itemId, authorId, currentRole) {
    // Fetch author details
    $.ajax({
        url: `/admin/library/authors/${authorId}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#edit_author_id').val(authorId);
                $('#edit_old_role').val(currentRole);
                $('#edit_author_name').val(response.data.name);
                $('#edit_new_role').val(currentRole);
                $('#editRoleModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load author details.'
            });
        }
    });
}

// Submit Edit Role Form
$('#editRoleForm').on('submit', function(e) {
    e.preventDefault();
    
    const itemId = $('#current-item-id').val();
    const formData = {
        author_id: $('#edit_author_id').val(),
        old_role: $('#edit_old_role').val(),
        new_role: $('#edit_new_role').val(),
        _token: '{{ csrf_token() }}'
    };

    $.ajax({
        url: `/admin/library/items/${itemId}/authors`,
        method: 'PUT',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#editRoleModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                itemAuthorsTable.ajax.reload(null, false);
            }
        },
        error: function(xhr) {
            let message = 'Failed to update role.';
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

// Toggle Author Status
function toggleAuthorStatus(itemId, authorId, role) {
    Swal.fire({
        title: 'Toggle Author Status',
        text: 'Do you want to attach/detach this author?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, toggle it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/library/items/${itemId}/authors/toggle`,
                method: 'POST',
                data: {
                    author_id: authorId,
                    role: role,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        itemAuthorsTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    let message = 'Failed to toggle status.';
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
        }
    });
}

// Remove Author
function removeAuthor(itemId, authorId, role) {
    Swal.fire({
        title: 'Remove Author?',
        text: 'This will soft-delete the author relationship. You can re-attach later.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, remove it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/library/items/${itemId}/authors`,
                method: 'DELETE',
                data: {
                    author_id: authorId,
                    role: role,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Removed!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        itemAuthorsTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    let message = 'Failed to remove author.';
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
        }
    });
}
</script>
@endpush










