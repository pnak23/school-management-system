@extends('layouts.app')

@section('title', 'Students Management - DataTable')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Students Management</h1>
            <p class="text-gray-600 mt-1">Manage student records with server-side DataTables</p>
        </div>
        @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
        <button onclick="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Student
        </button>
        @endif
    </div>

    <!-- DataTable Card -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">All Students</h2>
        </div>
        
        <div class="p-4">
            <div class="overflow-x-auto">
                <table id="students-table" class="table table-bordered datatable w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khmer Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">English Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sex</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Data will be loaded via Ajax -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">DataTable Features</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong>Server-side processing</strong> - Fast loading even with thousands of records</li>
                        <li><strong>Real-time search</strong> - Search across all columns instantly</li>
                        <li><strong>SweetAlert2 confirmations</strong> - Beautiful delete/deactivate dialogs</li>
                        <li><strong>Responsive design</strong> - Works on mobile and tablets</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Student Modal -->
<div id="student-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-lg bg-white mb-10">
        <!-- Modal Header -->
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 id="modal-title" class="text-2xl font-bold text-gray-900">Add Student</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="student-form" enctype="multipart/form-data" class="mt-4">
            <input type="hidden" id="student-id" name="student_id">
            
            <!-- Error Display -->
            <div id="form-errors" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul id="error-list" class="list-disc list-inside text-red-600 text-sm"></ul>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Khmer Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Khmer Name <span class="text-red-500">*</span></label>
                    <input type="text" id="khmer_name" name="khmer_name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <!-- English Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">English Name</label>
                    <input type="text" id="english_name" name="english_name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <!-- Date of Birth -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input type="date" id="dob" name="dob"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <!-- Sex -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sex <span class="text-red-500">*</span></label>
                    <select id="sex" name="sex" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Select Sex</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                </div>

                <!-- Student Code -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student Code</label>
                    <input type="text" id="code" name="code"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="text" id="phone" name="phone"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Primary phone (optional)</p>
                </div>

                <!-- Photo -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                    <input type="file" id="photo" name="photo" accept="image/*"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        onchange="previewPhoto(event)">
                    <div id="photo-preview" class="mt-2 hidden">
                        <img id="preview-image" src="" alt="Preview" class="h-32 w-32 object-cover rounded-lg border">
                    </div>
                </div>

                <!-- Note -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                    <textarea id="note" name="note" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Cancel
                </button>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <span id="submit-text">Save Student</span>
                    <span id="submit-spinner" class="hidden">
                        <svg class="inline animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Declare global variables
let currentEditingId = null;
let studentsTable = null;

document.addEventListener('DOMContentLoaded', function() {
    // Check if required libraries are loaded
    if (typeof $ === 'undefined') {
        console.error('❌ jQuery is not loaded. Run: npm install jquery --save && npm run dev');
        alert('jQuery is not loaded!\n\n1. Run: npm install jquery datatables.net sweetalert2 --save\n2. Run: npm run dev\n3. Keep terminal open\n4. Refresh page (Ctrl+Shift+R)');
        return;
    }
    
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('❌ DataTables plugin is not loaded. Run: npm install datatables.net --save && npm run dev');
        alert('DataTables not loaded!\n\n1. Run: npm install datatables.net datatables.net-dt --save\n2. Run: npm run dev\n3. Refresh page');
        return;
    }
    
    console.log('✅ jQuery loaded, version:', $.fn.jquery);
    console.log('✅ DataTables loaded');

    // Initialize DataTable directly
    studentsTable = $('#students-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.students-dt.index') }}',
            type: 'GET'
        },
        order: [[0, 'desc']],
        pageLength: 15,
        columns: [
            { data: 'id', name: 'id', width: '60px' },
            { data: 'khmer_name', name: 'khmer_name' },
            { 
                data: 'english_name', 
                name: 'english_name',
                render: function(data) {
                    return data || '-';
                }
            },
            { data: 'sex_display', name: 'sex', width: '80px' },
            { 
                data: 'code', 
                name: 'code',
                render: function(data) {
                    return data || '-';
                }
            },
            { data: 'phone', name: 'phone', orderable: false, searchable: false },
            { data: 'status', name: 'is_active', orderable: true, searchable: false, width: '100px' },
            { data: 'created_at_formatted', name: 'created_at', width: '120px' },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '200px' }
        ],
        language: {
            emptyTable: "No students found."
        }
    });

    // Handle View button click
    $(document).on('click', '.btn-view-student', function() {
        const studentId = $(this).data('id');

        Swal.fire({
            title: 'View Student',
            text: `Loading student details for ID: ${studentId}...`,
            icon: 'info',
            showConfirmButton: false,
            timer: 1500
        });

        // Place detailed modal loading logic here if needed
    });

    // Handle Edit button click
    $(document).on('click', '.btn-edit-student', function() {
        const studentId = $(this).data('id');
        openEditModal(studentId);
    });

    // Handle Deactivate button click
    $(document).on('click', '.btn-deactivate-student', function() {
        const studentId = $(this).data('id');
        const deactivateUrl = `{{ route('admin.students-dt.index') }}/${studentId}/deactivate`;

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to deactivate this student. They can be reactivated later.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, deactivate!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deactivating...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(deactivateUrl, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deactivated!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        studentsTable.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to deactivate student.',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(error => {
                    console.error('Deactivate error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while deactivating. Please try again.',
                        confirmButtonColor: '#d33'
                    });
                });
            }
        });
    });

    // Handle Delete button click (hard delete - admin only)
    $(document).on('click', '.btn-delete-student', function() {
        const studentId = $(this).data('id');
        const deleteUrl = `{{ route('admin.students-dt.index') }}/${studentId}`;
        
        // Fallback simple confirmation dialog, since confirmDelete is likely in missing students-dt.js
        Swal.fire({
            title: 'Delete Student?',
            text: 'Are you sure you want to permanently delete this student?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#d33'
        }).then(result => {
            if (result.isConfirmed) {
                fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.message || 'Student deleted.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        studentsTable.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to delete student.',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while deleting. Please try again.',
                        confirmButtonColor: '#d33'
                    });
                });
            }
        });
    });

    // Form submission
    $('#student-form').on('submit', function(e) {
        e.preventDefault();
        saveStudent();
    });
});

// Functions accessible globally
function openCreateModal() {
    currentEditingId = null;
    document.getElementById('modal-title').textContent = 'Add Student';
    document.getElementById('student-form').reset();
    document.getElementById('student-id').value = '';
    document.getElementById('photo-preview').classList.add('hidden');
    document.getElementById('form-errors').classList.add('hidden');
    document.getElementById('student-modal').classList.remove('hidden');
}

function openEditModal(studentId) {
    currentEditingId = studentId;
    document.getElementById('modal-title').textContent = 'Edit Student';
    
    fetch(`{{ route('admin.students-dt.index') }}/${studentId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const student = data.data;
            document.getElementById('student-id').value = student.id;
            document.getElementById('khmer_name').value = student.khmer_name;
            document.getElementById('english_name').value = student.english_name || '';
            document.getElementById('dob').value = student.dob || '';
            document.getElementById('sex').value = student.sex;
            document.getElementById('code').value = student.code || '';
            document.getElementById('note').value = student.note || '';

            const primaryPhone = student.phones && student.phones.length > 0 ? student.phones.find(p => p.is_primary) || student.phones[0] : null;
            document.getElementById('phone').value = primaryPhone ? primaryPhone.phone : '';

            if (student.photo) {
                document.getElementById('photo-preview').classList.remove('hidden');
                document.getElementById('preview-image').src = `/storage/${student.photo}`;
            } else {
                document.getElementById('photo-preview').classList.add('hidden');
                document.getElementById('preview-image').src = '';
            }

            document.getElementById('form-errors').classList.add('hidden');
            document.getElementById('student-modal').classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error loading student:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load student details'
        });
    });
}

function closeModal() {
    document.getElementById('student-modal').classList.add('hidden');
    currentEditingId = null;
}

function saveStudent() {
    const form = document.getElementById('student-form');
    const formData = new FormData(form);
    const id = document.getElementById('student-id').value;

    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');

    submitBtn.disabled = true;
    submitText.classList.add('hidden');
    submitSpinner.classList.remove('hidden');

    const url = id ? `{{ route('admin.students-dt.index') }}/${id}` : '{{ route('admin.students-dt.store') }}';
    const method = id ? 'POST' : 'POST';

    if (id) {
        formData.append('_method', 'PUT');
    }

    fetch(url, {
        method: method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
            closeModal();
            $('#students-table').DataTable().ajax.reload(null, false);
        } else {
            if (data.errors) {
                displayErrors(data.errors);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to save student'
                });
            }
        }
    })
    .catch(error => {
        console.error('Error saving student:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to save student'
        });
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitText.classList.remove('hidden');
        submitSpinner.classList.add('hidden');
    });
}

function previewPhoto(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photo-preview').classList.remove('hidden');
            document.getElementById('preview-image').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function displayErrors(errors) {
    const errorList = document.getElementById('error-list');
    const formErrors = document.getElementById('form-errors');

    errorList.innerHTML = '';

    Object.values(errors).forEach(errorArray => {
        errorArray.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
    });

    formErrors.classList.remove('hidden');
}
</script>
@endpush

@push('styles')
<style>
    /* Custom DataTable styling to match Tailwind theme */
    /* It's fine to keep this as is, since Tailwind + DataTables will apply */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        @apply border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        @apply px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 mr-1;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        @apply bg-indigo-600 text-white border-indigo-600;
    }

    .dataTables_wrapper .dataTables_info {
        @apply text-sm text-gray-700;
    }
</style>
@endpush

