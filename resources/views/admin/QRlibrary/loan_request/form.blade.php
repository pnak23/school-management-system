<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Borrow Book - Library System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }

        .container {
            max-width: 600px;
        }

        .main-card {
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            background: white;
        }

        .header-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .header-section h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .header-section .subtitle {
            font-size: 0.95rem;
            opacity: 0.95;
        }

        .content-section {
            padding: 30px 25px;
        }

        .user-info-card {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #28a745;
        }

        .user-info-card h5 {
            color: #155724;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: #333;
            font-weight: 500;
        }

        .badge-borrower {
            font-size: 0.85rem;
            padding: 5px 12px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-select, .form-control {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px;
            font-size: 1rem;
        }

        .form-select:focus, .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .btn-borrow {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 50px;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.2s;
        }

        .btn-borrow:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.4);
        }

        .alert-warning {
            border-radius: 15px;
            border-left: 5px solid #ffc107;
        }

        .alert-danger {
            border-radius: 15px;
            border-left: 5px solid #dc3545;
        }

        .alert-info {
            border-radius: 15px;
            border-left: 5px solid #17a2b8;
        }

        .icon-large {
            font-size: 1.5rem;
            margin-right: 10px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            height: 48px;
            padding: 8px 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card">
            <!-- Header -->
            <div class="header-section">
                <h1><i class="fas fa-book-open"></i> Borrow Book</h1>
                <p class="subtitle">សុំខ្ចីសៀវភៅ | Request Book Loan</p>
                <small>{{ $currentTime }} - {{ $currentDate }}</small>
            </div>

            <!-- Content -->
            <div class="content-section">
                @if(isset($borrower) && $borrower)
                    <!-- User & Borrower Info -->
                    <div class="user-info-card">
                        <h5><i class="fas fa-user-check"></i> Your Borrower Information</h5>
                        <div class="info-row">
                            <span class="info-label">Name:</span>
                            <span class="info-value">{{ $borrower['name'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Type:</span>
                            <span class="info-value">
                                <span class="badge bg-success badge-borrower">{{ ucfirst($borrower['type']) }}</span>
                            </span>
                        </div>
                        @if(!empty($borrower['identifier']))
                        <div class="info-row">
                            <span class="info-label">ID/Code:</span>
                            <span class="info-value">{{ $borrower['identifier'] }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Info Alert -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Your loan request will be reviewed by library staff. You will be notified once it's approved.
                    </div>

                    <!-- Loan Request Form -->
                    <form id="loanRequestForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-book text-primary"></i> Select Book/Item <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="libraryItemId" name="library_item_id" required>
                                <option value="">-- Search by title or ISBN --</option>
                            </select>
                            <small class="text-muted">Type to search for a book</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-barcode text-info"></i> Copy Barcode (Optional)
                            </label>
                            <select class="form-select" id="copyId" name="copy_id" disabled>
                                <option value="">-- Select book first --</option>
                            </select>
                            <small class="text-muted">Select a specific copy, or leave empty to auto-select available copy</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt text-success"></i> Due Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="dueDate" name="due_date" 
                                   min="{{ $currentDate }}" 
                                   value="{{ date('Y-m-d', strtotime('+14 days')) }}" 
                                   required>
                            <small class="text-muted">Select when you plan to return the book</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-sticky-note text-warning"></i> Note (Optional)
                            </label>
                            <textarea class="form-control" name="note" rows="2" placeholder="Add any additional notes about your loan request..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-borrow">
                            <i class="fas fa-paper-plane icon-large"></i> Submit Loan Request
                        </button>
                    </form>

                @else
                    <!-- No Borrower Found -->
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-triangle"></i> Cannot Request Loan
                        </h5>
                        <hr>
                        <p class="mb-0">
                            <strong>Your account is not linked to a borrower record.</strong><br>
                            Please contact the administrator to link your account to a student, teacher, or staff record.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Back Link -->
        <div class="back-link">
            <a href="{{ route('admin.library.books_report.index') }}">
                <i class="fas fa-book"></i> Browse Books
            </a>
            <span class="mx-3">|</span>
            <a href="{{ route('home') }}">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(isset($borrower) && $borrower)
    <script>
        // Set CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize Select2 for book search
        $(document).ready(function() {
            const preselectedItemId = @json($preselectedItemId ?? null);
            
            $('#libraryItemId').select2({
                placeholder: 'Type to search book...',
                allowClear: true,
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route('admin.library.reading-logs.search-items') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term || '' };
                    },
                    processResults: function(data) {
                        console.log('Book search results:', data);
                        if (!data.results || data.results.length === 0) {
                            return { 
                                results: [{ id: '', text: 'No books found' }] 
                            };
                        }
                        return { results: data.results };
                    },
                    cache: true
                }
            });

            // Auto-select book if library_item_id is passed in URL
            if (preselectedItemId) {
                // Fetch book details and set it in Select2
                $.ajax({
                    url: `/admin/library/items/${preselectedItemId}`,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            const book = response.data;
                            const bookText = book.title + (book.isbn ? ' (ISBN: ' + book.isbn + ')' : '');
                            
                            // Create option and append to select
                            const option = new Option(bookText, book.id, true, true);
                            $('#libraryItemId').append(option).trigger('change');
                            
                            // Trigger change event to enable copy field
                            $('#libraryItemId').trigger('change');
                            
                            console.log('Book auto-selected:', book.title);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching book details:', xhr);
                    }
                });
            }

            // When book selected, enable copy search
            $('#libraryItemId').on('change', function() {
                const itemId = $(this).val();
                const $copyField = $('#copyId');

                console.log('Book selected, item_id:', itemId);

                // Clear and reset copy field
                if ($copyField.hasClass('select2-hidden-accessible')) {
                    $copyField.select2('destroy');
                }
                $copyField.empty().append('<option value="">-- Search by barcode or leave empty for auto-select --</option>');

                if (itemId) {
                    // Enable the field
                    $copyField.prop('disabled', false);

                    // Initialize Select2 with AJAX search for copies
                    $copyField.select2({
                        placeholder: 'Type to search barcode or leave empty...',
                        allowClear: true,
                        minimumInputLength: 0,
                        ajax: {
                            url: '{{ route('admin.library.reading-logs.search-copies') }}',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return { 
                                    q: params.term || '',
                                    item_id: itemId
                                };
                            },
                            processResults: function(data) {
                                console.log('Copy search results:', data);
                                if (!data.results || data.results.length === 0) {
                                    return { 
                                        results: [{ id: '', text: 'No copies found. System will auto-select available copy.' }] 
                                    };
                                }
                                return { results: data.results };
                            },
                            cache: true
                        }
                    });

                    console.log('Copy field enabled and Select2 initialized');
                } else {
                    // Disable if no book selected
                    $copyField.prop('disabled', true);
                    console.log('Copy field disabled (no book selected)');
                }
            });
        });

        // Form submission
        $('#loanRequestForm').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            // Show loading
            Swal.fire({
                title: 'Processing...',
                html: 'Submitting your loan request...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route('qr.library.loan-request.submit') }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'ជោគជ័យ! Success!',
                            html: '<strong>' + response.message + '</strong><br><br>' +
                                  '<div style="background: #d4edda; padding: 15px; border-radius: 10px; margin-top: 15px;">' +
                                  '<h6 style="color: #155724;"><i class="fas fa-book-open"></i> Loan Request Submitted!</h6>' +
                                  '<p style="margin: 5px 0; color: #155724;"><strong>Book:</strong> ' + response.data.book_title + '</p>' +
                                  '<p style="margin: 5px 0; color: #155724;"><strong>Barcode:</strong> ' + response.data.barcode + '</p>' +
                                  '<p style="margin: 5px 0; color: #155724;"><strong>Due Date:</strong> ' + response.data.due_date + '</p>' +
                                  '<p style="margin: 5px 0; color: #155724;"><strong>Status:</strong> <span class="badge bg-warning">Pending Approval</span></p>' +
                                  '</div>' +
                                  '<p class="mt-3"><small>You will be notified once your request is approved by library staff.</small></p>',
                            confirmButtonText: 'OK',
                            showCancelButton: true,
                            cancelButtonText: '<i class="fas fa-book"></i> Browse More Books',
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#17a2b8'
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.cancel) {
                                // Go to Books Report
                                window.location.href = '{{ route('admin.library.books_report.index') }}';
                            } else {
                                // Reload to reset form
                                window.location.reload();
                            }
                        });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const response = xhr.responseJSON;
                        
                        if (response.errors) {
                            // Validation errors
                            let errorHtml = '<ul class="text-start">';
                            $.each(response.errors, function(field, messages) {
                                $.each(messages, function(i, message) {
                                    errorHtml += '<li>' + message + '</li>';
                                });
                            });
                            errorHtml += '</ul>';
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                html: errorHtml,
                                confirmButtonText: 'OK'
                            });
                        } else if (response.message) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                confirmButtonText: 'OK'
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to submit loan request. Please try again.',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        });
    </script>
    @endif
</body>
</html>

