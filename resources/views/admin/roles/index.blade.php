@extends('layouts.app')

@section('title', 'Manage Roles - School Management System')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Roles</h1>
            <p class="text-gray-600 mt-1">View and manage system roles</p>
        </div>
        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
            + Add Role
        </button>
    </div>

    <!-- Success/Error Messages -->
    <div id="messageContainer" class="mb-4"></div>

    <!-- Roles Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="rolesTableBody" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Loading roles...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Load roles when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadRoles();
});

// Load roles from API
async function loadRoles() {
    try {
        const response = await fetch('{{ route("admin.roles.index") }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to load roles');
        }
        
        const data = await response.json();
        displayRoles(data.data);
    } catch (error) {
        console.error('Error loading roles:', error);
        showMessage('Failed to load roles', 'error');
        document.getElementById('rolesTableBody').innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-red-600">
                    Error loading roles. Please refresh the page.
                </td>
            </tr>
        `;
    }
}

// Display roles in table
function displayRoles(roles) {
    const tbody = document.getElementById('rolesTableBody');
    
    if (!roles || roles.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    No roles found.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = roles.map(role => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${escapeHtml(role.name)}</div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-500">${escapeHtml(role.description || 'No description')}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${role.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${role.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${role.users_count || 0} users
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button onclick="editRole(${role.id})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                <button onclick="deleteRole(${role.id})" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
        </tr>
    `).join('');
}

// Show message
function showMessage(message, type = 'success') {
    const container = document.getElementById('messageContainer');
    const bgColor = type === 'success' ? 'bg-green-50 border-green-400 text-green-700' : 'bg-red-50 border-red-400 text-red-700';
    
    container.innerHTML = `
        <div class="${bgColor} border-l-4 p-4 rounded">
            <p class="text-sm">${escapeHtml(message)}</p>
        </div>
    `;
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Edit role (placeholder)
function editRole(id) {
    alert('Edit role functionality - Role ID: ' + id);
}

// Delete role (placeholder)
function deleteRole(id) {
    if (confirm('Are you sure you want to delete this role?')) {
        alert('Delete role functionality - Role ID: ' + id);
    }
}
</script>
@endpush
@endsection
