@extends('layouts.app')

@section('title', 'Positions Management')

@section('content')
<div class="container mx-auto px-4 py-6">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Positions Management</h1>
        @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
        <button onclick="openCreateModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
            <svg class="inline-block w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Position
        </button>
        @endif
    </div>

    <!-- Filter -->
    <div class="mb-4">
        <label class="mr-2">Status:</label>
        <select id="status-filter" class="border rounded px-3 py-2 dark:bg-gray-700 dark:text-white">
            <option value="all" selected>All Positions</option>
            <option value="active">Active Only</option>
            <option value="inactive">Inactive Only</option>
        </select>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="positions-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Populated via Ajax -->
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="position-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modal-title" class="text-xl font-bold text-gray-900 dark:text-white">Add Position</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="position-form" class="space-y-4">
            <input type="hidden" id="position-id">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name *</label>
                <input type="text" id="name" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <p class="mt-1 text-sm text-red-600" id="error-name"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                <input type="text" id="code"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <p class="mt-1 text-sm text-red-600" id="error-code"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea id="description" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                <p class="mt-1 text-sm text-red-600" id="error-description"></p>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 rounded-md border bg-white dark:bg-gray-700 dark:text-white hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Modal -->
<div id="view-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Position Details</h3>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="view-content" class="space-y-3"></div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeViewModal()" class="px-4 py-2 rounded-md bg-gray-200 dark:bg-gray-700 dark:text-white">Close</button>
        </div>
    </div>
</div>

<script>
const canWrite = {{ auth()->user()->hasAnyRole(['admin', 'manager', 'staff']) ? 'true' : 'false' }};
const canDelete = {{ auth()->user()->hasAnyRole(['admin', 'manager']) ? 'true' : 'false' }};
let statusFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
    loadPositions();
    document.getElementById('status-filter').addEventListener('change', function() {
        statusFilter = this.value;
        loadPositions();
    });
    document.getElementById('position-form').addEventListener('submit', submitForm);
});

function loadPositions() {
    fetch(`{{ route('admin.positions.index') }}?status=${statusFilter}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) renderPositions(data.data);
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error loading positions', 'error');
    });
}

function renderPositions(positions) {
    const tbody = document.getElementById('positions-table-body');
    if (positions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No positions found</td></tr>';
        return;
    }
    tbody.innerHTML = positions.map(pos => `
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">${pos.name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">${pos.code || '-'}</td>
            <td class="px-6 py-4 text-gray-900 dark:text-gray-100">${pos.description ? (pos.description.length > 50 ? pos.description.substring(0, 50) + '...' : pos.description) : '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span onclick="${canWrite ? `toggleStatus(${pos.id})` : ''}" 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${pos.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'} ${canWrite ? 'cursor-pointer hover:opacity-75' : ''}"
                    title="${canWrite ? 'Click to toggle status' : ''}">
                    ${pos.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button onclick="viewPosition(${pos.id})" class="text-indigo-600 hover:text-indigo-900" title="View">
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
                ${canWrite ? `
                <button onclick="editPosition(${pos.id})" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </button>
                ` : ''}
                ${canDelete ? `
                <button onclick="deletePosition(${pos.id})" class="text-red-600 hover:text-red-900" title="Delete">
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
                ` : ''}
            </td>
        </tr>
    `).join('');
}

function openCreateModal() {
    document.getElementById('modal-title').textContent = 'Add Position';
    document.getElementById('position-form').reset();
    document.getElementById('position-id').value = '';
    clearErrors();
    document.getElementById('position-modal').classList.remove('hidden');
}

function editPosition(id) {
    fetch(`{{ route('admin.positions.index') }}/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const pos = data.data;
            document.getElementById('modal-title').textContent = 'Edit Position';
            document.getElementById('position-id').value = pos.id;
            document.getElementById('name').value = pos.name;
            document.getElementById('code').value = pos.code || '';
            document.getElementById('description').value = pos.description || '';
            clearErrors();
            document.getElementById('position-modal').classList.remove('hidden');
        }
    });
}

function viewPosition(id) {
    fetch(`{{ route('admin.positions.index') }}/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const pos = data.data;
            document.getElementById('view-content').innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Name</p>
                        <p class="text-gray-900 dark:text-gray-100">${pos.name}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Code</p>
                        <p class="text-gray-900 dark:text-gray-100">${pos.code || '-'}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${pos.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${pos.is_active ? 'Active' : 'Inactive'}</span></p>
                    </div>
                    ${pos.description ? `
                    <div class="col-span-2">
                        <p class="text-sm font-medium text-gray-500">Description</p>
                        <p class="text-gray-900 dark:text-gray-100">${pos.description}</p>
                    </div>
                    ` : ''}
                    <div class="col-span-2"><hr class="my-4 border-gray-300"></div>
                    <div class="col-span-2"><h4 class="text-sm font-semibold text-gray-700 mb-3">📋 Audit Information</h4></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Created By</p>
                        <p class="text-gray-900 dark:text-gray-100">${pos.creator ? pos.creator.name : '-'}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Created At</p>
                        <p class="text-gray-900 dark:text-gray-100">${formatDateTime(pos.created_at)}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Updated By</p>
                        <p class="text-gray-900 dark:text-gray-100">${pos.updater ? pos.updater.name : '-'}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Updated At</p>
                        <p class="text-gray-900 dark:text-gray-100">${formatDateTime(pos.updated_at)}</p>
                    </div>
                </div>
            `;
            document.getElementById('view-modal').classList.remove('hidden');
        }
    });
}

function submitForm(e) {
    e.preventDefault();
    clearErrors();
    const id = document.getElementById('position-id').value;
    const url = id ? `{{ route('admin.positions.index') }}/${id}` : '{{ route('admin.positions.store') }}';
    const method = id ? 'PUT' : 'POST';
    const formData = {
        name: document.getElementById('name').value,
        code: document.getElementById('code').value,
        description: document.getElementById('description').value,
    };
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeModal();
            loadPositions();
        } else if (data.errors) {
            displayErrors(data.errors);
        }
    });
}

function deletePosition(id) {
    if (!confirm('Are you sure you want to deactivate this position?')) return;
    fetch(`{{ route('admin.positions.index') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadPositions();
        }
    });
}

function toggleStatus(id) {
    fetch(`{{ route('admin.positions.index') }}/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadPositions();
        }
    });
}

function closeModal() { document.getElementById('position-modal').classList.add('hidden'); }
function closeViewModal() { document.getElementById('view-modal').classList.add('hidden'); }
function clearErrors() { document.querySelectorAll('[id^="error-"]').forEach(el => el.textContent = ''); }
function displayErrors(errors) {
    for (const [field, messages] of Object.entries(errors)) {
        const errorEl = document.getElementById(`error-${field}`);
        if (errorEl) errorEl.textContent = messages[0];
    }
}
function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });
}
function showAlert(message, type) { alert(message); }
</script>
@endsection




@section('title', 'Positions Management')

@section('content')
<div class="container mx-auto px-4 py-6">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Positions Management</h1>
        @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
        <button onclick="openCreateModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
            <svg class="inline-block w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Position
        </button>
        @endif
    </div>

    <!-- Filter -->
    <div class="mb-4">
        <label class="mr-2">Status:</label>
        <select id="status-filter" class="border rounded px-3 py-2 dark:bg-gray-700 dark:text-white">
            <option value="all" selected>All Positions</option>
            <option value="active">Active Only</option>
            <option value="inactive">Inactive Only</option>
        </select>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="positions-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Populated via Ajax -->
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="position-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modal-title" class="text-xl font-bold text-gray-900 dark:text-white">Add Position</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="position-form" class="space-y-4">
            <input type="hidden" id="position-id">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name *</label>
                <input type="text" id="name" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <p class="mt-1 text-sm text-red-600" id="error-name"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                <input type="text" id="code"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <p class="mt-1 text-sm text-red-600" id="error-code"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea id="description" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                <p class="mt-1 text-sm text-red-600" id="error-description"></p>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 rounded-md border bg-white dark:bg-gray-700 dark:text-white hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Modal -->
<div id="view-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Position Details</h3>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="view-content" class="space-y-3"></div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeViewModal()" class="px-4 py-2 rounded-md bg-gray-200 dark:bg-gray-700 dark:text-white">Close</button>
        </div>
    </div>
</div>

<script>
const canWrite = {{ auth()->user()->hasAnyRole(['admin', 'manager', 'staff']) ? 'true' : 'false' }};
const canDelete = {{ auth()->user()->hasAnyRole(['admin', 'manager']) ? 'true' : 'false' }};
let statusFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
    loadPositions();
    document.getElementById('status-filter').addEventListener('change', function() {
        statusFilter = this.value;
        loadPositions();
    });
    document.getElementById('position-form').addEventListener('submit', submitForm);
});

function loadPositions() {
    fetch(`{{ route('admin.positions.index') }}?status=${statusFilter}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) renderPositions(data.data);
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error loading positions', 'error');
    });
}

function renderPositions(positions) {
    const tbody = document.getElementById('positions-table-body');
    if (positions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No positions found</td></tr>';
        return;
    }
    tbody.innerHTML = positions.map(pos => `
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">${pos.name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">${pos.code || '-'}</td>
            <td class="px-6 py-4 text-gray-900 dark:text-gray-100">${pos.description ? (pos.description.length > 50 ? pos.description.substring(0, 50) + '...' : pos.description) : '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span onclick="${canWrite ? `toggleStatus(${pos.id})` : ''}" 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${pos.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'} ${canWrite ? 'cursor-pointer hover:opacity-75' : ''}"
                    title="${canWrite ? 'Click to toggle status' : ''}">
                    ${pos.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button onclick="viewPosition(${pos.id})" class="text-indigo-600 hover:text-indigo-900" title="View">
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
                ${canWrite ? `
                <button onclick="editPosition(${pos.id})" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </button>
                ` : ''}
                ${canDelete ? `
                <button onclick="deletePosition(${pos.id})" class="text-red-600 hover:text-red-900" title="Delete">
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
                ` : ''}
            </td>
        </tr>
    `).join('');
}

function openCreateModal() {
    document.getElementById('modal-title').textContent = 'Add Position';
    document.getElementById('position-form').reset();
    document.getElementById('position-id').value = '';
    clearErrors();
    document.getElementById('position-modal').classList.remove('hidden');
}

function editPosition(id) {
    fetch(`{{ route('admin.positions.index') }}/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const pos = data.data;
            document.getElementById('modal-title').textContent = 'Edit Position';
            document.getElementById('position-id').value = pos.id;
            document.getElementById('name').value = pos.name;
            document.getElementById('code').value = pos.code || '';
            document.getElementById('description').value = pos.description || '';
            clearErrors();
            document.getElementById('position-modal').classList.remove('hidden');
        }
    });
}

function viewPosition(id) {
    fetch(`{{ route('admin.positions.index') }}/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const pos = data.data;
            document.getElementById('view-content').innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Name</p>
                        <p class="text-gray-900 dark:text-gray-100">${pos.name}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Code</p>
                        <p class="text-gray-900 dark:text-gray-100">${pos.code || '-'}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${pos.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${pos.is_active ? 'Active' : 'Inactive'}</span></p>
                    </div>
                    ${pos.description ? `
                    <div class="col-span-2">
                        <p class="text-sm font-medium text-gray-500">Description</p>
                        <p class="text-gray-900 dark:text-gray-100">${pos.description}</p>
                    </div>
                    ` : ''}
                    <div class="col-span-2"><hr class="my-4 border-gray-300"></div>
                    <div class="col-span-2"><h4 class="text-sm font-semibold text-gray-700 mb-3">📋 Audit Information</h4></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Created By</p>
                        <p class="text-gray-900 dark:text-gray-100">${pos.creator ? pos.creator.name : '-'}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Created At</p>
                        <p class="text-gray-900 dark:text-gray-100">${formatDateTime(pos.created_at)}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Updated By</p>
                        <p class="text-gray-900 dark:text-gray-100">${pos.updater ? pos.updater.name : '-'}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Updated At</p>
                        <p class="text-gray-900 dark:text-gray-100">${formatDateTime(pos.updated_at)}</p>
                    </div>
                </div>
            `;
            document.getElementById('view-modal').classList.remove('hidden');
        }
    });
}

function submitForm(e) {
    e.preventDefault();
    clearErrors();
    const id = document.getElementById('position-id').value;
    const url = id ? `{{ route('admin.positions.index') }}/${id}` : '{{ route('admin.positions.store') }}';
    const method = id ? 'PUT' : 'POST';
    const formData = {
        name: document.getElementById('name').value,
        code: document.getElementById('code').value,
        description: document.getElementById('description').value,
    };
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeModal();
            loadPositions();
        } else if (data.errors) {
            displayErrors(data.errors);
        }
    });
}

function deletePosition(id) {
    if (!confirm('Are you sure you want to deactivate this position?')) return;
    fetch(`{{ route('admin.positions.index') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadPositions();
        }
    });
}

function toggleStatus(id) {
    fetch(`{{ route('admin.positions.index') }}/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadPositions();
        }
    });
}

function closeModal() { document.getElementById('position-modal').classList.add('hidden'); }
function closeViewModal() { document.getElementById('view-modal').classList.add('hidden'); }
function clearErrors() { document.querySelectorAll('[id^="error-"]').forEach(el => el.textContent = ''); }
function displayErrors(errors) {
    for (const [field, messages] of Object.entries(errors)) {
        const errorEl = document.getElementById(`error-${field}`);
        if (errorEl) errorEl.textContent = messages[0];
    }
}
function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });
}
function showAlert(message, type) { alert(message); }
</script>
@endsection









