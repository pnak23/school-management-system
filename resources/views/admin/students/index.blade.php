@extends('layouts.app')

@section('title', 'Student Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-user-graduate"></i> Student Management</h2>
        </div>
        <div class="col-md-6 text-end">
        @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Add Student
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
                                <i class="fas fa-users"></i> Total Students
                            </h6>
                            <h2 class="mb-0 mt-2" id="statTotal">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h2>
                        </div>
                        <div>
                            <i class="fas fa-user-graduate fa-3x opacity-50"></i>
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
                                <i class="fas fa-check-circle"></i> Active Students
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
                                <i class="fas fa-user-slash"></i> Inactive Students
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
                        <button type="button" class="btn btn-sm btn-light" onclick="fetchStudentStats()" title="Refresh Stats">
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
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" id="dateToFilter" class="form-control">
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
                <table id="studentsTable" class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Photo</th>
                            <th>Code</th>
                            <th>Khmer Name</th>
                            <th>English Name</th>
                            <th>Sex</th>
                            <th>DOB</th>
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

<!-- Create/Edit Student Modal -->
<div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Add Student</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
            <div class="modal-body">
                <form id="studentForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="studentId" name="student_id">
                    
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="khmer_name" class="form-label">Khmer Name <span class="text-danger">*</span></label>
                            <input type="text" id="khmer_name" name="khmer_name" class="form-control" required>
                            <p id="khmer_nameError" class="text-danger small mt-1 d-none"></p>
            </div>

                        <div class="col-md-6 mb-3">
                            <label for="english_name" class="form-label">English Name</label>
                            <input type="text" id="english_name" name="english_name" class="form-control">
                            <p id="english_nameError" class="text-danger small mt-1 d-none"></p>
                        </div>
                </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" id="code" name="code" class="form-control">
                            <p id="codeError" class="text-danger small mt-1 d-none"></p>
                </div>

                        <div class="col-md-4 mb-3">
                            <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" id="dob" name="dob" class="form-control" required>
                            <p id="dobError" class="text-danger small mt-1 d-none"></p>
                </div>

                        <div class="col-md-4 mb-3">
                            <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
                            <select id="sex" name="sex" class="form-select" required>
                                <option value="">Select...</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                            <p id="sexError" class="text-danger small mt-1 d-none"></p>
                </div>
                </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <div class="input-group">
                                <input type="text" id="phone" name="phone" class="form-control">
                                <button type="button" id="managePhonesBtn" class="btn btn-info d-none" onclick="openManagePhonesModal()">
                                    <i class="fas fa-phone"></i> Manage
                        </button>
                    </div>
                            <small class="text-muted">Primary phone. Click "Manage" after saving to add more.</small>
                            <p id="phoneError" class="text-danger small mt-1 d-none"></p>
                </div>

                        <div class="col-md-6 mb-3">
                            <label for="user_id" class="form-label">Linked User</label>
                            <select id="user_id" name="user_id" class="form-select">
                                <option value="">None</option>
                            </select>
                            <small class="text-muted">Search for a user by name or email</small>
                            <p id="user_idError" class="text-danger small mt-1 d-none"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                            <p id="photoError" class="text-danger small mt-1 d-none"></p>
                            <div id="photoPreview" class="mt-2"></div>
                    </div>
                </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea id="note" name="note" class="form-control" rows="3"></textarea>
                        <p id="noteError" class="text-danger small mt-1 d-none"></p>
                </div>

                    <hr class="my-4">
                    <h6 class="mb-3"><i class="fas fa-map-marker-alt"></i> Birthplace</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birthplace_province_id" class="form-label">Birthplace Province (KH)</label>
                            <select name="birthplace_province_id" id="birthplace_province_id" class="form-select">
                                <option value="">Select province</option>
                            </select>
                            <small class="text-muted">Search for a province</small>
                            <p id="birthplace_province_idError" class="text-danger small mt-1 d-none"></p>
                </div>

                        <div class="col-md-6 mb-3">
                            <label for="birthplace_district_id" class="form-label">Birthplace District (KH)</label>
                            <select name="birthplace_district_id" id="birthplace_district_id" class="form-select">
                        <option value="">Select district</option>
                    </select>
                            <p id="birthplace_district_idError" class="text-danger small mt-1 d-none"></p>
                        </div>
                </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birthplace_commune_id" class="form-label">Birthplace Commune (KH)</label>
                            <select name="birthplace_commune_id" id="birthplace_commune_id" class="form-select">
                        <option value="">Select commune</option>
                    </select>
                            <p id="birthplace_commune_idError" class="text-danger small mt-1 d-none"></p>
                </div>

                        <div class="col-md-6 mb-3">
                            <label for="birthplace_village_id" class="form-label">Birthplace Village (KH)</label>
                            <select name="birthplace_village_id" id="birthplace_village_id" class="form-select">
                        <option value="">Select village</option>
                    </select>
                            <p id="birthplace_village_idError" class="text-danger small mt-1 d-none"></p>
                        </div>
                </div>

                    <hr class="my-4">
                    <h6 class="mb-3"><i class="fas fa-home"></i> Current Address</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="current_province_id" class="form-label">Current Province (KH)</label>
                            <select name="current_province_id" id="current_province_id" class="form-select">
                                <option value="">Select province</option>
                            </select>
                            <small class="text-muted">Search for a province</small>
                            <p id="current_province_idError" class="text-danger small mt-1 d-none"></p>
                </div>

                        <div class="col-md-6 mb-3">
                            <label for="current_district_id" class="form-label">Current District (KH)</label>
                            <select name="current_district_id" id="current_district_id" class="form-select">
                        <option value="">Select district</option>
                    </select>
                            <p id="current_district_idError" class="text-danger small mt-1 d-none"></p>
                        </div>
                </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="current_commune_id" class="form-label">Current Commune (KH)</label>
                            <select name="current_commune_id" id="current_commune_id" class="form-select">
                        <option value="">Select commune</option>
                    </select>
                            <p id="current_commune_idError" class="text-danger small mt-1 d-none"></p>
                </div>

                        <div class="col-md-6 mb-3">
                            <label for="current_village_id" class="form-label">Current Village (KH)</label>
                            <select name="current_village_id" id="current_village_id" class="form-select">
                        <option value="">Select village</option>
                    </select>
                            <p id="current_village_idError" class="text-danger small mt-1 d-none"></p>
                </div>
            </div>
        </form>
    </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveStudent()">
                    <i class="fas fa-save"></i> Save
            </button>
        </div>
        </div>
    </div>
</div>

<!-- View Student Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Student Details</h5>
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

<!-- Include Phone Manager Modal -->
@include('admin.students._phonemanager')
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
let studentsTable;
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
    fetchStudentStats();
    initLocationDropdowns();
    initUserSelect2();
    
    // Filter change handlers
    $('#searchInput').on('keyup', debounce(function() {
        studentsTable.search(this.value).draw();
    }, 500));
    
    $('#statusFilter, #sexFilter, #dateFromFilter, #dateToFilter').on('change', function() {
        studentsTable.ajax.reload();
    });
});

// Initialize DataTable
function initDataTable() {
    studentsTable = $('#studentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.students-dt.index") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.sex = $('#sexFilter').val();
                d.date_from = $('#dateFromFilter').val();
                d.date_to = $('#dateToFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'photo_display', name: 'photo', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'khmer_name', name: 'khmer_name' },
            { data: 'english_name', name: 'english_name' },
            { data: 'sex_display', name: 'sex' },
            { data: 'dob', name: 'dob' },
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
    $(document).on('click', '.btn-view-student', function() {
        const id = $(this).data('id');
        viewStudent(id);
    });

    // Handle edit button
    $(document).on('click', '.btn-edit-student', function() {
        const id = $(this).data('id');
        editStudent(id);
    });

    // Handle toggle status button
    $(document).on('click', '.btn-toggle-status', function() {
        const id = $(this).data('id');
        toggleStatus(id);
    });

    // Handle delete button
    $(document).on('click', '.btn-delete-student', function() {
        const id = $(this).data('id');
        deleteStudent(id);
    });
}

// Fetch student statistics
function fetchStudentStats() {
    $.ajax({
        url: '{{ route("admin.students-dt.stats") }}',
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
            console.error('Failed to fetch student stats:', xhr);
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
    $('#sexFilter').val('all');
    $('#dateFromFilter').val('');
    $('#dateToFilter').val('');
    studentsTable.search('').ajax.reload();
}

// Open create modal
function openCreateModal() {
    $('#studentId').val('');
    $('#modalTitle').text('Add Student');
    $('#studentForm')[0].reset();
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
    $('#studentModal').on('shown.bs.modal', function() {
        // Ensure Select2 is initialized for location dropdowns
        if (typeof $.fn.select2 !== 'undefined') {
            const $modalParent = $('#studentModal');
            
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
                            url: '{{ route("admin.students.search-provinces") }}',
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
    
    $('#studentModal').modal('show');
}

// View student
function viewStudent(id) {
    $.ajax({
        url: `/admin/students-dt/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const student = response.data;
                let photoHtml = '';
                if (student.photo) {
                    photoHtml = `<img src="{{ asset('storage') }}/${student.photo}" alt="Photo" class="img-thumbnail" style="max-width: 150px;">`;
                } else {
                    photoHtml = '<div class="bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px;"><i class="fas fa-user fa-3x"></i></div>';
                }
                
                const primaryPhone = student.phones && student.phones.length > 0 ? student.phones[0].phone : '-';
                
                let html = `
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            ${photoHtml}
                        </div>
                        <div class="col-md-8">
                            <h5 class="mb-3">${student.khmer_name}${student.english_name ? ' (' + student.english_name + ')' : ''}</h5>
                            <p><strong>Code:</strong> ${student.code || '-'}</p>
                            <p><strong>Date of Birth:</strong> ${student.dob || '-'}</p>
                            <p><strong>Sex:</strong> ${student.sex === 'M' ? 'Male' : 'Female'}</p>
                            <p><strong>Phone:</strong> ${primaryPhone}</p>
                            <p><strong>Status:</strong> ${student.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}</p>
                            ${student.note ? '<p><strong>Note:</strong><br>' + student.note + '</p>' : ''}
                        </div>
                    </div>
                `;
                
                $('#viewContent').html(html);
                $('#viewModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load student details.', 'error');
        }
    });
}

// Edit student
function editStudent(id) {
    $.ajax({
        url: `/admin/students-dt/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const student = response.data;
                $('#studentId').val(student.id);
                $('#modalTitle').text('Edit Student');
                $('#khmer_name').val(student.khmer_name);
                $('#english_name').val(student.english_name);
                $('#code').val(student.code);
                $('#dob').val(student.dob);
                $('#sex').val(student.sex);
                $('#note').val(student.note);
                
                // Set user Select2 value
                if ($('#user_id').hasClass('select2-hidden-accessible')) {
                    if (student.user_id && student.user) {
                        // Clear existing options and add the selected user
                        $('#user_id').empty();
                        const userOption = new Option(
                            student.user.name + ' (' + student.user.email + ')',
                            student.user_id,
                            true,
                            true
                        );
                        $('#user_id').append(userOption).trigger('change');
                    } else {
                        $('#user_id').val(null).trigger('change');
                    }
                } else {
                    // If Select2 not initialized yet, just set the value
                    $('#user_id').val(student.user_id || '');
                }
                
                const primaryPhone = student.phones && student.phones.length > 0 ? student.phones[0].phone : '';
                $('#phone').val(primaryPhone);
                
                // Photo preview
                if (student.photo) {
                    $('#photoPreview').html(`<img src="{{ asset('storage') }}/${student.photo}" alt="Photo" class="img-thumbnail" style="max-width: 150px;">`);
                } else {
                    $('#photoPreview').html('');
                }
                
                // Show manage phones button if student exists
                if (student.id) {
                    $('#managePhonesBtn').removeClass('d-none');
                }
                
                // Populate location fields
                isPrefilling = true;
                
            if (student.birthplace_province_id) {
                    setSelectValue($('#birthplace_province_id'), student.birthplace_province_id);
                    loadDistricts(student.birthplace_province_id, $('#birthplace_district_id'), student.birthplace_district_id, function() {
                    if (student.birthplace_district_id) {
                            loadCommunes(student.birthplace_district_id, $('#birthplace_commune_id'), student.birthplace_commune_id, function() {
                            if (student.birthplace_commune_id) {
                                    loadVillages(student.birthplace_commune_id, $('#birthplace_village_id'), student.birthplace_village_id, function() {
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
            
            if (student.current_province_id) {
                    setSelectValue($('#current_province_id'), student.current_province_id);
                    loadDistricts(student.current_province_id, $('#current_district_id'), student.current_district_id, function() {
                    if (student.current_district_id) {
                            loadCommunes(student.current_district_id, $('#current_commune_id'), student.current_commune_id, function() {
                            if (student.current_commune_id) {
                                    loadVillages(student.current_commune_id, $('#current_village_id'), student.current_village_id, function() {
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
                } else if (!student.birthplace_province_id) {
                    isPrefilling = false;
                }
                
                clearErrors();
                $('#studentModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load student data.', 'error');
        }
    });
}

// Save student
function saveStudent() {
    const formData = new FormData($('#studentForm')[0]);
    const studentId = $('#studentId').val();
    const url = studentId ? `/admin/students-dt/${studentId}` : '/admin/students-dt';
    const method = studentId ? 'PUT' : 'POST';
    
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
                $('#studentModal').modal('hide');
                studentsTable.ajax.reload();
                fetchStudentStats();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                displayErrors(errors);
        } else {
                Swal.fire('Error', xhr.responseJSON.message || 'Failed to save student.', 'error');
            }
        }
    });
}

// Toggle status
function toggleStatus(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to change the status of this student?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/students-dt/${id}/deactivate`,
                type: 'PUT',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success');
                        studentsTable.ajax.reload();
                        fetchStudentStats();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Failed to update status.', 'error');
                }
            });
        }
    });
}

// Delete student
function deleteStudent(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete the student. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/students-dt/${id}`,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        studentsTable.ajax.reload();
                        fetchStudentStats();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Failed to delete student.', 'error');
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

// Photo preview
$('#photo').on('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#photoPreview').html(`<img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-width: 150px;">`);
        };
        reader.readAsDataURL(file);
    }
});

// ========================================
// PHONE MANAGEMENT FUNCTIONS
// ========================================

// Open manage phones modal
function openManagePhonesModal() {
    const studentId = $('#studentId').val();
    if (!studentId) {
        Swal.fire('Info', 'Please save the student first before managing phones.', 'info');
        return;
    }
    openManagePhonesModalInternal(studentId);
}

// Open manage phones modal (internal)
function openManagePhonesModalInternal(studentId) {
    $('#phonesStudentId').val(studentId);
    loadPhones(studentId);
    $('#phonesModal').modal('show');
}

// Load phones for a student
function loadPhones(studentId) {
    $.ajax({
        url: `/admin/students/${studentId}/phones`,
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
                        ${!phone.is_primary ? `<button class="btn btn-sm btn-success me-1" onclick="setPrimaryPhone(${phone.student_id}, ${phone.id})" title="Set as Primary">
                            <i class="fas fa-star"></i>
                        </button>` : ''}
                        <button class="btn btn-sm btn-primary me-1" onclick="editPhone(${phone.student_id}, ${phone.id}, '${phone.phone}', '${phone.note || ''}', ${phone.is_primary})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deletePhoneConfirm(${phone.student_id}, ${phone.id})" title="Delete">
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
    
    const studentId = $('#phonesStudentId').val();
    const phone = $('#newPhone').val();
    const note = $('#newPhoneNote').val();
    const isPrimary = $('#newPhonePrimary').is(':checked');
    
    $.ajax({
        url: `/admin/students/${studentId}/phones`,
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
                loadPhones(studentId);
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
function editPhone(studentId, phoneId, phone, note, isPrimary) {
    $('#editPhoneId').val(phoneId);
    $('#editPhoneStudentId').val(studentId);
    $('#editPhone').val(phone);
    $('#editPhoneNote').val(note);
    $('#editPhonePrimary').prop('checked', isPrimary);
    $('#editPhoneModal').modal('show');
}

// Save phone edit
function savePhoneEdit() {
    const studentId = $('#editPhoneStudentId').val();
    const phoneId = $('#editPhoneId').val();
    const phone = $('#editPhone').val();
    const note = $('#editPhoneNote').val();
    const isPrimary = $('#editPhonePrimary').is(':checked') ? 1 : 0;
    
    $.ajax({
        url: `/admin/students/${studentId}/phones/${phoneId}`,
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
                loadPhones(studentId);
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
function setPrimaryPhone(studentId, phoneId) {
    $.ajax({
        url: `/admin/students/${studentId}/phones/${phoneId}/set-primary`,
        type: 'POST',
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
                loadPhones(studentId);
            }
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON.message || 'Failed to set primary phone.', 'error');
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
                $.get(`{{ route('admin.students.search-provinces') }}`, { q: '', page: 1 }, function(data) {
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

    $.get(`{{ route('admin.students.districts') }}`, { province_id: provinceId }, function(resp) {
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
                const $modalParent = $('#studentModal');
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

    $.get(`{{ route('admin.students.communes') }}`, { district_id: districtId }, function(resp) {
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
                const $modalParent = $('#studentModal');
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
    
    $.get(`{{ route('admin.students.villages') }}`, { commune_id: communeId }, function(resp) {
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
                const $modalParent = $('#studentModal');
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
    const $modalParent = $('#studentModal');
    
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
                        url: '{{ route("admin.students.search-provinces") }}',
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
            dropdownParent: $('#studentModal'),
            placeholder: 'Search for a user...',
            width: '100%',
            allowClear: true,
            ajax: {
                url: '{{ route("admin.students-dt.search-users") }}',
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

// Delete phone confirmation
function deletePhoneConfirm(studentId, phoneId) {
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
                url: `/admin/students/${studentId}/phones/${phoneId}`,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
            loadPhones(studentId);
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Failed to delete phone.', 'error');
                }
            });
        }
    });
}
</script>
@endpush
