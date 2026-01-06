<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Library QR Check-in/Out</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .qr-container {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .status-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .status-inside {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        
        .status-outside {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        
        .welcome-text {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .user-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .user-info p {
            margin: 8px 0;
            font-size: 15px;
        }
        
        .action-btn {
            width: 100%;
            padding: 20px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 15px;
            border: none;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .btn-checkin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-checkin:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-checkout {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
        }
        
        .icon-large {
            font-size: 30px;
            margin-right: 15px;
        }
        
        .purpose-select {
            padding: 15px;
            font-size: 16px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            margin-bottom: 15px;
        }
        
        .note-input {
            padding: 15px;
            font-size: 16px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            margin-bottom: 15px;
        }
        
        .library-icon {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .library-icon i {
            font-size: 60px;
            color: white;
        }
        
        .session-info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: white;
            text-decoration: none;
            font-size: 16px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .quick-reading-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }
        
        .quick-reading-section .form-check-label {
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <!-- Library Icon -->
        <div class="library-icon">
            <i class="fas fa-book-reader"></i>
            <h4 class="text-white mt-2">Library Visit System</h4>
        </div>
        
        <!-- Status Card -->
        <div class="status-card">
            <!-- Welcome Message -->
            <div class="welcome-text">
                <i class="fas fa-user-circle"></i> សួស្តី! Hello!
            </div>
            
            <!-- Status Badge -->
            @if($hasOpenVisit)
                <span class="status-badge status-inside">
                    <i class="fas fa-door-open"></i> You are INSIDE the library
                </span>
            @else
                <span class="status-badge status-outside">
                    <i class="fas fa-door-closed"></i> You are OUTSIDE the library
                </span>
            @endif
            
            <!-- User Information -->
            <div class="user-info">
                <p><i class="fas fa-user text-primary"></i> <strong>Name:</strong> {{ $user->name }}</p>
                <p><i class="fas fa-envelope text-info"></i> <strong>Email:</strong> {{ $user->email }}</p>
                
                @if($hasOpenVisit)
                    <hr>
                    <p><i class="fas fa-clock text-success"></i> <strong>Checked in:</strong> {{ $openVisit->check_in_time->format('h:i A') }}</p>
                    <p><i class="fas fa-calendar-day text-warning"></i> <strong>Session:</strong> {{ ucfirst($openVisit->session) }}</p>
                    <p><i class="fas fa-bullseye text-danger"></i> <strong>Purpose:</strong> {{ ucfirst($openVisit->purpose) }}</p>
                    <p><i class="fas fa-hourglass-half text-secondary"></i> <strong>Duration:</strong> {{ $openVisit->duration }}</p>
                @endif
            </div>
            
            <!-- Session Info -->
            <div class="session-info mt-3">
                <i class="fas fa-info-circle"></i> <strong>Current Session:</strong> {{ ucfirst($currentSession) }} ({{ $currentTime }})
            </div>
            
            <!-- Action Buttons -->
            @if($hasOpenVisit)
                <!-- Check-out Button (user is inside) -->
                <button type="button" class="action-btn btn-checkout" onclick="showCheckOutConfirm()">
                    <i class="fas fa-sign-out-alt icon-large"></i>
                    Check-out / ចេញ
                </button>
            @else
                <!-- Check-in Form (user is outside) -->
                <form id="checkInForm">
                    @csrf
                    
                    <label class="form-label mt-3"><strong>Purpose / គោលបំណង:</strong></label>
                    <select class="form-select purpose-select" name="purpose" required>
                        <option value="">-- Select Purpose --</option>
                        <option value="read">📖 Read / អាន</option>
                        <option value="study">✏️ Study / សិក្សា</option>
                        <option value="borrow">📚 Borrow Book / ខ្ចីសៀវភៅ</option>
                        <option value="return">🔄 Return Book / ប្រគល់សៀវភៅ</option>
                        <option value="other">➕ Other / ផ្សេងៗ</option>
                    </select>
                    
                    <label class="form-label mt-3"><strong>Note (Optional):</strong></label>
                    <textarea class="form-control note-input" name="note" rows="2" placeholder="Add a note..."></textarea>
                    
                    <!-- Quick Start Reading -->
                    <div class="quick-reading-section mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="startReadingNow" name="start_reading_now" value="1">
                            <label class="form-check-label fw-bold" for="startReadingNow">
                                <i class="fas fa-book-reader text-success"></i> Start Reading Now
                            </label>
                            <small class="d-block text-muted" style="font-size: 12px;">ចាប់ផ្តើមអានភ្លាមៗ</small>
                        </div>
                        
                        <div id="quickReadingFields" style="display: none;" class="mt-3">
                            <label class="form-label"><strong>Select Book:</strong></label>
                            <select class="form-select purpose-select" id="readingLibraryItemId" name="library_item_id">
                                <option value="">-- Search book --</option>
                            </select>
                            <small class="text-muted" style="font-size: 12px;">Search by title or ISBN</small>
                            
                            <label class="form-label mt-2"><strong>Copy Barcode (Optional):</strong></label>
                            <select class="form-select purpose-select" id="readingCopyId" name="copy_id" disabled>
                                <option value="">-- Select book first --</option>
                            </select>
                            <small class="text-muted" style="font-size: 12px;">Will be enabled after selecting a book</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="action-btn btn-checkin">
                        <i class="fas fa-sign-in-alt icon-large"></i>
                        Check-in / ចូល
                    </button>
                </form>
            @endif
        </div>
        
        <!-- Back Link -->
        <div class="back-link">
            <a href="{{ route('home') }}">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Set CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Quick Start Reading checkbox toggle
        $('#startReadingNow').on('change', function() {
            const isChecked = $(this).is(':checked');
            const $quickFields = $('#quickReadingFields');
            const $bookField = $('#readingLibraryItemId');
            const $copyField = $('#readingCopyId');
            
            console.log('Start Reading checkbox:', isChecked);
            
            if (isChecked) {
                $quickFields.slideDown();
                $bookField.prop('required', true);
                
                // Initialize Select2 for book search
                if (!$bookField.hasClass('select2-hidden-accessible')) {
                    console.log('Initializing Select2 for book search...');
                    $bookField.select2({
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
                    console.log('Book Select2 initialized');
                } else {
                    console.log('Book Select2 already initialized');
                }
            } else {
                $quickFields.slideUp();
                $bookField.prop('required', false).val(null).trigger('change');
                $copyField.val(null).trigger('change');
                
                // Destroy Select2 instances
                if ($bookField.hasClass('select2-hidden-accessible')) {
                    $bookField.select2('destroy');
                    console.log('Book Select2 destroyed');
                }
                if ($copyField.hasClass('select2-hidden-accessible')) {
                    $copyField.select2('destroy');
                    console.log('Copy Select2 destroyed');
                }
            }
        });
        
        // When book selected, enable copy search
        $('#readingLibraryItemId').on('change', function() {
            const itemId = $(this).val();
            const $copyField = $('#readingCopyId');
            
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
        
        // Check-in Form Submit
        $('#checkInForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            
            // Show loading
            Swal.fire({
                title: 'Processing...',
                html: 'Checking you in, please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '{{ route('qr.library.visits.check-in') }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        let successHtml = '<strong>' + response.message + '</strong><br><br>' +
                                         '<p>Check-in Time: ' + response.data.check_in_time + '</p>' +
                                         '<p>Session: ' + response.data.session + '</p>';
                        
                        // If reading started, show book info
                        if (response.data && response.data.reading_started) {
                            successHtml += '<hr>' +
                                          '<div style="background: #d4edda; padding: 15px; border-radius: 10px; margin-top: 15px;">' +
                                          '<h6 style="color: #155724;"><i class="fas fa-book-reader"></i> Reading Started!</h6>' +
                                          '<p style="margin: 5px 0; color: #155724;"><strong>Book:</strong> ' + response.data.book_title + '</p>' +
                                          '<small style="color: #155724;">Reading log created automatically</small>' +
                                          '</div>';
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'ជោគជ័យ! Success!',
                            html: successHtml,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#667eea'
                        }).then(() => {
                            // Reload page to show check-out button
                            window.location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON?.message || 'Check-in failed. Please try again.';
                    let errorHtml = '<p><strong>' + errorMessage + '</strong></p>';
                    
                    // Show existing session details if available
                    if (xhr.responseJSON?.data?.existing_session) {
                        const session = xhr.responseJSON.data.existing_session;
                        errorHtml += '<hr>' +
                                    '<div class="text-start">' +
                                    '<p><strong>Open Session Found:</strong></p>' +
                                    '<p>• Check-in: ' + session.check_in_time + '</p>' +
                                    '<p>• Session: ' + session.session + '</p>' +
                                    '<p>• Purpose: ' + session.purpose + '</p>' +
                                    '<p>• Duration: ' + session.duration + '</p>' +
                                    '</div>' +
                                    '<hr>' +
                                    '<p>Please check-out first before checking in again.</p>';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Cannot Check-in',
                        html: errorHtml,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#f5576c'
                    });
                }
            });
        });
        
        // Check-out Confirmation
        function showCheckOutConfirm() {
            Swal.fire({
                title: 'Check-out Confirmation',
                html: '<p>Are you sure you want to check-out now?</p>' +
                      '<p class="text-muted small">This will end your current visit session.</p>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Check-out',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#f5576c',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    processCheckOut();
                }
            });
        }
        
        // Process Check-out
        function processCheckOut() {
            // Show loading
            Swal.fire({
                title: 'Processing...',
                html: 'Checking you out, please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '{{ route('qr.library.visits.check-out') }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'ជោគជ័យ! Success!',
                            html: '<strong>' + response.message + '</strong><br><br>' +
                                  '<p>Check-out Time: ' + response.data.check_out_time + '</p>' +
                                  '<p>Total Duration: ' + response.data.duration + '</p>' +
                                  '<br><p class="text-muted">Thank you for visiting! 📚</p>',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#667eea'
                        }).then(() => {
                            // Reload page to show check-in form
                            window.location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON?.message || 'Check-out failed. Please try again.';
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Cannot Check-out',
                        html: '<p><strong>' + errorMessage + '</strong></p>',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#f5576c'
                    });
                }
            });
        }
    </script>
</body>
</html>



