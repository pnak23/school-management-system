@extends('layouts.app')

@section('title', 'Library Shelves')

@section('content')
<div class="max-w-full mx-auto">
    {{-- Page Header --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Library Shelves</h1>
            <p class="text-gray-600 mt-1">Manage library shelf locations</p>
        </div>
        @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
        <button onclick="openCreateModal()" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Shelf
        </button>
        @endif
    </div>

    {{-- Alert Messages --}}
    <div id="alert-container" class="mb-4"></div>

    {{-- Filters and Search --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Search --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" id="search-input" placeholder="Search by code, location, or description..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status-filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                    <option value="active" selected>Active Only</option>
                    <option value="all">All Shelves</option>
                    <option value="inactive">Inactive Only</option>
                </select>
            </div>

            {{-- Per Page --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Records per page</label>
                <select id="per-page-select" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                    <option value="10">10</option>
                    <option value="15" selected>15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Shelves Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="shelves-table-body" class="bg-white divide-y divide-gray-200">
                    {{-- Data loaded via Ajax --}}
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex justify-center items-center">
                                <svg class="animate-spin h-8 w-8 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="ml-2">Loading shelves...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div id="pagination-container" class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
            <div id="pagination-info" class="text-sm text-gray-700">
                {{-- Pagination info will be inserted here --}}
            </div>
            <div id="pagination-buttons" class="flex gap-2">
                {{-- Pagination buttons will be inserted here --}}
            </div>
        </div>
    </div>
</div>

{{-- Include Modal --}}
@include('admin.library.shelves._form')

@push('scripts')
<script>
let currentPage = 1;
let currentEditingId = null;

// Load shelves on page load
document.addEventListener('DOMContentLoaded', function() {
    loadShelves();
    
    // Event listeners
    document.getElementById('search-input').addEventListener('input', debounce(function() {
        currentPage = 1;
        loadShelves();
    }, 500));
    
    document.getElementById('status-filter').addEventListener('change', function() {
        currentPage = 1;
        loadShelves();
    });
    
    document.getElementById('per-page-select').addEventListener('change', function() {
        currentPage = 1;
        loadShelves();
    });
    
    // Form submit handler
    document.getElementById('shelf-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveShelf();
    });
});

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

function loadShelves() {
    const search = document.getElementById('search-input').value;
    const status = document.getElementById('status-filter').value;
    const perPage = document.getElementById('per-page-select').value;
    
    const params = new URLSearchParams({
        page: currentPage,
        per_page: perPage,
        status: status,
        search: search
    });
    
    fetch(`{{ route('admin.library.shelves.index') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderShelvesTable(data.data);
            renderPagination(data.pagination);
        }
    })
    .catch(error => {
        console.error('Error loading shelves:', error);
        showAlert('Failed to load shelves', 'error');
    });
}

function renderShelvesTable(shelves) {
    const tbody = document.getElementById('shelves-table-body');
    
    if (shelves.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No shelves found</td></tr>';
        return;
    }
    
    tbody.innerHTML = shelves.map((shelf, index) => {
        const statusBadge = shelf.is_active 
            ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>'
            : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>';
        
        const location = shelf.location || '-';
        const description = shelf.description 
            ? (shelf.description.length > 50 ? shelf.description.substring(0, 50) + '...' : shelf.description)
            : '-';
        const createdAt = new Date(shelf.created_at).toLocaleDateString();
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${index + 1}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${shelf.code}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${location}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${description}</td>
                <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${createdAt}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex gap-2">
                        @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
                        <button onclick="editShelf(${shelf.id})" class="text-teal-600 hover:text-teal-900" title="Edit">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="toggleStatus(${shelf.id})" class="text-yellow-600 hover:text-yellow-900" title="Toggle Status">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                        </button>
                        @endif
                        @if(auth()->user()->hasAnyRole(['admin', 'manager']))
                        <button onclick="deleteShelf(${shelf.id})" class="text-red-600 hover:text-red-900" title="Delete">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function renderPagination(pagination) {
    const infoDiv = document.getElementById('pagination-info');
    const buttonsDiv = document.getElementById('pagination-buttons');
    
    const start = (pagination.current_page - 1) * pagination.per_page + 1;
    const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);
    
    infoDiv.innerHTML = `Showing ${start} to ${end} of ${pagination.total} shelves`;
    
    let buttonsHtml = '';
    
    if (pagination.current_page > 1) {
        buttonsHtml += `<button onclick="goToPage(${pagination.current_page - 1})" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-50">Previous</button>`;
    }
    
    for (let i = 1; i <= pagination.last_page; i++) {
        if (i === 1 || i === pagination.last_page || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            const activeClass = i === pagination.current_page ? 'bg-teal-600 text-white' : 'bg-white hover:bg-gray-50';
            buttonsHtml += `<button onclick="goToPage(${i})" class="px-3 py-1 border border-gray-300 rounded ${activeClass}">${i}</button>`;
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            buttonsHtml += `<span class="px-2">...</span>`;
        }
    }
    
    if (pagination.current_page < pagination.last_page) {
        buttonsHtml += `<button onclick="goToPage(${pagination.current_page + 1})" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-50">Next</button>`;
    }
    
    buttonsDiv.innerHTML = buttonsHtml;
}

function goToPage(page) {
    currentPage = page;
    loadShelves();
}

function openCreateModal() {
    currentEditingId = null;
    document.getElementById('modal-title').textContent = 'Add Shelf';
    document.getElementById('shelf-form').reset();
    document.getElementById('shelf-id').value = '';
    document.getElementById('form-errors').classList.add('hidden');
    document.getElementById('shelf-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('shelf-modal').classList.add('hidden');
    currentEditingId = null;
}

function editShelf(id) {
    currentEditingId = id;
    document.getElementById('modal-title').textContent = 'Edit Shelf';
    
    fetch(`{{ route('admin.library.shelves.index') }}/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const shelf = data.data;
            document.getElementById('shelf-id').value = shelf.id;
            document.getElementById('code').value = shelf.code;
            document.getElementById('location').value = shelf.location || '';
            document.getElementById('description').value = shelf.description || '';
            
            document.getElementById('form-errors').classList.add('hidden');
            document.getElementById('shelf-modal').classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error loading shelf:', error);
        showAlert('Failed to load shelf details', 'error');
    });
}

function saveShelf() {
    const form = document.getElementById('shelf-form');
    const formData = new FormData(form);
    const id = document.getElementById('shelf-id').value;
    
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');
    
    submitBtn.disabled = true;
    submitText.classList.add('hidden');
    submitSpinner.classList.remove('hidden');
    
    const url = id ? `{{ route('admin.library.shelves.index') }}/${id}` : '{{ route('admin.library.shelves.store') }}';
    const method = 'POST';
    
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
            showAlert(data.message, 'success');
            closeModal();
            loadShelves();
        } else {
            if (data.errors) {
                displayErrors(data.errors);
            } else {
                showAlert(data.message || 'Failed to save shelf', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error saving shelf:', error);
        showAlert('Failed to save shelf', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitText.classList.remove('hidden');
        submitSpinner.classList.add('hidden');
    });
}

function deleteShelf(id) {
    @if(auth()->user()->hasRole('admin'))
    // Admin: Show choice between soft delete and permanent delete
    const modalHtml = `
        <div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Delete Shelf</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">Are you sure you want to delete this shelf?</p>
                        <p class="text-xs text-gray-400 mt-2">Choose the type of deletion:</p>
                    </div>
                    <div class="flex flex-col gap-2 px-4 py-3">
                        <button onclick="performSoftDelete(${id})" class="px-4 py-2 bg-yellow-500 text-white text-base font-medium rounded-lg w-full shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-300">
                            Deactivate (Soft Delete)
                        </button>
                        <button onclick="performForceDelete(${id})" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-lg w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Permanent Delete
                        </button>
                        <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-lg w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    @else
    // Non-admin: Simple confirmation for soft delete only
    if (!confirm('Are you sure you want to delete this shelf?')) {
        return;
    }
    
    performSoftDelete(id);
    @endif
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-modal');
    if (modal) {
        modal.remove();
    }
}

function performSoftDelete(id) {
    closeDeleteModal();
    
    fetch(`{{ route('admin.library.shelves.index') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadShelves();
        } else {
            showAlert(data.message || 'Failed to delete shelf', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting shelf:', error);
        showAlert('Failed to delete shelf', 'error');
    });
}

function performForceDelete(id) {
    closeDeleteModal();
    
    if (!confirm('⚠️ WARNING: This will PERMANENTLY delete this shelf. This action CANNOT be undone!\n\nAre you absolutely sure?')) {
        return;
    }
    
    fetch(`/admin/library/shelves/${id}/force-delete`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadShelves();
        } else {
            showAlert(data.message || 'Failed to permanently delete shelf', 'error');
        }
    })
    .catch(error => {
        console.error('Error force deleting shelf:', error);
        showAlert('Failed to permanently delete shelf', 'error');
    });
}

function toggleStatus(id) {
    fetch(`{{ route('admin.library.shelves.index') }}/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadShelves();
        } else {
            showAlert(data.message || 'Failed to toggle status', 'error');
        }
    })
    .catch(error => {
        console.error('Error toggling status:', error);
        showAlert('Failed to toggle status', 'error');
    });
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

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const bgColor = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
    
    const alert = document.createElement('div');
    alert.className = `p-4 rounded-lg border ${bgColor} mb-4`;
    alert.textContent = message;
    
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>
@endpush
@endsection


