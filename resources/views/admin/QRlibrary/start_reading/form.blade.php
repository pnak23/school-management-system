<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Start Reading - Library System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #667eea;
        }

        .user-info-card h5 {
            color: #667eea;
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

        .badge-session {
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
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-start {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .alert-warning {
            border-radius: 15px;
            border-left: 5px solid #ffc107;
        }

        .alert-danger {
            border-radius: 15px;
            border-left: 5px solid #dc3545;
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
                <h1><i class="fas fa-book-reader"></i> Start Reading</h1>
                <p class="subtitle">ចាប់ផ្តើមអានសៀវភៅ | Quick Start Reading Log</p>
                <small>{{ $currentTime }} - {{ ucfirst($currentSession) }} Session</small>
            </div>

            <!-- Content -->
            <div class="content-section">
                @if($hasOpenVisit)
                    <!-- User & Visit Info -->
                    <div class="user-info-card">
                        <h5><i class="fas fa-user-check"></i> Your Visit Session</h5>
                        <div class="info-row">
                            <span class="info-label">Name:</span>
                            <span class="info-value">{{ $user->name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Checked-in:</span>
                            <span class="info-value">{{ $openVisit->check_in_time->format('H:i') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Session:</span>
                            <span class="info-value">
                                <span class="badge bg-primary badge-session">{{ ucfirst($openVisit->session) }}</span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Purpose:</span>
                            <span class="info-value">{{ ucfirst($openVisit->purpose) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Duration:</span>
                            <span class="info-value text-success fw-bold">{{ $openVisit->duration }}</span>
                        </div>
                    </div>

                    <!-- Start Reading Form -->
                    <form id="startReadingForm">
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
                            <small class="text-muted">Select a book to enable this field</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-sticky-note text-warning"></i> Note (Optional)
                            </label>
                            <textarea class="form-control" name="note" rows="2" placeholder="Add a note about this reading session..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-start">
                            <i class="fas fa-play-circle icon-large"></i> Start Reading Now
                        </button>
                    </form>

                @else
                    <!-- No Open Visit -->
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-triangle"></i> Cannot Start Reading
                        </h5>
                        <hr>
                        <p class="mb-0">
                            <strong>You do not have an open visit today.</strong><br>
                            Please check-in first before starting a reading log.
                        </p>
                        <div class="mt-3">
                            <a href="{{ route('qr.library.visits.index') }}" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Go to Check-in
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Back Link -->
        <div class="back-link">
            <a href="{{ route('qr.library.my-reading-logs') }}">
                <i class="fas fa-list"></i> My Reading Logs
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

    @if($hasOpenVisit)
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
                $copyField.empty().append('<option value="">-- Search by barcode --</option>');

                if (itemId) {
                    // Enable the field
                    $copyField.prop('disabled', false);

                    // Initialize Select2 with AJAX search for copies
                    $copyField.select2({
                        placeholder: 'Type to search barcode...',
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
                                        results: [{ id: '', text: 'No copies found for this book' }] 
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
        $('#startReadingForm').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            // Show loading
            Swal.fire({
                title: 'Processing...',
                html: 'Starting your reading session...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route('qr.library.start-reading.submit') }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'ជោគជ័យ! Success!',
                            html: '<strong>' + response.message + '</strong><br><br>' +
                                  '<div style="background: #d4edda; padding: 15px; border-radius: 10px; margin-top: 15px;">' +
                                  '<h6 style="color: #155724;"><i class="fas fa-book-reader"></i> Reading Started!</h6>' +
                                  '<p style="margin: 5px 0; color: #155724;"><strong>Book:</strong> ' + response.data.book_title + '</p>' +
                                  '<p style="margin: 5px 0; color: #155724;"><strong>Started:</strong> ' + response.data.start_time + '</p>' +
                                  '<p style="margin: 5px 0; color: #155724;"><strong>Session:</strong> ' + response.data.visit_session + '</p>' +
                                  '</div>',
                            confirmButtonText: 'OK',
                            showCancelButton: true,
                            cancelButtonText: '<i class="fas fa-list"></i> View My Logs',
                            confirmButtonColor: '#667eea',
                            cancelButtonColor: '#28a745'
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.cancel) {
                                // Go to My Reading Logs
                                window.location.href = '{{ route('qr.library.my-reading-logs') }}';
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
                        
                        // Check if user needs to check-in first
                        if (response.action === 'check-in') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Check-in Required',
                                html: '<p>' + response.message + '</p>',
                                confirmButtonText: 'Go to Check-in',
                                confirmButtonColor: '#667eea',
                                showCancelButton: true,
                                cancelButtonText: 'Cancel'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = response.check_in_url;
                                }
                            });
                        } else if (response.errors) {
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
                        } else {
                            // Other 422 errors (e.g., already has running log)
                            let errorHtml = '<p>' + response.message + '</p>';
                            
                            if (response.data && response.data.running_log) {
                                errorHtml += '<hr>' +
                                            '<div class="text-start">' +
                                            '<p><strong>Current Reading:</strong></p>' +
                                            '<p>Book: ' + response.data.running_log.book + '</p>' +
                                            '<p>Started: ' + response.data.running_log.started_at + '</p>' +
                                            '</div>';
                            }
                            
                            Swal.fire({
                                icon: 'warning',
                                title: 'Cannot Start Reading',
                                html: errorHtml,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#ffc107'
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to start reading. Please try again.',
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

