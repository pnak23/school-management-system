/**
 * DataTable Helper Module
 * 
 * Provides reusable functions for initializing server-side DataTables
 * and handling common operations like delete confirmations
 */

/**
 * Initialize a server-side DataTable
 * 
 * @param {string} selector - CSS selector for the table element
 * @param {string} ajaxUrl - URL for Ajax data source
 * @param {Array} columns - DataTables columns configuration
 * @param {Object} options - Additional DataTable options (optional)
 * @returns {Object} DataTable instance
 */
export function initServerSideDataTable(selector, ajaxUrl, columns, options = {}) {
    const defaultOptions = {
        processing: true,
        serverSide: true,
        ajax: {
            url: ajaxUrl,
            type: 'GET',
            error: function (xhr, error, code) {
                console.error('DataTable Ajax error:', error, code);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Loading Data',
                    text: 'Failed to load data from server. Please refresh the page.',
                    confirmButtonColor: '#d33'
                });
            }
        },
        columns: columns,
        responsive: true,
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No data available",
            zeroRecords: "No matching records found",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            search: "Search:",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        order: [[0, 'desc']], // Default sort by first column descending
        drawCallback: function(settings) {
            // Re-attach event handlers after table redraw
            attachRowEventHandlers();
        }
    };

    // Merge default options with custom options
    const finalOptions = { ...defaultOptions, ...options };

    // Initialize DataTable
    const table = $(selector).DataTable(finalOptions);

    return table;
}

/**
 * Show a SweetAlert2 confirmation dialog for delete action
 * 
 * @param {number|string} id - ID of the item to delete
 * @param {string} deleteUrl - URL endpoint for delete action
 * @param {Object} table - DataTable instance to reload after delete
 * @param {string} itemName - Name of item type (e.g., "user", "student")
 * @returns {Promise}
 */
export function confirmDelete(id, deleteUrl, table, itemName = 'item') {
    return Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete this ${itemName}. This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Perform delete request
            return fetch(deleteUrl, {
                method: 'DELETE',
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
                        title: 'Deleted!',
                        text: data.message || `${itemName.charAt(0).toUpperCase() + itemName.slice(1)} has been deleted.`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Reload DataTable
                    if (table) {
                        table.ajax.reload(null, false); // false = stay on current page
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to delete item.',
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
}

/**
 * Show a success toast notification
 * 
 * @param {string} message - Success message to display
 */
export function showSuccessToast(message) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: 'success',
        title: message
    });
}

/**
 * Show an error toast notification
 * 
 * @param {string} message - Error message to display
 */
export function showErrorToast(message) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    Toast.fire({
        icon: 'error',
        title: message
    });
}

/**
 * Attach event handlers to table rows
 * Call this function after DataTable draws/redraws
 */
function attachRowEventHandlers() {
    // This function can be customized per page
    // Example handlers are attached in the page-specific scripts
}

/**
 * Format date for display
 * 
 * @param {string} dateString - Date string to format
 * @returns {string} Formatted date
 */
export function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

/**
 * Format datetime for display
 * 
 * @param {string} dateString - Datetime string to format
 * @returns {string} Formatted datetime
 */
export function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

