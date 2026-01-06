@extends('layouts.app')

@section('title', 'Sample Users - DataTable Demo')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h1 class="text-3xl font-bold text-gray-900">Users Management</h1>
        <p class="text-gray-600 mt-1">Demonstration of server-side DataTables with jQuery and SweetAlert2</p>
    </div>

    <!-- DataTable Card -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">All Users</h2>
        </div>
        
        <div class="p-4">
            <div class="overflow-x-auto">
                <table id="users-table" class="table table-bordered datatable w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
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
                <h3 class="text-sm font-medium text-blue-800">DataTable Stack Features</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong>Server-side processing</strong> - Handles large datasets efficiently</li>
                        <li><strong>Responsive design</strong> - Adapts to mobile screens</li>
                        <li><strong>SweetAlert2 integration</strong> - Beautiful confirmation dialogs</li>
                        <li><strong>Reusable helpers</strong> - Clean code patterns for new modules</li>
                        <li><strong>Vite bundling</strong> - No CDN dependencies, faster loading</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Wait for DOM to be ready
    $(document).ready(function() {
        
        // Define columns configuration
        const columns = [
            { 
                data: 'id', 
                name: 'id',
                width: '60px'
            },
            { 
                data: 'name', 
                name: 'name' 
            },
            { 
                data: 'email', 
                name: 'email' 
            },
            { 
                data: 'roles', 
                name: 'roles',
                orderable: false,
                searchable: false
            },
            { 
                data: 'status', 
                name: 'is_active',
                orderable: true,
                searchable: false
            },
            { 
                data: 'created_at', 
                name: 'created_at',
                render: function(data) {
                    if (!data) return '-';
                    const date = new Date(data);
                    return date.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                }
            },
            { 
                data: 'action', 
                name: 'action',
                orderable: false,
                searchable: false,
                width: '150px'
            }
        ];

        // Initialize DataTable using helper function
        const usersTable = initServerSideDataTable(
            '#users-table',
            '{{ route('admin.sample-users.data') }}',
            columns,
            {
                order: [[0, 'desc']], // Sort by ID descending
                pageLength: 15
            }
        );

        // Handle Edit button click
        $(document).on('click', '.edit-btn', function() {
            const userId = $(this).data('id');
            
            Swal.fire({
                title: 'Edit User',
                text: `You clicked edit for user ID: ${userId}`,
                icon: 'info',
                confirmButtonText: 'OK'
            });
            
            // In a real application, you would:
            // - Open a modal with edit form
            // - Or redirect to edit page: window.location.href = `/admin/users/${userId}/edit`;
        });

        // Handle Delete button click
        $(document).on('click', '.delete-btn', function() {
            const userId = $(this).data('id');
            const deleteUrl = `{{ route('admin.sample-users.index') }}/${userId}`;
            
            // Use the reusable confirmDelete helper
            confirmDelete(userId, deleteUrl, usersTable, 'user');
        });

        // Example: Show success toast on page load (for testing)
        // Uncomment the line below to test
        // showSuccessToast('DataTable loaded successfully!');
    });
</script>
@endpush

@push('styles')
<style>
    /* Custom DataTable styling to match your Tailwind theme */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        @apply border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        @apply px-3 py-1 border border-gray-300 rounded hover:bg-gray-50;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        @apply bg-indigo-600 text-white border-indigo-600;
    }

    .dataTables_wrapper .dataTables_info {
        @apply text-sm text-gray-700;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            @apply mb-4;
        }
    }
</style>
@endpush




@section('title', 'Sample Users - DataTable Demo')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h1 class="text-3xl font-bold text-gray-900">Users Management</h1>
        <p class="text-gray-600 mt-1">Demonstration of server-side DataTables with jQuery and SweetAlert2</p>
    </div>

    <!-- DataTable Card -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">All Users</h2>
        </div>
        
        <div class="p-4">
            <div class="overflow-x-auto">
                <table id="users-table" class="table table-bordered datatable w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
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
                <h3 class="text-sm font-medium text-blue-800">DataTable Stack Features</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong>Server-side processing</strong> - Handles large datasets efficiently</li>
                        <li><strong>Responsive design</strong> - Adapts to mobile screens</li>
                        <li><strong>SweetAlert2 integration</strong> - Beautiful confirmation dialogs</li>
                        <li><strong>Reusable helpers</strong> - Clean code patterns for new modules</li>
                        <li><strong>Vite bundling</strong> - No CDN dependencies, faster loading</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Wait for DOM to be ready
    $(document).ready(function() {
        
        // Define columns configuration
        const columns = [
            { 
                data: 'id', 
                name: 'id',
                width: '60px'
            },
            { 
                data: 'name', 
                name: 'name' 
            },
            { 
                data: 'email', 
                name: 'email' 
            },
            { 
                data: 'roles', 
                name: 'roles',
                orderable: false,
                searchable: false
            },
            { 
                data: 'status', 
                name: 'is_active',
                orderable: true,
                searchable: false
            },
            { 
                data: 'created_at', 
                name: 'created_at',
                render: function(data) {
                    if (!data) return '-';
                    const date = new Date(data);
                    return date.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                }
            },
            { 
                data: 'action', 
                name: 'action',
                orderable: false,
                searchable: false,
                width: '150px'
            }
        ];

        // Initialize DataTable using helper function
        const usersTable = initServerSideDataTable(
            '#users-table',
            '{{ route('admin.sample-users.data') }}',
            columns,
            {
                order: [[0, 'desc']], // Sort by ID descending
                pageLength: 15
            }
        );

        // Handle Edit button click
        $(document).on('click', '.edit-btn', function() {
            const userId = $(this).data('id');
            
            Swal.fire({
                title: 'Edit User',
                text: `You clicked edit for user ID: ${userId}`,
                icon: 'info',
                confirmButtonText: 'OK'
            });
            
            // In a real application, you would:
            // - Open a modal with edit form
            // - Or redirect to edit page: window.location.href = `/admin/users/${userId}/edit`;
        });

        // Handle Delete button click
        $(document).on('click', '.delete-btn', function() {
            const userId = $(this).data('id');
            const deleteUrl = `{{ route('admin.sample-users.index') }}/${userId}`;
            
            // Use the reusable confirmDelete helper
            confirmDelete(userId, deleteUrl, usersTable, 'user');
        });

        // Example: Show success toast on page load (for testing)
        // Uncomment the line below to test
        // showSuccessToast('DataTable loaded successfully!');
    });
</script>
@endpush

@push('styles')
<style>
    /* Custom DataTable styling to match your Tailwind theme */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        @apply border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        @apply px-3 py-1 border border-gray-300 rounded hover:bg-gray-50;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        @apply bg-indigo-600 text-white border-indigo-600;
    }

    .dataTables_wrapper .dataTables_info {
        @apply text-sm text-gray-700;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            @apply mb-4;
        }
    }
</style>
@endpush









