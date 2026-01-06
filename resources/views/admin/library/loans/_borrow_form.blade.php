<!-- Borrow Book Modal -->
<div class="modal fade" id="borrowModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-book-reader"></i> Borrow Book</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="borrowForm">
                    <div class="row g-3">
                        <!-- Step 1: Find Book (Barcode OR Search) -->
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary"><i class="fas fa-book"></i> Step 1: Find Book</h6>
                                    
                                    <!-- Option A: Scan Barcode -->
                                    <div class="mb-3">
                                        <label class="form-label"><i class="fas fa-barcode"></i> Option A: Scan Barcode (Fast)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                            <input type="text" class="form-control" id="barcode" 
                                                   placeholder="Scan or enter barcode..." autofocus>
                                            <button type="button" class="btn btn-primary" onclick="lookupBarcode()">
                                                <i class="fas fa-search"></i> Lookup
                                            </button>
                                        </div>
                                        <small class="text-muted">For barcode scanner users</small>
                                    </div>
                                    
                                    <!-- OR Divider -->
                                    <div class="text-center my-2">
                                        <small class="text-muted">OR</small>
                                    </div>
                                    
                                    <!-- Option B: Search Book -->
                                    <div>
                                        <label class="form-label"><i class="fas fa-search"></i> Option B: Search Book (by Title, ISBN, etc.)</label>
                                        <select class="form-select" id="bookSearch" style="width: 100%;">
                                            <option value="">Type to search by title, ISBN, edition, year, language...</option>
                                        </select>
                                        <small class="text-muted">Search by title, ISBN, edition, published year, or language</small>
                                        
                                        <!-- Fallback: Manual search button if Select2 fails -->
                                        <div id="manualSearchFallback" style="display: none;" class="mt-2">
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control" id="manualSearchInput" 
                                                       placeholder="Enter title, ISBN, or other details...">
                                                <button type="button" class="btn btn-primary btn-sm" onclick="manualBookSearch()">
                                                    <i class="fas fa-search"></i> Search
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Book Info (shown after barcode lookup or book selection) -->
                        <div class="col-md-12" id="bookInfoSection" style="display: none;">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-book"></i> Book Found:</h6>
                                <div id="bookInfoDisplay"></div>
                            </div>
                        </div>
                        
                        <!-- Available Copies Selection (for search results with multiple copies) -->
                        <div class="col-md-12" id="copySelectionSection" style="display: none;">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-copy"></i> Select Copy to Borrow</h6>
                                </div>
                                <div class="card-body">
                                    <div id="copySelectionList"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Select Borrower -->
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary"><i class="fas fa-user"></i> Step 2: Select Borrower</h6>
                                    
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label for="borrowerType" class="form-label">Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="borrowerType" required onchange="loadBorrowerOptions()">
                                                <option value="">Select Type</option>
                                                <option value="student">Student</option>
                                                <option value="teacher">Teacher</option>
                                                <option value="staff">Staff</option>
                                                <option value="guest">Guest</option>
                                            </select>
                                        </div>
                                        <div class="col-md-8">
                                            <label for="borrowerSelect" class="form-label">Select Borrower <span class="text-danger">*</span></label>
                                            <select class="form-select" id="borrowerSelect" required disabled>
                                                <option value="">First, select a type...</option>
                                            </select>
                                            <small class="text-muted">Select type first, then choose borrower from dropdown</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Loan Details -->
                        <div class="col-md-6">
                            <label for="borrowedAt" class="form-label">Borrowed Date/Time</label>
                            <input type="datetime-local" class="form-control" id="borrowedAt">
                            <small class="text-muted">Leave empty for current time</small>
                        </div>

                        <div class="col-md-6">
                            <label for="dueDate" class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="dueDate" required>
                            <div class="btn-group btn-group-sm mt-1" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="setDueDays(7)">7 days</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setDueDays(14)">14 days</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setDueDays(30)">30 days</button>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="borrowNote" class="form-label">Note (Optional)</label>
                            <textarea class="form-control" id="borrowNote" rows="2" maxlength="1000" 
                                      placeholder="Add any notes about this loan..."></textarea>
                        </div>

                        <!-- Processed By Staff -->
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-secondary"><i class="fas fa-user-tie"></i> Processed By (Staff)</h6>
                                    
                                    <div class="mb-2">
                                        <label class="form-label">Current Staff</label>
                                        <input type="text" class="form-control" value="{{ $authStaff ? ($authStaff->english_name ?? $authStaff->khmer_name) . ' (' . ($authStaff->staff_code ?? 'N/A') . ')' : auth()->user()->name . ' (No staff record)' }}" readonly>
                                        <small class="text-muted">This loan will be processed by you</small>
                                    </div>

                                    @if(auth()->user()->hasAnyRole(['admin', 'manager']))
                                    <div>
                                        <label for="processedByOverride" class="form-label">Override Processed By (Optional)</label>
                                        <select class="form-select" id="processedByOverride">
                                            <option value="">Use current staff (default)</option>
                                            @foreach($staffList as $staff)
                                                <option value="{{ $staff->id }}">
                                                    {{ $staff->khmer_name ?? $staff->english_name }} ({{ $staff->staff_code ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Only override if processing on behalf of another staff</small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveBorrow()" id="saveBorrowBtn">
                    <i class="fas fa-check"></i> Confirm Borrow
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let copyId = null;

// Open borrow modal
function openBorrowModal() {
    $('#borrowForm')[0].reset();
    $('#barcode').val('');
    $('#bookInfoSection').hide();
    $('#copySelectionSection').hide();
    $('#borrowerSelect').prop('disabled', true).html('<option value="">First, select a type...</option>');
    
    // Destroy Select2 if exists
    if ($('#borrowerSelect').hasClass('select2-hidden-accessible')) {
        $('#borrowerSelect').select2('destroy');
    }
    
    // Destroy book search Select2 if exists
    if ($('#bookSearch').hasClass('select2-hidden-accessible')) {
        $('#bookSearch').select2('destroy');
    }
    
    copyId = null;
    
    // Set default due date (14 days from now)
    setDueDays(14);
    
    $('#borrowModal').modal('show');
    
    // Initialize book search after modal is shown
    setTimeout(() => {
        initBookSearch();
    }, 300);
    
    // Scanner Mode: Enhanced focus and setup
    if (typeof scannerModeEnabled !== 'undefined' && scannerModeEnabled) {
        setTimeout(() => {
            const barcodeInput = $('#barcode');
            barcodeInput.focus();
            barcodeInput.select(); // Select any existing text
        }, 500);
    } else {
        // Normal mode: just focus
        setTimeout(() => $('#barcode').focus(), 500);
    }
}

// Initialize book search with Select2
function initBookSearch() {
    // Check if Select2 is available
    if (typeof $.fn.select2 === 'undefined') {
        console.warn('Select2 is not loaded. Showing manual search fallback.');
        $('#bookSearch').hide();
        $('#manualSearchFallback').show();
        return;
    }
    
    // Select2 is available, hide fallback
    $('#bookSearch').show();
    $('#manualSearchFallback').hide();
    
    // Destroy existing Select2 if present
    if ($('#bookSearch').hasClass('select2-hidden-accessible')) {
        $('#bookSearch').select2('destroy');
    }
    
    $('#bookSearch').select2({
        dropdownParent: $('#borrowModal'),
        placeholder: 'Type to search by title, ISBN, edition, year, language...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '{{ route("admin.library.loans.search-books") }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (data) {
                if (data.success) {
                    return {
                        results: data.results
                    };
                }
                return { results: [] };
            },
            cache: true
        }
    }).on('select2:select', function (e) {
        const bookData = e.params.data;
        handleBookSelected(bookData);
    });
}

// Manual book search fallback (if Select2 not available)
function manualBookSearch() {
    const query = $('#manualSearchInput').val().trim();
    
    if (query.length < 2) {
        Swal.fire('Error', 'Please enter at least 2 characters to search.', 'error');
        return;
    }
    
    // Show loading
    Swal.fire({
        title: 'Searching...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '{{ route("admin.library.loans.search-books") }}',
        type: 'GET',
        data: { q: query },
        success: function(response) {
            Swal.close();
            
            if (response.success && response.results.length > 0) {
                // Show results in a modal
                let html = '<div class="list-group" style="max-height: 400px; overflow-y: auto;">';
                
                response.results.forEach(function(book) {
                    html += `
                        <button type="button" class="list-group-item list-group-item-action text-start" 
                                onclick='selectManualBook(${JSON.stringify(book)})'>
                            <h6 class="mb-1">${book.title}</h6>
                            <small class="text-muted">
                                ${book.isbn ? 'ISBN: ' + book.isbn + ' | ' : ''}
                                ${book.edition ? 'Ed: ' + book.edition + ' | ' : ''}
                                <span class="badge bg-success">${book.available_copies} available</span>
                            </small>
                        </button>
                    `;
                });
                
                html += '</div>';
                
                Swal.fire({
                    title: 'Search Results',
                    html: html,
                    width: '600px',
                    showCloseButton: true,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('No Results', 'No books found matching your search.', 'info');
            }
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire('Error', 'Failed to search books. Please try again.', 'error');
        }
    });
}

// Select book from manual search results
function selectManualBook(bookData) {
    Swal.close();
    handleBookSelected(bookData);
}

// Handle book selection from search
function handleBookSelected(bookData) {
    // Display book info
    let html = `
        <p class="mb-1"><strong>Title:</strong> ${bookData.title}</p>
        ${bookData.isbn ? '<p class="mb-1"><strong>ISBN:</strong> ' + bookData.isbn + '</p>' : ''}
        ${bookData.edition ? '<p class="mb-1"><strong>Edition:</strong> ' + bookData.edition + '</p>' : ''}
        ${bookData.published_year ? '<p class="mb-1"><strong>Year:</strong> ' + bookData.published_year + '</p>' : ''}
        ${bookData.language ? '<p class="mb-1"><strong>Language:</strong> ' + bookData.language + '</p>' : ''}
        <p class="mb-0">
            <strong>Copies:</strong> 
            <span class="badge bg-success">${bookData.available_copies} available</span>
            ${bookData.total_copies > bookData.available_copies ? '<span class="badge bg-warning ms-1">' + (bookData.total_copies - bookData.available_copies) + ' on loan</span>' : ''}
        </p>
    `;
    
    $('#bookInfoDisplay').html(html);
    $('#bookInfoSection').show();
    
    // Filter only AVAILABLE copies for borrowing
    const availableCopies = bookData.copies ? bookData.copies.filter(c => c.status === 'available') : [];
    
    // Show copy selection if available copies exist
    if (availableCopies.length > 0) {
        let copiesHtml = '<div class="list-group">';
        
        availableCopies.forEach(function(copy) {
            const conditionBadge = copy.condition === 'new' ? 'success' : 
                                   copy.condition === 'good' ? 'primary' : 
                                   copy.condition === 'fair' ? 'warning' : 'secondary';
            
            copiesHtml += `
                <button type="button" class="list-group-item list-group-item-action" 
                        onclick="selectCopy(${copy.id}, '${copy.barcode}')">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-barcode"></i> <strong>${copy.barcode}</strong>
                            <span class="badge bg-${conditionBadge} ms-2">${copy.condition || 'N/A'}</span>
                            <span class="badge bg-success ms-1">Available</span>
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </button>
            `;
        });
        
        copiesHtml += '</div>';
        
        $('#copySelectionList').html(copiesHtml);
        $('#copySelectionSection').show();
        
        // Auto-select if only one available copy
        if (availableCopies.length === 1) {
            selectCopy(availableCopies[0].id, availableCopies[0].barcode);
        }
    } else if (bookData.total_copies > 0) {
        // Book has copies but all are on loan
        $('#copySelectionList').html(`
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>All copies are currently on loan.</strong><br>
                This book has ${bookData.total_copies} copy/copies but none are available right now.
            </div>
        `);
        $('#copySelectionSection').show();
    } else {
        // Book has no copies at all
        $('#copySelectionList').html(`
            <div class="alert alert-danger">
                <i class="fas fa-times-circle"></i>
                <strong>No physical copies exist for this book.</strong><br>
                Please add copies in the Book Copies module first.
            </div>
        `);
        $('#copySelectionSection').show();
    }
}

// Select a specific copy
function selectCopy(id, barcode) {
    copyId = id;
    
    // Update UI to show selected copy
    $('#copySelectionList .list-group-item').removeClass('active');
    event.currentTarget.classList.add('active');
    
    // Fill barcode field
    $('#barcode').val(barcode);
    
    // Enable save button
    $('#saveBorrowBtn').prop('disabled', false);
    
    // Scroll to borrower section
    $('html, body').animate({
        scrollTop: $('#borrowerType').offset().top - 100
    }, 500);
}


// Load borrower options based on type
function loadBorrowerOptions() {
    const type = $('#borrowerType').val();
    
    if (!type) {
        $('#borrowerSelect').prop('disabled', true).html('<option value="">First, select a type...</option>');
        return;
    }

    // Show loading
    $('#borrowerSelect').prop('disabled', true).html('<option value="">Loading...</option>');

    $.ajax({
        url: '{{ route("admin.library.loans.search-borrowers") }}',
        type: 'GET',
        data: {
            borrower_type: type,
            q: '' // Empty query to get all
        },
        success: function(response) {
            // Handle both response formats:
            // 1. Direct array: [ {...}, {...} ]
            // 2. Object with results: { success: true, results: [...] }
            let borrowers = [];
            
            if (Array.isArray(response)) {
                // Response is directly an array
                borrowers = response;
            } else if (response.success && Array.isArray(response.results)) {
                // Response has success and results properties
                borrowers = response.results;
            } else if (response.results && Array.isArray(response.results)) {
                // Response has results property
                borrowers = response.results;
            }
            
            if (borrowers.length > 0) {
                let options = '<option value="">Select ' + type.charAt(0).toUpperCase() + type.slice(1) + '...</option>';
                
                borrowers.forEach(function(borrower) {
                    options += `<option value="${borrower.id}">${borrower.text}</option>`;
                });
                
                $('#borrowerSelect').html(options).prop('disabled', false);
                
                // Initialize Select2 for searchable dropdown (with check)
                if (typeof $.fn.select2 !== 'undefined') {
                    // Destroy existing Select2 if any
                    if ($('#borrowerSelect').hasClass('select2-hidden-accessible')) {
                        $('#borrowerSelect').select2('destroy');
                    }
                    
                    $('#borrowerSelect').select2({
                        placeholder: 'Search and select ' + type + '...',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#borrowModal'),
                        minimumResultsForSearch: 0, // Always show search box
                        language: {
                            noResults: function() {
                                return "No matching " + type + " found";
                            },
                            searching: function() {
                                return "Searching...";
                            }
                        }
                    });
                    
                    console.log('Select2 initialized for borrower dropdown with ' + borrowers.length + ' options');
                } else {
                    console.warn('Select2 not available, using basic dropdown');
                    // Still functional, just no search box
                }
            } else {
                $('#borrowerSelect').html('<option value="">No ' + type + 's found</option>').prop('disabled', true);
                Swal.fire('No Records', 'No active ' + type + 's found in the system.', 'info');
            }
        },
        error: function(xhr) {
            console.error('Error loading borrowers:', xhr);
            $('#borrowerSelect').html('<option value="">Error loading ' + type + 's</option>').prop('disabled', true);
            Swal.fire('Error', 'Failed to load ' + type + ' list.', 'error');
        }
    });
}

// Lookup barcode
function lookupBarcode() {
    const barcode = $('#barcode').val().trim();
    
    if (!barcode) {
        Swal.fire('Error', 'Please enter a barcode.', 'error');
        return;
    }

    // Prevent double lookup in Scanner Mode
    if (typeof isLookingUp !== 'undefined' && isLookingUp) {
        return;
    }
    
    if (typeof isLookingUp !== 'undefined') {
        isLookingUp = true;
    }

    $.ajax({
        url: `/admin/library/loans/find-copy/${barcode}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const copy = response.data;
                copyId = copy.id;
                
                // Display book info
                let html = `
                    <p class="mb-1"><strong>Title:</strong> ${copy.item.title}</p>
                    <p class="mb-1"><strong>Barcode:</strong> ${copy.barcode}</p>
                    ${copy.item.isbn ? '<p class="mb-1"><strong>ISBN:</strong> ' + copy.item.isbn + '</p>' : ''}
                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-${copy.can_borrow ? 'success' : 'danger'}">${copy.status}</span></p>
                    ${copy.shelf ? '<p class="mb-0"><strong>Shelf:</strong> ' + copy.shelf + '</p>' : ''}
                `;
                
                $('#bookInfoDisplay').html(html);
                $('#bookInfoSection').show();
                
                if (!copy.can_borrow) {
                    Swal.fire('Cannot Borrow', 'This book cannot be borrowed. Current status: ' + copy.status, 'warning');
                    $('#saveBorrowBtn').prop('disabled', true);
                    
                    // Scanner Mode: Keep focus on barcode for retry
                    if (typeof scannerModeEnabled !== 'undefined' && scannerModeEnabled) {
                        setTimeout(() => {
                            $('#barcode').focus().select();
                        }, 500);
                    }
                } else {
                    $('#saveBorrowBtn').prop('disabled', false);
                    
                    // Scanner Mode: Auto-focus to borrower type dropdown
                    if (typeof scannerModeEnabled !== 'undefined' && scannerModeEnabled) {
                        setTimeout(() => {
                            $('#borrowerType').focus();
                        }, 300);
                    }
                }
            }
            
            if (typeof isLookingUp !== 'undefined') {
                isLookingUp = false;
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Barcode not found.';
            Swal.fire('Error', message, 'error');
            $('#bookInfoSection').hide();
            copyId = null;
            
            // Scanner Mode: Keep focus on barcode and select text for retry
            if (typeof scannerModeEnabled !== 'undefined' && scannerModeEnabled) {
                setTimeout(() => {
                    $('#barcode').focus().select();
                }, 500);
            }
            
            if (typeof isLookingUp !== 'undefined') {
                isLookingUp = false;
            }
        }
    });
}

// ========================================
// SCANNER MODE SUPPORT
// ========================================

// Global flag to prevent double lookup
let isLookingUp = false;
let scannerDebounceTimer = null;

// Enhanced barcode input handler (supports both manual and scanner input)
$(document).ready(function() {
    const barcodeInput = $('#barcode');
    
    // Handle Enter key (scanners typically send this)
    barcodeInput.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            
            // In Scanner Mode, Enter triggers immediate lookup
            if (typeof scannerModeEnabled !== 'undefined' && scannerModeEnabled) {
                const value = $(this).val().trim();
                if (value.length >= 4 && !isLookingUp) {
                    lookupBarcode();
                }
            } else {
                // Normal mode: just trigger lookup
                lookupBarcode();
            }
        }
    });
    
    // Scanner Mode: Detect fast input (scanner types quickly)
    barcodeInput.on('input', function() {
        // Only in Scanner Mode
        if (typeof scannerModeEnabled === 'undefined' || !scannerModeEnabled) {
            return;
        }
        
        clearTimeout(scannerDebounceTimer);
        
        const value = $(this).val().trim();
        
        // If barcode reaches minimum length and user stops typing (150ms = scanner finished)
        if (value.length >= 4) {
            scannerDebounceTimer = setTimeout(() => {
                if (!isLookingUp) {
                    lookupBarcode();
                }
            }, 150);
        }
    });
});

// Set due date (days from now)
function setDueDays(days) {
    const date = new Date();
    date.setDate(date.getDate() + days);
    const dateStr = date.toISOString().split('T')[0];
    $('#dueDate').val(dateStr);
}

// Save borrow
function saveBorrow() {
    const borrowerId = $('#borrowerSelect').val();
    const borrowerType = $('#borrowerType').val();
    const barcode = $('#barcode').val().trim();
    const dueDate = $('#dueDate').val();
    const processedByOverride = $('#processedByOverride').val();

    // Validation
    if (!barcode) {
        Swal.fire('Error', 'Please scan or enter a barcode.', 'error');
        return;
    }

    if (!copyId) {
        Swal.fire('Error', 'Please lookup the barcode first.', 'error');
        return;
    }

    if (!borrowerType) {
        Swal.fire('Error', 'Please select borrower type.', 'error');
        return;
    }

    if (!borrowerId) {
        Swal.fire('Error', 'Please select a borrower.', 'error');
        return;
    }

    if (!dueDate) {
        Swal.fire('Error', 'Please set due date.', 'error');
        return;
    }

    // Prepare form data
    const formData = {
        borrower_type: borrowerType,
        borrower_id: borrowerId,
        barcode: barcode,
        borrowed_at: $('#borrowedAt').val() || null,
        due_date: dueDate,
        note: $('#borrowNote').val()
    };

    // Only include processed_by_staff_id if admin/manager selected an override
    if (processedByOverride) {
        formData.processed_by_staff_id = processedByOverride;
    }

    // If override selected, confirm
    if (processedByOverride) {
        Swal.fire({
            title: 'Staff Override Selected',
            text: 'You selected a staff override. This loan will be processed by the selected staff, not you. Continue?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                submitBorrow(formData);
            }
        });
    } else {
        submitBorrow(formData);
    }
}

// Submit borrow request
function submitBorrow(formData) {
    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();

    $.ajax({
        url: '{{ route("admin.library.loans.store") }}',
        type: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#borrowModal').modal('hide');
                Swal.fire('Success', response.message, 'success');
                loansTable.ajax.reload();
                
                // Refresh dashboard stats
                if (typeof fetchLoanStats === 'function') {
                    fetchLoanStats();
                }
                
                // Refresh trend chart
                if (typeof fetchLoanTrends === 'function') {
                    fetchLoanTrends();
                }
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMsg = 'Validation errors:<br>';
                $.each(errors, function(field, messages) {
                    errorMsg += '- ' + messages[0] + '<br>';
                });
                Swal.fire('Validation Error', errorMsg, 'error');
            } else {
                const message = xhr.responseJSON?.message || 'Failed to borrow book.';
                Swal.fire('Error', message, 'error');
            }
        }
    });
}
</script>
@endpush

