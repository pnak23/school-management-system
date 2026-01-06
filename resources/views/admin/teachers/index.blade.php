@extends('layouts.app')

@section('title', 'Teacher Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-chalkboard-teacher"></i> Teacher Management</h2>
        </div>
        <div class="col-md-6 text-end">
        @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Add Teacher
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
                                <i class="fas fa-users"></i> Total Teachers
                            </h6>
                            <h2 class="mb-0 mt-2" id="statTotal">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-chalkboard-teacher fa-3x opacity-50"></i>
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
                                <i class="fas fa-check-circle"></i> Active Teachers
                            </h6>
                            <h2 class="mb-0 mt-2" id="statActive">
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
                                <i class="fas fa-user-slash"></i> Inactive Teachers
                            </h6>
                            <h2 class="mb-0 mt-2" id="statInactive">
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
                                <i class="fas fa-venus-mars"></i> Male / Female
                            </h6>
                            <h2 class="mb-0 mt-2" id="statGender">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
            <div>
                            <i class="fas fa-venus-mars fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <small class="d-block mt-2">
                        <button type="button" class="btn btn-sm btn-light" onclick="fetchTeacherStats()" title="Refresh Stats">
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
                    <input type="text" id="searchInput" class="form-control" placeholder="Name, Code...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                </select>
            </div>
                <div class="col-md-2">
                    <label class="form-label">Department</label>
                    <select id="departmentFilter" class="form-select">
                        <option value="all">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sex</label>
                    <select id="sexFilter" class="form-select">
                        <option value="all">All</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
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
                <table id="teachersTable" class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Photo</th>
                            <th>Code</th>
                            <th>Khmer Name</th>
                            <th>English Name</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Phone</th>
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

<!-- Include Teacher Form Modal -->
@include('admin.teachers._form')

<!-- Include Phone Manager Modal -->
@include('admin.teachers._phonemanager')

<!-- View Teacher Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Teacher Details</h5>
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
let teachersTable;
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
    fetchTeacherStats();
    initLocationDropdowns();
    initUserSelect2();
    
    // Filter change handlers
    $('#searchInput').on('keyup', debounce(function() {
        teachersTable.search(this.value).draw();
    }, 500));
    
    $('#statusFilter, #departmentFilter, #sexFilter, #dateFromFilter').on('change', function() {
        teachersTable.ajax.reload();
    });
});

// Initialize DataTable
function initDataTable() {
    teachersTable = $('#teachersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.teachers.index") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.department = $('#departmentFilter').val();
                d.sex = $('#sexFilter').val();
                d.date_from = $('#dateFromFilter').val();
                d.date_to = $('#dateToFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'photo_display', name: 'photo', orderable: false, searchable: false },
            { data: 'teacher_code', name: 'teacher_code' },
            { data: 'khmer_name', name: 'khmer_name' },
            { data: 'english_name', name: 'english_name' },
            { data: 'department_name', name: 'department.name', orderable: false },
            { data: 'position_name', name: 'position.name', orderable: false },
            { data: 'phone', name: 'phone', orderable: false, searchable: false },
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
    $(document).on('click', '.btn-view-teacher', function() {
        const id = $(this).data('id');
        viewTeacher(id);
    });

    // Handle edit button
    $(document).on('click', '.btn-edit-teacher', function() {
        const id = $(this).data('id');
        editTeacher(id);
    });

    // Handle toggle status button
    $(document).on('click', '.btn-toggle-status', function() {
        const id = $(this).data('id');
        toggleStatus(id);
    });

    // Handle delete button
    $(document).on('click', '.btn-delete-teacher', function() {
        const id = $(this).data('id');
        deleteTeacher(id);
    });
}

// Fetch teacher statistics
function fetchTeacherStats() {
    $.ajax({
        url: '{{ route("admin.teachers.stats") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#statTotal').html(data.total);
                $('#statActive').html(data.active);
                $('#statInactive').html(data.inactive);
                $('#statGender').html(data.male + ' / ' + data.female);
            }
        },
        error: function(xhr) {
            console.error('Failed to fetch teacher stats:', xhr);
            $('#statTotal').html('<small>Error</small>');
            $('#statActive').html('<small>Error</small>');
            $('#statInactive').html('<small>Error</small>');
            $('#statGender').html('<small>Error</small>');
        }
    });
}

// Clear filters
function clearFilters() {
    $('#searchInput').val('');
    $('#statusFilter').val('all');
    $('#departmentFilter').val('all');
    $('#sexFilter').val('all');
    $('#dateFromFilter').val('');
    $('#dateToFilter').val('');
    teachersTable.search('').ajax.reload();
}

// Open create modal
function openCreateModal() {
    $('#teacherId').val('');
    $('#modalTitle').text('Add Teacher');
    $('#teacherForm')[0].reset();
    $('#formErrors').addClass('d-none').html('');
    $('#photoPreview').html('');
    $('#managePhonesBtn').addClass('d-none');
    
    // Reset location dropdowns
    resetLocationDropdowns();
    
    // Reset user Select2
    if ($('#user_id').hasClass('select2-hidden-accessible')) {
        $('#user_id').val(null).trigger('change');
    }
    
    // Ensure location dropdowns are initialized
    if (typeof $.fn.select2 !== 'undefined') {
        setupLocationEventHandlers();
    }
    
    clearErrors();
    
    // Re-initialize location dropdowns when modal is shown
    $('#teacherModal').off('shown.bs.modal').on('shown.bs.modal', function() {
        // Ensure Select2 is initialized for location dropdowns
        if (typeof $.fn.select2 !== 'undefined') {
            const $modalParent = $('#teacherModal');
            
            // Re-initialize provinces if not already initialized
            $('#birthplace_province_id, #current_province_id').each(function() {
                const $province = $(this);
                if (!$province.hasClass('select2-hidden-accessible')) {
                    $province.select2({
                        dropdownParent: $modalParent,
                        placeholder: 'Search for a province...',
                        width: '100%',
                        allowClear: true,
                        ajax: {
                            url: '{{ route("admin.teachers.search-provinces") }}',
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
                                        results: data.results.map(province => ({
                                            id: province.id,
                                            text: province.text
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
                        minimumInputLength: 0,
                        theme: 'bootstrap-5'
                    });
                }
            });
            
            // Re-initialize cascading selects if not already initialized
            const cascadingSelects = [
                '#birthplace_district_id', '#birthplace_commune_id', '#birthplace_village_id',
                '#current_district_id', '#current_commune_id', '#current_village_id'
            ];
            
            cascadingSelects.forEach(function(sel) {
                const $el = $(sel);
                if ($el.length && !$el.hasClass('select2-hidden-accessible')) {
                    $el.select2({
                        width: '100%',
                        dropdownParent: $modalParent,
                        placeholder: 'Select...',
                        allowClear: true,
                        theme: 'bootstrap-5'
                    });
                }
            });
            
            // Setup event handlers
            setupLocationEventHandlers();
        }
    });
    
    $('#teacherModal').modal('show');
}

// View teacher
function viewTeacher(id) {
    $.ajax({
        url: `/admin/teachers/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const teacher = response.data;
                let photoHtml = '';
                if (teacher.photo) {
                    photoHtml = `<img src="{{ asset('storage') }}/${teacher.photo}" alt="Photo" class="img-thumbnail" style="max-width: 150px;">`;
                } else {
                    photoHtml = '<div class="bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px;"><i class="fas fa-user fa-3x"></i></div>';
                }
                
                const primaryPhone = teacher.phones && teacher.phones.length > 0 ? teacher.phones[0].phone : '-';
                
                let html = `
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            ${photoHtml}
                        </div>
                        <div class="col-md-8">
                            <h5 class="mb-3">${teacher.khmer_name}${teacher.english_name ? ' (' + teacher.english_name + ')' : ''}</h5>
                            <p><strong>Code:</strong> ${teacher.teacher_code || '-'}</p>
                            <p><strong>Date of Birth:</strong> ${teacher.dob || '-'}</p>
                            <p><strong>Sex:</strong> ${teacher.sex === 'M' ? 'Male' : 'Female'}</p>
                            <p><strong>Department:</strong> ${teacher.department ? teacher.department.name : '-'}</p>
                            <p><strong>Position:</strong> ${teacher.position ? teacher.position.name : '-'}</p>
                            <p><strong>Employment Type:</strong> ${teacher.employment_type ? teacher.employment_type.name : '-'}</p>
                            <p><strong>Phone:</strong> ${primaryPhone}</p>
                            <p><strong>Status:</strong> ${teacher.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}</p>
                            ${teacher.note ? '<p><strong>Note:</strong><br>' + teacher.note + '</p>' : ''}
                        </div>
                    </div>
                `;
                
                $('#viewContent').html(html);
                $('#viewModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load teacher details.', 'error');
        }
    });
}

// Edit teacher
function editTeacher(id) {
    $.ajax({
        url: `/admin/teachers/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const teacher = response.data;
                $('#teacherId').val(teacher.id);
                $('#modalTitle').text('Edit Teacher');
                $('#khmer_name').val(teacher.khmer_name);
                $('#english_name').val(teacher.english_name);
                $('#teacher_code').val(teacher.teacher_code);
                $('#dob').val(teacher.dob);
                $('#sex').val(teacher.sex);
                $('#department_id').val(teacher.department_id);
                $('#position_id').val(teacher.position_id);
                $('#employment_type_id').val(teacher.employment_type_id);
                $('#note').val(teacher.note);
                
                // Set user Select2 value
                if ($('#user_id').hasClass('select2-hidden-accessible')) {
                    if (teacher.user_id && teacher.user) {
                        // Clear existing options and add the selected user
                        $('#user_id').empty();
                        const userOption = new Option(
                            teacher.user.name + ' (' + teacher.user.email + ')',
                            teacher.user_id,
                            true,
                            true
                        );
                        $('#user_id').append(userOption).trigger('change');
                    } else {
                        $('#user_id').val(null).trigger('change');
                    }
                } else {
                    // If Select2 not initialized yet, just set the value
                    $('#user_id').val(teacher.user_id || '');
                }
                
                const primaryPhone = teacher.phones && teacher.phones.length > 0 ? teacher.phones[0].phone : '';
                $('#phone').val(primaryPhone);
                
                // Photo preview
                if (teacher.photo) {
                    $('#photoPreview').html(`<img src="{{ asset('storage') }}/${teacher.photo}" alt="Photo" class="img-thumbnail" style="max-width: 150px;">`);
                } else {
                    $('#photoPreview').html('');
                }
                
                // Show manage phones button if teacher exists
                if (teacher.id) {
                    $('#managePhonesBtn').removeClass('d-none');
                }
                
                // Populate location fields
                isPrefilling = true;
                
                if (teacher.birthplace_province_id) {
                    setSelectValue($('#birthplace_province_id'), teacher.birthplace_province_id);
                    loadDistricts(teacher.birthplace_province_id, $('#birthplace_district_id'), teacher.birthplace_district_id, function() {
                        if (teacher.birthplace_district_id) {
                            loadCommunes(teacher.birthplace_district_id, $('#birthplace_commune_id'), teacher.birthplace_commune_id, function() {
                                if (teacher.birthplace_commune_id) {
                                    loadVillages(teacher.birthplace_commune_id, $('#birthplace_village_id'), teacher.birthplace_village_id, function() {
                                        isPrefilling = false;
                                    });
                                } else {
                                    isPrefilling = false;
                                }
                            });
                        } else {
                            isPrefilling = false;
                        }
                    });
                } else {
                    isPrefilling = false;
                }
                
                if (teacher.current_province_id) {
                    setSelectValue($('#current_province_id'), teacher.current_province_id);
                    loadDistricts(teacher.current_province_id, $('#current_district_id'), teacher.current_district_id, function() {
                        if (teacher.current_district_id) {
                            loadCommunes(teacher.current_district_id, $('#current_commune_id'), teacher.current_commune_id, function() {
                                if (teacher.current_commune_id) {
                                    loadVillages(teacher.current_commune_id, $('#current_village_id'), teacher.current_village_id, function() {
                                        isPrefilling = false;
                                    });
                                } else {
                                    isPrefilling = false;
                                }
                            });
                        } else {
                            isPrefilling = false;
                        }
                    });
                } else if (!teacher.birthplace_province_id) {
                    isPrefilling = false;
                }
                
                clearErrors();
                $('#teacherModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load teacher data.', 'error');
        }
    });
}

// Save teacher
function saveTeacher() {
    const formData = new FormData($('#teacherForm')[0]);
    const teacherId = $('#teacherId').val();
    const url = teacherId ? `/admin/teachers/${teacherId}` : '/admin/teachers';
    const method = teacherId ? 'PUT' : 'POST';
    
    // Add CSRF token to FormData
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    if (csrfToken) {
        formData.append('_token', csrfToken);
    }
    
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
                $('#teacherModal').modal('hide');
                teachersTable.ajax.reload();
                fetchTeacherStats();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                displayErrors(errors);
        } else {
                Swal.fire('Error', xhr.responseJSON.message || 'Failed to save teacher.', 'error');
            }
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

// Toggle status
function toggleStatus(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to change the status of this teacher?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/teachers/${id}/toggle-status`,
                type: 'POST',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success');
                        teachersTable.ajax.reload();
                        fetchTeacherStats();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Failed to update status.', 'error');
                }
            });
        }
    });
}

// Delete teacher
function deleteTeacher(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete the teacher. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/teachers/${id}`,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        teachersTable.ajax.reload();
                        fetchTeacherStats();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Failed to delete teacher.', 'error');
                }
            });
        }
    });
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

// ========================================
// PHONE MANAGEMENT FUNCTIONS
// ========================================

// Open manage phones modal
function openManagePhonesModalInternal(teacherId) {
    $('#phonesTeacherId').val(teacherId);
    loadPhones(teacherId);
    $('#phonesModal').modal('show');
}

// Load phones for a teacher
function loadPhones(teacherId) {
    $.ajax({
        url: `/admin/teachers/${teacherId}/phones`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                renderPhonesList(response.data);
            }
        },
        error: function(xhr) {
            $('#phonesList').html('<div class="alert alert-danger">Failed to load phones.</div>');
        }
    });
}

// Render phones list
function renderPhonesList(phones) {
    if (phones.length === 0) {
        $('#phonesList').html('<div class="text-center py-3 text-muted">No phone numbers found.</div>');
        return;
    }
    
    let html = '';
    phones.forEach(function(phone) {
        const primaryBadge = phone.is_primary ? '<span class="badge bg-success">Primary</span>' : '';
        const activeBadge = phone.is_active ? '<span class="badge bg-primary">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
        
        html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                <div>
                        <strong>${phone.phone}</strong> ${primaryBadge} ${activeBadge}
                        ${phone.note ? '<br><small class="text-muted">' + phone.note + '</small>' : ''}
                </div>
                <div>
                        ${!phone.is_primary ? `<button class="btn btn-sm btn-success me-1" onclick="setPrimaryPhone(${phone.teacher_id}, ${phone.id})" title="Set as Primary">
                            <i class="fas fa-star"></i>
                        </button>` : ''}
                        <button class="btn btn-sm btn-primary me-1" onclick="editPhone(${phone.teacher_id}, ${phone.id}, '${phone.phone}', '${phone.note || ''}', ${phone.is_primary})" title="Edit">
                            <i class="fas fa-edit"></i>
                </button>
                        <button class="btn btn-sm btn-danger" onclick="deletePhoneConfirm(${phone.teacher_id}, ${phone.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                </button>
                    </div>
            </div>
        </div>
    `;
    });
    
    $('#phonesList').html(html);
}

// Add phone form submit
$(document).on('submit', '#addPhoneForm', function(e) {
    e.preventDefault();
    
    const teacherId = $('#phonesTeacherId').val();
    const phone = $('#newPhone').val();
    const note = $('#newPhoneNote').val();
    const isPrimary = $('#newPhonePrimary').is(':checked');
    
    $.ajax({
        url: `/admin/teachers/${teacherId}/phones`,
        type: 'POST',
        data: {
            phone: phone,
            note: note,
            is_primary: isPrimary ? 1 : 0
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
                $('#addPhoneForm')[0].reset();
                loadPhones(teacherId);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMsg = '';
                $.each(errors, function(field, messages) {
                    errorMsg += messages[0] + '<br>';
                });
                Swal.fire('Validation Error', errorMsg, 'error');
            } else {
                Swal.fire('Error', xhr.responseJSON.message || 'Failed to add phone.', 'error');
            }
        }
    });
});

// Edit phone
function editPhone(teacherId, phoneId, phone, note, isPrimary) {
    $('#editPhoneId').val(phoneId);
    $('#editPhoneTeacherId').val(teacherId);
    $('#editPhone').val(phone);
    $('#editPhoneNote').val(note);
    $('#editPhonePrimary').prop('checked', isPrimary);
    $('#editPhoneModal').modal('show');
}

// Save phone edit
function savePhoneEdit() {
    const teacherId = $('#editPhoneTeacherId').val();
    const phoneId = $('#editPhoneId').val();
    const phone = $('#editPhone').val();
    const note = $('#editPhoneNote').val();
    const isPrimary = $('#editPhonePrimary').is(':checked') ? 1 : 0;
    
    $.ajax({
        url: `/admin/teachers/${teacherId}/phones/${phoneId}`,
        type: 'PUT',
        data: {
            phone: phone,
            note: note,
            is_primary: isPrimary
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
                $('#editPhoneModal').modal('hide');
                loadPhones(teacherId);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMsg = '';
                $.each(errors, function(field, messages) {
                    errorMsg += messages[0] + '<br>';
                });
                Swal.fire('Validation Error', errorMsg, 'error');
        } else {
                Swal.fire('Error', xhr.responseJSON.message || 'Failed to update phone.', 'error');
            }
        }
    });
}

// Set primary phone
function setPrimaryPhone(teacherId, phoneId) {
    $.ajax({
        url: `/admin/teachers/${teacherId}/phones/${phoneId}/set-primary`,
        type: 'POST',
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
                loadPhones(teacherId);
            }
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON.message || 'Failed to set primary phone.', 'error');
        }
    });
}

// Delete phone confirmation
function deletePhoneConfirm(teacherId, phoneId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the phone number. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/teachers/${teacherId}/phones/${phoneId}`,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        loadPhones(teacherId);
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Failed to delete phone.', 'error');
                }
            });
        }
    });
}

// ========== Location Dropdown Functions ==========

// Province -> District cascading
function setSelectValue($el, value, text = null) {
    if (!value) {
        $el.val('').trigger('change');
        return;
    }
    
    // For Select2 with AJAX (provinces), we need to add the option first
    if ($el.hasClass('select2-hidden-accessible') && ($el.attr('id') === 'birthplace_province_id' || $el.attr('id') === 'current_province_id')) {
        // Check if option already exists
        if ($el.find(`option[value="${value}"]`).length === 0) {
            // If text is provided, use it directly
            if (text) {
                const option = new Option(text, value, true, true);
                $el.append(option).trigger('change');
            } else {
                // Need to fetch province name
                $.get(`{{ route('admin.teachers.search-provinces') }}`, { q: '', page: 1 }, function(data) {
                    if (data.success && data.results) {
                        const province = data.results.find(p => p.id == value);
                        if (province) {
                            const option = new Option(province.text, value, true, true);
                            $el.append(option).trigger('change');
                        } else {
                            // If not found, try loading more pages or just set value
                            $el.val(value).trigger('change');
                        }
                    } else {
                        $el.val(value).trigger('change');
                    }
                });
            }
        } else {
            $el.val(value).trigger('change');
        }
    } else {
        $el.val(value ?? '');
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.trigger('change');
        } else {
            $el.trigger('change');
        }
    }
}

function loadDistricts(provinceId, $target, selectedId = null, done = null) {
    if (!provinceId) {
        $target.html('<option value="">Select district</option>');
        if ($target.hasClass('select2-hidden-accessible')) {
            $target.trigger('change.select2');
        }
        if (typeof done === 'function') done();
        return;
    }

    $.get(`{{ route('admin.teachers.districts') }}`, { province_id: provinceId }, function(resp) {
        if (resp && resp.ok) {
            // Check if Select2 is initialized
            const isSelect2 = $target.hasClass('select2-hidden-accessible');
            
            // If Select2 is initialized, we need to properly update it
            if (isSelect2) {
                // Destroy Select2 temporarily
                $target.select2('destroy');
            }
            
            // Update HTML options
            let opts = '<option value="">Select district</option>';
            resp.data.forEach(function(d) {
                const label = d.name_km ?? d.name_en;
                opts += `<option value="${d.id}">${label}</option>`;
            });
            $target.html(opts);
            
            // Re-initialize Select2 if it was initialized before
            if (isSelect2) {
                const $modalParent = $('#teacherModal');
                $target.select2({
                    width: '100%',
                    dropdownParent: $modalParent,
                    placeholder: 'Select...',
                    allowClear: true,
                    theme: 'bootstrap-5'
                });
            }
            
            // Set selected value if provided
            if (selectedId) {
                setSelectValue($target, String(selectedId));
            } else {
                $target.trigger('change');
            }
            
            if (typeof done === 'function') done();
        }
    }).fail(function() {
        $target.html('<option value="">Error loading districts</option>');
        if ($target.hasClass('select2-hidden-accessible')) {
            $target.trigger('change.select2');
        }
        if (typeof done === 'function') done();
    });
}

function loadCommunes(districtId, $target, selectedId = null, done = null) {
    if (!districtId) {
        $target.html('<option value="">Select commune</option>');
        if ($target.hasClass('select2-hidden-accessible')) {
            $target.trigger('change.select2');
        }
        if (typeof done === 'function') done();
        return;
    }

    $.get(`{{ route('admin.teachers.communes') }}`, { district_id: districtId }, function(resp) {
        if (resp && resp.ok) {
            // Check if Select2 is initialized
            const isSelect2 = $target.hasClass('select2-hidden-accessible');
            
            // If Select2 is initialized, we need to properly update it
            if (isSelect2) {
                // Destroy Select2 temporarily
                $target.select2('destroy');
            }
            
            // Update HTML options
            let opts = '<option value="">Select commune</option>';
            resp.data.forEach(function(d) {
                const label = d.name_km ?? d.name_en;
                opts += `<option value="${d.id}">${label}</option>`;
            });
            $target.html(opts);
            
            // Re-initialize Select2 if it was initialized before
            if (isSelect2) {
                const $modalParent = $('#teacherModal');
                $target.select2({
                    width: '100%',
                    dropdownParent: $modalParent,
                    placeholder: 'Select...',
                    allowClear: true,
                    theme: 'bootstrap-5'
                });
            }
            
            // Set selected value if provided
            if (selectedId) {
                setSelectValue($target, String(selectedId));
            } else {
                $target.trigger('change');
            }
            
            if (typeof done === 'function') done();
        }
    }).fail(function() {
        $target.html('<option value="">Error loading communes</option>');
        if ($target.hasClass('select2-hidden-accessible')) {
            $target.trigger('change.select2');
        }
        if (typeof done === 'function') done();
    });
}

function loadVillages(communeId, $target, selectedId = null, done = null) {
    if (!communeId) {
        $target.html('<option value="">Select village</option>');
        if ($target.hasClass('select2-hidden-accessible')) {
            $target.trigger('change.select2');
        }
        if (typeof done === 'function') done();
        return;
    }

    $.get(`{{ route('admin.teachers.villages') }}`, { commune_id: communeId }, function(resp) {
        if (resp && resp.ok) {
            // Check if Select2 is initialized
            const isSelect2 = $target.hasClass('select2-hidden-accessible');
            
            // If Select2 is initialized, we need to properly update it
            if (isSelect2) {
                // Destroy Select2 temporarily
                $target.select2('destroy');
            }
            
            // Update HTML options
            let opts = '<option value="">Select village</option>';
            resp.data.forEach(function(d) {
                const label = d.name_km ?? d.name_en;
                opts += `<option value="${d.id}">${label}</option>`;
            });
            $target.html(opts);
            
            // Re-initialize Select2 if it was initialized before
            if (isSelect2) {
                const $modalParent = $('#teacherModal');
                $target.select2({
                    width: '100%',
                    dropdownParent: $modalParent,
                    placeholder: 'Select...',
                    allowClear: true,
                    theme: 'bootstrap-5'
                });
            }
            
            // Set selected value if provided
            if (selectedId) {
                setSelectValue($target, String(selectedId));
            } else {
                $target.trigger('change');
            }
            
            if (typeof done === 'function') done();
        }
    }).fail(function() {
        $target.html('<option value="">Error loading villages</option>');
        if ($target.hasClass('select2-hidden-accessible')) {
            $target.trigger('change.select2');
        }
        if (typeof done === 'function') done();
    });
}

let isPrefilling = false;

// Initialize location dropdowns
function initLocationDropdowns() {
    const $modalParent = $('#teacherModal');
    
    // Wait for Select2 to load
    function initializeSelect2() {
        if (typeof $.fn.select2 === 'undefined') {
            console.log('⏳ Waiting for Select2 to load...');
            setTimeout(initializeSelect2, 100);
            return;
        }

        console.log('✅ Initializing Select2 for location dropdowns...');

        // Initialize Select2 for provinces with AJAX search
        $('#birthplace_province_id, #current_province_id').each(function() {
            const $province = $(this);
            if (!$province.hasClass('select2-hidden-accessible')) {
                $province.select2({
                    dropdownParent: $modalParent,
                    placeholder: 'Search for a province...',
                    width: '100%',
                    allowClear: true,
                    ajax: {
                        url: '{{ route("admin.teachers.search-provinces") }}',
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
                                    results: data.results.map(province => ({
                                        id: province.id,
                                        text: province.text
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
                    minimumInputLength: 0, // Allow loading provinces without typing
                    theme: 'bootstrap-5'
                });
            }
        });

        // Initialize Select2 for districts, communes, villages (basic searchable)
        const cascadingSelects = [
            '#birthplace_district_id', '#birthplace_commune_id', '#birthplace_village_id',
            '#current_district_id', '#current_commune_id', '#current_village_id'
        ];
        
        cascadingSelects.forEach(function(sel) {
            const $el = $(sel);
            if ($el.length && !$el.hasClass('select2-hidden-accessible')) {
                $el.select2({
                    width: '100%',
                    dropdownParent: $modalParent,
                    placeholder: 'Select...',
                    allowClear: true,
                    theme: 'bootstrap-5'
                });
            }
        });

        // Set up event handlers AFTER Select2 is initialized
        setupLocationEventHandlers();
    }

    // Start initialization
    initializeSelect2();
}

// Setup event handlers for location cascading
function setupLocationEventHandlers() {
    // Birthplace cascading
    $('#birthplace_province_id').off('change').on('change', function() {
        if (isPrefilling) return;
        const provinceId = $(this).val();
        loadDistricts(provinceId, $('#birthplace_district_id'));
        $('#birthplace_commune_id').html('<option value="">Select commune</option>');
        $('#birthplace_village_id').html('<option value="">Select village</option>');
        setSelectValue($('#birthplace_commune_id'), '');
        setSelectValue($('#birthplace_village_id'), '');
    });
    
    $('#birthplace_district_id').off('change').on('change', function() {
        if (isPrefilling) return;
        const districtId = $(this).val();
        loadCommunes(districtId, $('#birthplace_commune_id'));
        $('#birthplace_village_id').html('<option value="">Select village</option>');
        setSelectValue($('#birthplace_village_id'), '');
    });
    
    $('#birthplace_commune_id').off('change').on('change', function() {
        if (isPrefilling) return;
        const communeId = $(this).val();
        loadVillages(communeId, $('#birthplace_village_id'));
    });
    
    // Current address cascading
    $('#current_province_id').off('change').on('change', function() {
        if (isPrefilling) return;
        const provinceId = $(this).val();
        loadDistricts(provinceId, $('#current_district_id'));
        $('#current_commune_id').html('<option value="">Select commune</option>');
        $('#current_village_id').html('<option value="">Select village</option>');
        setSelectValue($('#current_commune_id'), '');
        setSelectValue($('#current_village_id'), '');
    });
    
    $('#current_district_id').off('change').on('change', function() {
        if (isPrefilling) return;
        const districtId = $(this).val();
        loadCommunes(districtId, $('#current_commune_id'));
        $('#current_village_id').html('<option value="">Select village</option>');
        setSelectValue($('#current_village_id'), '');
    });
    
    $('#current_commune_id').off('change').on('change', function() {
        if (isPrefilling) return;
        const communeId = $(this).val();
        loadVillages(communeId, $('#current_village_id'));
    });
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
            dropdownParent: $('#teacherModal'),
            placeholder: 'Search for a user...',
            width: '100%',
            allowClear: true,
            ajax: {
                url: '{{ route("admin.teachers.search-users") }}',
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

// Reset location dropdowns
function resetLocationDropdowns() {
    $('#birthplace_district_id, #birthplace_commune_id, #birthplace_village_id').html('<option value="">Select...</option>');
    $('#current_district_id, #current_commune_id, #current_village_id').html('<option value="">Select...</option>');
    setSelectValue($('#birthplace_province_id'), '');
    setSelectValue($('#birthplace_district_id'), '');
    setSelectValue($('#birthplace_commune_id'), '');
    setSelectValue($('#birthplace_village_id'), '');
    setSelectValue($('#current_province_id'), '');
    setSelectValue($('#current_district_id'), '');
    setSelectValue($('#current_commune_id'), '');
    setSelectValue($('#current_village_id'), '');
}
</script>
@endpush
