@extends('layouts.app')

@section('title', 'Library Books Report')

@push('styles')
<style>
    .book-card {
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .book-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .book-cover {
        height: 220px;
        object-fit: cover;
        width: 100%;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
    }
    
    .book-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .book-title {
        font-weight: 600;
        font-size: 1rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.8em;
    }
    
    .book-authors {
        color: #666;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .book-meta {
        font-size: 0.85rem;
        color: #999;
    }
    
    .availability-badge {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }
    
    .skeleton-loader {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    
    .search-header {
        background: #2c3e50;
        color: white;
        padding: 1.5rem;
        border-radius: 0.5rem;
        margin-bottom: 2rem;
    }
    
    .result-count {
        color: rgba(255,255,255,0.9);
        font-size: 0.95rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-md-12">
            <h2><i class="fas fa-book-open"></i> Library Books</h2>
            <p class="text-muted">Browse and search library books. Reserve unavailable books.</p>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-header">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="categoryFilter" class="form-label text-white">Category</label>
                <select id="categoryFilter" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="shelfFilter" class="form-label text-white">Shelf</label>
                <select id="shelfFilter" class="form-select">
                    <option value="">All Shelves</option>
                    @foreach($shelves as $shelf)
                        <option value="{{ $shelf->id }}">
                            {{ $shelf->code }}@if($shelf->location) - {{ $shelf->location }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="searchInput" class="form-label text-white">Search</label>
                <input type="text" id="searchInput" class="form-control" 
                       placeholder="Search by Title / Author / Category / ISBN">
            </div>
            <div class="col-md-2">
                <label class="form-label text-white">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light w-100" onclick="handleSearch()">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" class="btn btn-outline-light w-100" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <p class="result-count mb-0" id="resultCount">Loading...</p>
            </div>
        </div>
    </div>

    <!-- Books Grid -->
    <div id="booksGrid" class="row g-4">
        <!-- Books will be loaded here via AJAX -->
    </div>

    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-md-12">
            <nav aria-label="Books pagination">
                <ul class="pagination justify-content-center" id="pagination">
                    <!-- Pagination will be generated here -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Book Details Modal -->
<div class="modal fade" id="bookDetailsModal" tabindex="-1" aria-labelledby="bookDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="bookDetailsModalLabel">
                    <i class="fas fa-book"></i> Book Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="bookDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading book details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <a href="#" id="borrowBookLink" class="btn btn-success" style="display: none;">
                    <i class="fas fa-book-open"></i> Borrow Book
                </a>
                <a href="#" id="startReadingLink" class="btn btn-info" target="_blank">
                    <i class="fas fa-book-reader"></i> Start Reading
                </a>
                <button type="button" id="editBookBtn" class="btn btn-primary" onclick="openEditBookModal()">
                    <i class="fas fa-edit"></i> Edit Book
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Book Modal -->
<div class="modal fade" id="editBookModal" tabindex="-1" aria-labelledby="editBookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editBookModalLabel">
                    <i class="fas fa-edit"></i> Edit Book
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editBookForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="editBookId" name="item_id">
                    
                    <!-- Error Display -->
                    <div id="editBookErrors" class="alert alert-danger d-none" role="alert">
                        <ul id="editBookErrorList" class="mb-0"></ul>
                    </div>
                    
                    <div class="row">
                        <!-- Title -->
                        <div class="col-md-12 mb-3">
                            <label for="editTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editTitle" name="title" required maxlength="255">
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <!-- ISBN -->
                        <div class="col-md-6 mb-3">
                            <label for="editIsbn" class="form-label">ISBN</label>
                            <input type="text" class="form-control" id="editIsbn" name="isbn" maxlength="20">
                        </div>
                        
                        <!-- Edition -->
                        <div class="col-md-6 mb-3">
                            <label for="editEdition" class="form-label">Edition</label>
                            <input type="text" class="form-control" id="editEdition" name="edition" maxlength="50">
                        </div>
                        
                        <!-- Published Year -->
                        <div class="col-md-6 mb-3">
                            <label for="editPublishedYear" class="form-label">Published Year</label>
                            <input type="number" class="form-control" id="editPublishedYear" name="published_year" min="1000" max="2155">
                        </div>
                        
                        <!-- Language -->
                        <div class="col-md-6 mb-3">
                            <label for="editLanguage" class="form-label">Language</label>
                            <input type="text" class="form-control" id="editLanguage" name="language" maxlength="50">
                        </div>
                        
                        <!-- Category -->
                        <div class="col-md-6 mb-3">
                            <label for="editCategoryId" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="editCategoryId" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <!-- Publisher -->
                        <div class="col-md-6 mb-3">
                            <label for="editPublisherId" class="form-label">Publisher</label>
                            <select class="form-select" id="editPublisherId" name="publisher_id">
                                <option value="">Select Publisher (optional)</option>
                                @foreach($publishers as $publisher)
                                    <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Authors -->
                        <div class="col-md-12 mb-3">
                            <label for="editAuthorIds" class="form-label">Authors (Select Multiple)</label>
                            <select class="form-select" id="editAuthorIds" name="author_ids[]" multiple size="5">
                                @foreach($authors as $author)
                                    <option value="{{ $author->id }}">{{ $author->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple authors</small>
                        </div>
                        
                        <!-- Cover Image -->
                        <div class="col-md-12 mb-3">
                            <label for="editCoverImage" class="form-label">Cover Image</label>
                            <input type="file" class="form-control" id="editCoverImage" name="cover_image" accept="image/*" onchange="previewEditCoverImage(this)">
                            <small class="text-muted">Max 2MB, formats: JPEG, PNG, GIF. Leave empty to keep current image.</small>
                            <div id="editCoverPreview" class="mt-2"></div>
                        </div>
                        
                        <!-- Description -->
                        <div class="col-md-12 mb-3">
                            <label for="editDescription" class="form-label">Description / Synopsis</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="5"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveEditBook()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentPage = 1;
let currentCategory = '';
let currentShelf = '';
let currentSearch = '';
let currentPerPage = 12;

$(document).ready(function() {
    // Load initial data
    loadData({page: 1});
    
    // Category change
    $('#categoryFilter').on('change', function() {
        currentCategory = $(this).val();
        loadData({page: 1});
    });
    
    // Shelf change
    $('#shelfFilter').on('change', function() {
        currentShelf = $(this).val();
        loadData({page: 1});
    });
    
    // Search on Enter key
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            handleSearch();
        }
    });
});

function handleSearch() {
    currentSearch = $('#searchInput').val().trim();
    loadData({page: 1});
}

function clearFilters() {
    $('#categoryFilter').val('');
    $('#shelfFilter').val('');
    $('#searchInput').val('');
    currentCategory = '';
    currentShelf = '';
    currentSearch = '';
    loadData({page: 1});
}

function loadData(params = {}) {
    const page = params.page || currentPage;
    currentPage = page;
    
    // Show loading skeleton
    showSkeleton();
    
    // Build query params
    const queryParams = {
        page: page,
        per_page: currentPerPage
    };
    
    if (currentCategory) {
        queryParams.category_id = currentCategory;
    }
    
    if (currentShelf) {
        queryParams.shelf_id = currentShelf;
    }
    
    if (currentSearch) {
        queryParams.q = currentSearch;
    }
    
    // Make AJAX request
    $.ajax({
        url: '{{ route("admin.library.books_report.data") }}',
        type: 'GET',
        data: queryParams,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                renderBooks(response.data);
                renderPagination(response.meta);
                updateResultCount(response.meta);
            } else {
                Swal.fire('Error', 'Failed to load books.', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error loading books:', xhr);
            Swal.fire('Error', 'Failed to load books. Please try again.', 'error');
            $('#booksGrid').html('<div class="col-12"><div class="alert alert-danger">Failed to load books.</div></div>');
        }
    });
}

function showSkeleton() {
    let skeletonHtml = '';
    for (let i = 0; i < currentPerPage; i++) {
        skeletonHtml += `
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card book-card skeleton-loader" style="height: 400px;"></div>
            </div>
        `;
    }
    $('#booksGrid').html(skeletonHtml);
}

function renderBooks(books) {
    if (books.length === 0) {
        $('#booksGrid').html(`
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No books found.
                </div>
            </div>
        `);
        return;
    }
    
    let html = '';
    books.forEach(function(book) {
        // Determine badge color
        let badgeClass = 'bg-secondary';
        let badgeText = book.availability_status;
        if (book.availability_status === 'Available') {
            badgeClass = 'bg-success';
            badgeText = `Available (${book.available_copies})`;
        } else if (book.availability_status === 'Borrowed') {
            badgeClass = 'bg-warning';
            badgeText = 'Borrowed';
        }
        
        html += `
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card book-card">
                    <div class="book-cover">
                        <img src="${book.cover_url}" alt="${book.title}" onerror="this.onerror=null; this.src='{{ asset('images/default-book.svg') }}'">
                    </div>
                    <div class="card-body">
                        <h6 class="book-title">${escapeHtml(book.title)}</h6>
                        <p class="book-authors mb-2">
                            <i class="fas fa-user-edit"></i> ${escapeHtml(book.authors)}
                        </p>
                        <p class="book-meta mb-2">
                            <span class="badge bg-light text-dark">${escapeHtml(book.category)}</span>
                            ${book.isbn !== '-' ? `<small class="text-muted">ISBN: ${escapeHtml(book.isbn)}</small>` : ''}
                        </p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge ${badgeClass} availability-badge">${badgeText}</span>
                            <small class="text-muted">${book.available_copies}/${book.total_copies} copies</small>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('qr.library.start-reading.form') }}?library_item_id=${book.id}" class="btn btn-sm btn-info flex-fill" target="_blank">
                                <i class="fas fa-book-reader"></i> Start Reading
                            </a>
                            ${book.availability_status === 'Available' && book.available_copies > 0 ? `
                                <a href="{{ route('qr.library.loan-request.form') }}?library_item_id=${book.id}" class="btn btn-sm btn-success flex-fill">
                                    <i class="fas fa-book-open"></i> Borrow
                                </a>
                            ` : ''}
                            ${book.can_reserve ? `
                                <button class="btn btn-sm btn-warning flex-fill" onclick="reserveBook(${book.id})">
                                    <i class="fas fa-bookmark"></i> Reserve
                                </button>
                            ` : ''}
                            <button class="btn btn-sm btn-outline-primary" onclick="viewBookDetails(${book.id})">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#booksGrid').html(html);
}

function renderPagination(meta) {
    if (meta.last_page <= 1) {
        $('#pagination').html('');
        return;
    }
    
    let html = '';
    
    // Previous button
    html += `
        <li class="page-item ${meta.page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadData({page: ${meta.page - 1}}); return false;">Previous</a>
        </li>
    `;
    
    // Page numbers
    let startPage = Math.max(1, meta.page - 2);
    let endPage = Math.min(meta.last_page, meta.page + 2);
    
    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadData({page: 1}); return false;">1</a></li>`;
        if (startPage > 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === meta.page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadData({page: ${i}}); return false;">${i}</a>
            </li>
        `;
    }
    
    if (endPage < meta.last_page) {
        if (endPage < meta.last_page - 1) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadData({page: ${meta.last_page}}); return false;">${meta.last_page}</a></li>`;
    }
    
    // Next button
    html += `
        <li class="page-item ${meta.page === meta.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadData({page: ${meta.page + 1}}); return false;">Next</a>
        </li>
    `;
    
    $('#pagination').html(html);
}

function updateResultCount(meta) {
    const start = (meta.page - 1) * meta.per_page + 1;
    const end = Math.min(meta.page * meta.per_page, meta.total);
    $('#resultCount').text(`Showing ${start}-${end} of ${meta.total} books`);
}

function reserveBook(itemId) {
    // Check if user is logged in
    if (!{{ auth()->check() ? 'true' : 'false' }}) {
        Swal.fire({
            icon: 'warning',
            title: 'Login Required',
            text: 'Please login first to reserve a book.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    Swal.fire({
        title: 'Reserve Book?',
        text: 'Are you sure you want to reserve this book?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Reserve',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Make reservation request
            $.ajax({
                url: `/admin/library/books-report/${itemId}/reserve`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reserved!',
                            text: response.message,
                            timer: 3000,
                            showConfirmButton: false
                        });
                        // Reload current page to update availability
                        loadData({page: currentPage});
                    } else {
                        Swal.fire('Error', response.message || 'Failed to reserve book.', 'error');
                    }
                },
                error: function(xhr) {
                    let message = 'Failed to reserve book.';
                    if (xhr.status === 401) {
                        message = 'Please login first to reserve a book.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', message, 'error');
                }
            });
        }
    });
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
}

function viewBookDetails(bookId) {
    // Show modal with loading state
    const modalElement = document.getElementById('bookDetailsModal');
    if (!modalElement) {
        console.error('Book details modal element not found');
        return;
    }
    
    // Check if Bootstrap is available
    let modal;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Get existing modal instance or create new one
        modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    } else if (typeof $ !== 'undefined' && $.fn.modal) {
        // Fallback to jQuery Bootstrap modal
        $(modalElement).modal('show');
        modal = { show: function() { $(modalElement).modal('show'); } };
    } else {
        console.error('Bootstrap modal is not available');
        return;
    }
    
    modal.show();
    
    // Reset content
    $('#bookDetailsContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading book details...</p>
        </div>
    `);
    
    // Fetch book details
    $.ajax({
        url: `/admin/library/items/${bookId}`,
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success && response.data) {
                const book = response.data;
                renderBookDetails(book);
                // Store book data for edit modal
                window.currentBookData = book;
                // Set start reading link with book ID
                $('#startReadingLink').attr('href', `{{ route('qr.library.start-reading.form') }}?library_item_id=${book.id}`);
                // Set borrow link with book ID (only if available)
                if (book.availability_status === 'Available' && book.available_copies > 0) {
                    $('#borrowBookLink').attr('href', `{{ route('qr.library.loan-request.form') }}?library_item_id=${book.id}`).show();
                } else {
                    $('#borrowBookLink').hide();
                }
            } else {
                $('#bookDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Failed to load book details.
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error('Error loading book details:', xhr);
            $('#bookDetailsContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Error loading book details. Please try again.
                </div>
            `);
        }
    });
}

function renderBookDetails(book) {
    // Get cover image URL
    let coverUrl = book.cover_image_url || '{{ asset("images/default-book.svg") }}';
    if (!coverUrl || coverUrl === 'null' || coverUrl === null) {
        coverUrl = '{{ asset("images/default-book.svg") }}';
    }
    
    // Format authors
    let authorsHtml = '<p class="text-muted mb-0">No authors listed</p>';
    if (book.authors && book.authors.length > 0) {
        const authorNames = book.authors.map(a => {
            const role = a.role ? `<small class="text-muted">(${a.role})</small>` : '';
            return `${escapeHtml(a.name)} ${role}`;
        }).join(', ');
        authorsHtml = `<p class="mb-0"><strong>Authors:</strong> ${authorNames}</p>`;
    }
    
    // Format dates
    const createdDate = book.created_at ? new Date(book.created_at).toLocaleDateString() : 'N/A';
    const updatedDate = book.updated_at ? new Date(book.updated_at).toLocaleDateString() : 'N/A';
    
    // Status badge
    const statusBadge = book.is_active 
        ? '<span class="badge bg-success">Active</span>' 
        : '<span class="badge bg-secondary">Inactive</span>';
    
    const html = `
        <div class="row">
            <!-- Cover Image -->
            <div class="col-md-4 text-center mb-3">
                <img src="${coverUrl}" alt="${escapeHtml(book.title)}" 
                     class="img-fluid rounded shadow-sm" 
                     style="max-height: 300px; object-fit: cover;"
                     onerror="this.src='{{ asset('images/default-book.svg') }}'">
            </div>
            
            <!-- Book Details -->
            <div class="col-md-8">
                <h4 class="mb-3">${escapeHtml(book.title)}</h4>
                
                <div class="mb-3">
                    ${statusBadge}
                    <span class="badge bg-info ms-2">${book.copies_count || 0} Copies</span>
                    <span class="badge bg-success ms-2">${book.available_copies || 0} Available</span>
                </div>
                
                <hr>
                
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>ISBN:</strong></div>
                    <div class="col-sm-8">${escapeHtml(book.isbn || 'N/A')}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Edition:</strong></div>
                    <div class="col-sm-8">${escapeHtml(book.edition || 'N/A')}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Published Year:</strong></div>
                    <div class="col-sm-8">${book.published_year || 'N/A'}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Language:</strong></div>
                    <div class="col-sm-8">${escapeHtml(book.language || 'N/A')}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Category:</strong></div>
                    <div class="col-sm-8">${escapeHtml(book.category_name || 'N/A')}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Publisher:</strong></div>
                    <div class="col-sm-8">${escapeHtml(book.publisher_name || 'N/A')}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Shelf(s):</strong></div>
                    <div class="col-sm-8">
                        ${book.shelves && book.shelves.length > 0 
                            ? book.shelves.map(s => {
                                const location = s.location ? ` - ${escapeHtml(s.location)}` : '';
                                return `<span class="badge bg-secondary me-1">${escapeHtml(s.code)}${location}</span>`;
                            }).join('')
                            : '<span class="text-muted">No Shelf Assigned</span>'}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Authors:</strong></div>
                    <div class="col-sm-8">${authorsHtml}</div>
                </div>
                
                ${book.description ? `
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p class="text-muted mt-2">${escapeHtml(book.description)}</p>
                    </div>
                ` : ''}
                
                <hr>
                
                <div class="row">
                    <div class="col-sm-6">
                        <small class="text-muted">
                            <strong>Created:</strong> ${createdDate}<br>
                            <strong>By:</strong> ${escapeHtml(book.created_by || 'System')}
                        </small>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted">
                            <strong>Updated:</strong> ${updatedDate}<br>
                            <strong>By:</strong> ${escapeHtml(book.updated_by || 'System')}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#bookDetailsContent').html(html);
}

// Open edit book modal
function openEditBookModal() {
    if (!window.currentBookData) {
        Swal.fire('Error', 'No book data available. Please view book details first.', 'error');
        return;
    }
    
    const book = window.currentBookData;
    
    // Populate form
    $('#editBookId').val(book.id);
    $('#editTitle').val(book.title || '');
    $('#editIsbn').val(book.isbn || '');
    $('#editEdition').val(book.edition || '');
    $('#editPublishedYear').val(book.published_year || '');
    $('#editLanguage').val(book.language || '');
    $('#editCategoryId').val(book.category_id || '');
    $('#editPublisherId').val(book.publisher_id || '');
    $('#editDescription').val(book.description || '');
    
    // Set authors (multi-select)
    $('#editAuthorIds').val(book.author_ids || []);
    
    // Show cover preview
    let coverPreviewHtml = '';
    if (book.cover_image_url && book.cover_image_url !== 'null') {
        coverPreviewHtml = `
            <div class="mt-2">
                <p class="text-muted small mb-1">Current Cover:</p>
                <img src="${book.cover_image_url}" alt="Current Cover" class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">
            </div>
        `;
    }
    $('#editCoverPreview').html(coverPreviewHtml);
    
    // Clear errors
    $('#editBookErrors').addClass('d-none');
    $('#editBookErrorList').html('');
    $('.form-control, .form-select').removeClass('is-invalid');
    
    // Show modal
    const modalElement = document.getElementById('editBookModal');
    if (!modalElement) {
        console.error('Edit book modal element not found');
        Swal.fire('Error', 'Edit modal not found. Please refresh the page.', 'error');
        return;
    }
    
    // Check if Bootstrap is available
    let modal;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Get existing modal instance or create new one
        modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    } else if (typeof $ !== 'undefined' && $.fn.modal) {
        // Fallback to jQuery Bootstrap modal
        $(modalElement).modal('show');
        modal = { show: function() { $(modalElement).modal('show'); } };
    } else {
        console.error('Bootstrap modal is not available');
        Swal.fire('Error', 'Bootstrap modal is not loaded. Please refresh the page.', 'error');
        return;
    }
    
    modal.show();
}

// Preview cover image when file selected
function previewEditCoverImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#editCoverPreview').html(`
                <div class="mt-2">
                    <p class="text-muted small mb-1">New Cover Preview:</p>
                    <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                </div>
            `);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Save edited book
function saveEditBook() {
    const form = document.getElementById('editBookForm');
    const formData = new FormData(form);
    const bookId = $('#editBookId').val();
    
    // Add _method for PUT
    formData.append('_method', 'PUT');
    
    // Clear previous errors
    $('#editBookErrors').addClass('d-none');
    $('#editBookErrorList').html('');
    $('.form-control, .form-select').removeClass('is-invalid');
    
    // Show loading
    Swal.fire({
        title: 'Saving...',
        html: 'Please wait while we update the book.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: `/admin/library/items/${bookId}`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Book updated successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Close modals
                const editModalElement = document.getElementById('editBookModal');
                const detailsModalElement = document.getElementById('bookDetailsModal');
                
                if (editModalElement) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const editModal = bootstrap.Modal.getInstance(editModalElement);
                        if (editModal) editModal.hide();
                    } else if (typeof $ !== 'undefined' && $.fn.modal) {
                        $(editModalElement).modal('hide');
                    }
                }
                
                if (detailsModalElement) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const detailsModal = bootstrap.Modal.getInstance(detailsModalElement);
                        if (detailsModal) detailsModal.hide();
                    } else if (typeof $ !== 'undefined' && $.fn.modal) {
                        $(detailsModalElement).modal('hide');
                    }
                }
                
                // Reload book data
                loadData({page: currentPage});
            }
        },
        error: function(xhr) {
            Swal.close();
            
            if (xhr.status === 422) {
                // Validation errors
                const errors = xhr.responseJSON.errors;
                let errorHtml = '<ul class="mb-0">';
                
                $.each(errors, function(field, messages) {
                    $.each(messages, function(i, message) {
                        errorHtml += `<li>${escapeHtml(message)}</li>`;
                    });
                    
                    // Field-specific error highlighting
                    const fieldMap = {
                        'title': 'editTitle',
                        'category_id': 'editCategoryId',
                        'isbn': 'editIsbn',
                        'edition': 'editEdition',
                        'published_year': 'editPublishedYear',
                        'language': 'editLanguage',
                        'publisher_id': 'editPublisherId',
                        'author_ids': 'editAuthorIds',
                        'cover_image': 'editCoverImage',
                        'description': 'editDescription'
                    };
                    
                    if (fieldMap[field]) {
                        $(`#${fieldMap[field]}`).addClass('is-invalid');
                    }
                });
                
                errorHtml += '</ul>';
                $('#editBookErrorList').html(errorHtml);
                $('#editBookErrors').removeClass('d-none');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to update book. Please try again.'
                });
            }
        }
    });
}
</script>
@endpush

