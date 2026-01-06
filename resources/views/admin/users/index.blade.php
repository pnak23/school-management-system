@extends('layouts.app')

@section('title', 'Manage Users - School Management System')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-users"></i> Manage Users</h2>
        </div>
        <div class="col-md-6 text-end">
            @if(auth()->user()->hasAnyRole(['admin', 'manager']))
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Create New User
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
                                <i class="fas fa-users"></i> Total Users
                            </h6>
                            <h2 class="mb-0 mt-2" id="statTotalUsers">
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

        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">
                                <i class="fas fa-check-circle"></i> Active Users
                            </h6>
                            <h2 class="mb-0 mt-2" id="statActiveUsers">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                    <div>
                            <i class="fas fa-user-check fa-3x opacity-50"></i>
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
                                <i class="fas fa-user-slash"></i> Inactive Users
                            </h6>
                            <h2 class="mb-0 mt-2" id="statInactiveUsers">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                    <div>
                            <i class="fas fa-user-times fa-3x opacity-50"></i>
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
                                <i class="fas fa-user-shield"></i> With Roles
                            </h6>
                            <h2 class="mb-0 mt-2" id="statUsersWithRoles">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-shield-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <small class="d-block mt-2">
                        <button type="button" class="btn btn-sm btn-light" onclick="fetchUserStats()" title="Refresh Stats">
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
                    <input type="text" id="searchInput" class="form-control" placeholder="Name, Email...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="banned">Banned</option>
                        </select>
                    </div>
                <div class="col-md-2">
                    <label class="form-label">Active</label>
                    <select id="activeFilter" class="form-select">
                        <option value="all">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Role</label>
                    <select id="roleFilter" class="form-select">
                        <option value="all">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
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
                <table id="usersTable" class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Roles</th>
                            <th>Active</th>
                            <th>Created At</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Create New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm" onsubmit="saveUser(event)">
                    <input type="hidden" id="userId" name="user_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required placeholder="Enter full name">
                            <p id="nameError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control" required placeholder="user@example.com">
                            <p id="emailError" class="text-danger small mt-1 d-none"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                Password <span id="passwordRequired" class="text-danger">*</span>
                            </label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter password (min 3 characters)">
                            <p id="passwordError" class="text-danger small mt-1 d-none"></p>
                            <small class="text-muted">Leave blank to keep current password (when editing)</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="banned">Banned</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="profile_picture" class="form-label">Profile Picture</label>
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="form-control">
                            <small class="text-muted">Upload JPG, PNG or GIF (max 2MB)</small>
                            <div id="profilePreview" class="mt-2 d-none">
                                <img src="" alt="Preview" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="cover_photo" class="form-label">Cover Photo</label>
                            <input type="file" id="cover_photo" name="cover_photo" accept="image/*" class="form-control">
                            <small class="text-muted">Upload JPG, PNG or GIF (max 2MB)</small>
                            <div id="coverPreview" class="mt-2 d-none">
                                <img src="" alt="Preview" class="img-thumbnail" style="width: 100%; max-height: 100px; object-fit: cover;">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Roles</label>
                        <div id="rolesCheckboxes" class="border rounded p-3 bg-light">
                            <!-- Roles will be loaded here dynamically -->
                            <p class="text-muted small mb-0">Loading roles...</p>
                        </div>
            </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('userForm').requestSubmit()">
                    <span id="submitBtnText">Create User</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye"></i> User Details</h5>
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
<style>
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.85em;
    }
</style>
@endpush

    @push('scripts')
    <script>
let usersTable;
let allRoles = [];

$(document).ready(function() {
    loadUsers();
    loadRoles();
    fetchUserStats();

    // Filter change handlers
    $('#searchInput').on('keyup', debounce(function() {
        usersTable.search(this.value).draw();
    }, 500));

    $('#statusFilter, #activeFilter, #roleFilter, #dateFromFilter').on('change', function() {
        usersTable.ajax.reload();
    });
});

// Load roles
function loadRoles() {
    $.ajax({
        url: '{{ route("admin.roles.index") }}',
        type: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                allRoles = response.data.filter(role => role.is_active);
        renderRolesCheckboxes();
            }
        },
        error: function(error) {
        console.error('Error loading roles:', error);
    }
    });
}

// Render roles checkboxes
function renderRolesCheckboxes(selectedRoles = []) {
    const container = $('#rolesCheckboxes');
    if (allRoles.length === 0) {
        container.html('<p class="text-muted small mb-0">No roles available</p>');
        return;
    }
    
    const html = allRoles.map(role => `
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" value="${role.id}" 
                ${selectedRoles.includes(role.name) ? 'checked' : ''}
                id="role_${role.id}">
            <label class="form-check-label" for="role_${role.id}">
                ${escapeHtml(role.name)}
        </label>
        </div>
    `).join('');
    
    container.html(html);
}

// Load users DataTable
function loadUsers() {
    usersTable = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.users.index") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.is_active = $('#activeFilter').val();
                d.role = $('#roleFilter').val();
                d.date_from = $('#dateFromFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'user_info', name: 'name', orderable: true },
            { data: 'email', name: 'email' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'roles_badge', name: 'roles_badge', orderable: false, searchable: false },
            { data: 'active_toggle', name: 'is_active', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']],
        pageLength: 25,
        responsive: true
    });
}

// Fetch user statistics
function fetchUserStats() {
    $.ajax({
        url: '{{ route("admin.users.stats") }}',
        type: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#statTotalUsers').html(data.total);
                $('#statActiveUsers').html(data.active);
                $('#statInactiveUsers').html(data.inactive);
                $('#statUsersWithRoles').html(data.with_roles);
            }
        },
        error: function(xhr) {
            console.error('Failed to fetch user stats:', xhr);
            $('#statTotalUsers').html('<small>Error</small>');
            $('#statActiveUsers').html('<small>Error</small>');
            $('#statInactiveUsers').html('<small>Error</small>');
            $('#statUsersWithRoles').html('<small>Error</small>');
        }
    });
}

// Clear filters
function clearFilters() {
    $('#searchInput').val('');
    $('#statusFilter').val('all');
    $('#activeFilter').val('all');
    $('#roleFilter').val('all');
    $('#dateFromFilter').val('');
    usersTable.search('').ajax.reload();
        }

        // Open create modal
        function openCreateModal() {
    $('#modalTitle').text('Create New User');
    $('#submitBtnText').text('Create User');
    $('#userForm')[0].reset();
    $('#userId').val('');
    $('#password').prop('required', true);
    $('#passwordRequired').show();
    $('#profilePreview').addClass('d-none');
    $('#coverPreview').addClass('d-none');
    renderRolesCheckboxes();
    clearErrors();
    $('#userModal').modal('show');
        }

// View user
function viewUser(id) {
    $.ajax({
        url: `{{ route("admin.users.index") }}/${id}`,
        type: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                const user = response.data;
                let statusBadge = '';
                if (user.status === 'active') statusBadge = '<span class="badge bg-success">Active</span>';
                else if (user.status === 'inactive') statusBadge = '<span class="badge bg-warning">Inactive</span>';
                else statusBadge = '<span class="badge bg-danger">Banned</span>';
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">User Information</h6>
                            <p><strong>ID:</strong> #${user.id}</p>
                            <p><strong>Name:</strong> ${escapeHtml(user.name)}</p>
                            <p><strong>Email:</strong> ${escapeHtml(user.email)}</p>
                            <p><strong>Status:</strong> ${statusBadge}</p>
                            <p><strong>Active:</strong> ${user.is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Profile</h6>
                            ${user.profile_picture ? `<p><img src="/storage/${user.profile_picture}" alt="Profile" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;"></p>` : '<p class="text-muted">No profile picture</p>'}
                            ${user.cover_photo ? `<p><img src="/storage/${user.cover_photo}" alt="Cover" class="img-thumbnail" style="width: 100%; max-height: 150px; object-fit: cover;"></p>` : '<p class="text-muted">No cover photo</p>'}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Roles</h6>
                            ${user.roles && user.roles.length > 0 
                                ? user.roles.map(role => `<span class="badge bg-info me-1">${escapeHtml(role.name)}</span>`).join('')
                                : '<span class="text-muted">No roles assigned</span>'}
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Timestamps</h6>
                            <p><strong>Created:</strong> ${user.created_at || 'N/A'}</p>
                            <p><strong>Updated:</strong> ${user.updated_at || 'N/A'}</p>
                            ${user.email_verified_at ? `<p><strong>Email Verified:</strong> ${user.email_verified_at}</p>` : ''}
                        </div>
                    </div>
                `;
                
                $('#viewContent').html(html);
                $('#viewModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load user details.', 'error');
        }
    });
}

// Edit user
function editUser(id) {
    $.ajax({
        url: `{{ route("admin.users.index") }}/${id}`,
        type: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                const user = response.data;
                
                $('#modalTitle').text('Edit User');
                $('#submitBtnText').text('Update User');
                $('#userId').val(user.id);
                $('#name').val(user.name);
                $('#email').val(user.email);
                $('#status').val(user.status);
                $('#password').prop('required', false);
                $('#passwordRequired').hide();
                $('#profile_picture').val('');
                $('#cover_photo').val('');
                    
        // Show current images
        if (user.profile_picture) {
                    $('#profilePreview img').attr('src', `/storage/${user.profile_picture}`);
                    $('#profilePreview').removeClass('d-none');
        } else {
                    $('#profilePreview').addClass('d-none');
        }
        
        if (user.cover_photo) {
                    $('#coverPreview img').attr('src', `/storage/${user.cover_photo}`);
                    $('#coverPreview').removeClass('d-none');
        } else {
                    $('#coverPreview').addClass('d-none');
        }
        
        // Set roles
                const roleNames = user.roles ? user.roles.map(r => r.name) : [];
                renderRolesCheckboxes(roleNames);
        
        clearErrors();
                $('#userModal').modal('show');
            }
        },
        error: function(error) {
        console.error('Error loading user:', error);
            Swal.fire('Error', 'Failed to load user details', 'error');
    }
    });
        }

// Save user (create or update)
function saveUser(event) {
    event.preventDefault();
    clearErrors();
    
    const userId = $('#userId').val();
    const isEdit = userId !== '';
    
    // Use FormData for file uploads
    const formData = new FormData();
    formData.append('name', $('#name').val());
    formData.append('email', $('#email').val());
    formData.append('status', $('#status').val());
    
    // Add password if provided
    const password = $('#password').val();
    if (password) {
        formData.append('password', password);
        formData.append('password_confirmation', password);
    }
    
    // Add files if selected
    const profilePictureFile = $('#profile_picture')[0].files[0];
    if (profilePictureFile) {
        formData.append('profile_picture', profilePictureFile);
    }
    
    const coverPhotoFile = $('#cover_photo')[0].files[0];
    if (coverPhotoFile) {
        formData.append('cover_photo', coverPhotoFile);
    }
    
    // Add roles
    const roles = $('input[name="roles[]"]:checked').map(function() {
        return $(this).val();
    }).get();
    roles.forEach(roleId => formData.append('roles[]', roleId));
    
    // Add method for edit
    if (isEdit) {
        formData.append('_method', 'PUT');
    }
    
    const url = isEdit 
        ? `{{ route("admin.users.index") }}/${userId}`
        : '{{ route("admin.users.store") }}';
    
    $.ajax({
        url: url,
        type: 'POST',
            headers: {
                'Accept': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message || 'User saved successfully', 'success');
                $('#userModal').modal('hide');
                usersTable.ajax.reload();
                fetchUserStats();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                displayErrors(errors);
                } else {
                Swal.fire('Error', xhr.responseJSON?.message || 'An error occurred while saving', 'error');
    }
        }
    });
}

// Toggle active status
function toggleActive(id, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    const action = newStatus ? 'activate' : 'deactivate';
    const statusText = newStatus ? 'active' : 'inactive';
    
    Swal.fire({
        title: `Are you sure?`,
        text: `Do you want to ${action} this user? Status will be changed to "${statusText}".`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Yes, ${action}!`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ route("admin.users.index") }}/${id}`,
                type: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(userResponse) {
                    if (userResponse.success) {
                        const user = userResponse.data;
                        
                        $.ajax({
                            url: `{{ route("admin.users.index") }}/${id}`,
                            type: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            data: JSON.stringify({
                name: user.name,
                email: user.email,
                is_active: newStatus,
                status: statusText
                            }),
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Success', response.message || `User ${action}d successfully`, 'success');
                                    usersTable.ajax.reload();
                                    fetchUserStats();
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to update status', 'error');
                            }
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to load user data', 'error');
    }
            });
        } else {
            // Reload table to reset toggle
            usersTable.ajax.reload();
        }
    });
}

// Delete user
function deleteUser(id) {
    Swal.fire({
        title: 'Delete User?',
        text: 'This will deactivate the user. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ route("admin.users.index") }}/${id}`,
                type: 'DELETE',
                headers: {
                'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted', response.message || 'User deleted successfully', 'success');
                        usersTable.ajax.reload();
                        fetchUserStats();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete user', 'error');
    }
            });
        }
    });
}

// Display form errors
function displayErrors(errors) {
    Object.keys(errors).forEach(field => {
        const errorElement = $(`#${field}Error`);
        if (errorElement.length) {
            errorElement.text(errors[field][0]);
            errorElement.removeClass('d-none');
        }
    });
}

// Clear form errors
function clearErrors() {
    $('[id$="Error"]').addClass('d-none').text('');
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

// Escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Image preview handlers
$('#profile_picture').on('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#profilePreview img').attr('src', e.target.result);
            $('#profilePreview').removeClass('d-none');
        };
        reader.readAsDataURL(file);
    }
});

$('#cover_photo').on('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#coverPreview img').attr('src', e.target.result);
            $('#coverPreview').removeClass('d-none');
        };
        reader.readAsDataURL(file);
    }
});
    </script>
    @endpush
