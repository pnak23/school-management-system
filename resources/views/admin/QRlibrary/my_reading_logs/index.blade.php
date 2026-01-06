<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Reading Logs - Library System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }

        .container {
            max-width: 900px;
        }

        .main-card {
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            background: white;
            margin-bottom: 20px;
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

        .content-section {
            padding: 25px 20px;
        }

        .active-reading-card {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #0d7a6e;
        }

        .active-reading-card h5 {
            font-weight: bold;
            margin-bottom: 15px;
        }

        .active-reading-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .reading-log-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 5px solid #667eea;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .reading-log-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .reading-log-card.completed {
            border-left-color: #28a745;
        }

        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .log-book-title {
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
            flex: 1;
        }

        .log-status {
            font-size: 0.85rem;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        .log-status.active {
            background: #ff6b6b;
            color: white;
        }

        .log-status.completed {
            background: #28a745;
            color: white;
        }

        .log-details {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 8px;
        }

        .log-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .btn-stop {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn-stop:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
        }

        .btn-details {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn-details:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .no-logs {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .no-logs i {
            font-size: 3rem;
            margin-bottom: 15px;
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

        .pagination {
            justify-content: center;
        }

        .page-link {
            border-radius: 10px;
            margin: 0 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Main Card -->
        <div class="main-card">
            <!-- Header -->
            <div class="header-section">
                <h1><i class="fas fa-book-reader"></i> My Reading Logs</h1>
                <p class="mb-0">
                    <small>បញ្ជីអានសៀវភៅរបស់ខ្ញុំ | Your Reading History</small>
                </p>
            </div>

            <!-- Content -->
            <div class="content-section">
                <!-- Active Reading Card (if exists) -->
                @if($hasActiveLog)
                <div class="active-reading-card">
                    <h5><i class="fas fa-book-open"></i> Currently Reading</h5>
                    <div class="active-reading-info">
                        <div>
                            <strong>{{ $activeLog->item->title }}</strong><br>
                            <small>Started: {{ $activeLog->start_time->format('H:i') }}</small>
                        </div>
                        <button class="btn btn-light btn-sm" onclick="stopReading({{ $activeLog->id }})">
                            <i class="fas fa-stop-circle"></i> Stop Reading
                        </button>
                    </div>
                </div>
                @endif

                <!-- Reading Logs List -->
                @if($readingLogs->count() > 0)
                    @foreach($readingLogs as $log)
                    <div class="reading-log-card {{ $log->end_time ? 'completed' : '' }}">
                        <div class="log-header">
                            <div class="log-book-title">
                                <i class="fas fa-book text-primary"></i>
                                {{ $log->item->title ?? 'Unknown Book' }}
                            </div>
                            <span class="log-status {{ $log->end_time ? 'completed' : 'active' }}">
                                {{ $log->end_time ? 'Completed' : 'Reading' }}
                            </span>
                        </div>

                        <div class="log-details">
                            <i class="fas fa-calendar text-muted"></i>
                            <strong>Date:</strong> {{ $log->visit->visit_date->format('M d, Y') }}
                            <span class="mx-2">|</span>
                            <i class="fas fa-sun text-warning"></i>
                            <strong>Session:</strong> {{ ucfirst($log->visit->session) }}
                        </div>

                        <div class="log-details">
                            <i class="fas fa-clock text-muted"></i>
                            <strong>Start:</strong> {{ $log->start_time ? $log->start_time->format('H:i') : 'N/A' }}
                            @if($log->end_time)
                                <span class="mx-2">→</span>
                                <strong>End:</strong> {{ $log->end_time->format('H:i') }}
                                <span class="mx-2">|</span>
                                <i class="fas fa-hourglass-half text-success"></i>
                                <strong>Duration:</strong> {{ $log->duration }}
                            @else
                                <span class="mx-2 text-danger">
                                    <i class="fas fa-spinner fa-pulse"></i> Still reading...
                                </span>
                            @endif
                        </div>

                        @if($log->copy)
                        <div class="log-details">
                            <i class="fas fa-barcode text-muted"></i>
                            <strong>Copy:</strong> {{ $log->copy->barcode }}
                        </div>
                        @endif

                        <div class="log-actions">
                            @if(!$log->end_time)
                            <button class="btn btn-stop btn-sm" onclick="stopReading({{ $log->id }})">
                                <i class="fas fa-stop-circle"></i> Stop Reading
                            </button>
                            @endif
                            <button class="btn btn-details btn-sm" onclick="showDetails({{ $log->id }})">
                                <i class="fas fa-info-circle"></i> View Details
                            </button>
                        </div>
                    </div>
                    @endforeach

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $readingLogs->links() }}
                    </div>
                @else
                    <div class="no-logs">
                        <i class="fas fa-book-open text-muted"></i>
                        <h5>No Reading Logs Yet</h5>
                        <p>Start reading by scanning the "Start Reading" QR code!</p>
                        <a href="{{ route('qr.library.start-reading.form') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-plus-circle"></i> Start Reading Now
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Back Links -->
        <div class="back-link">
            <a href="{{ route('qr.library.start-reading.form') }}">
                <i class="fas fa-plus-circle"></i> Start New Reading
            </a>
            <span class="mx-3 text-white">|</span>
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

    <script>
        // Set CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Stop Reading function
        function stopReading(logId) {
            Swal.fire({
                title: 'Stop Reading?',
                html: 'This will set <strong>end time</strong> to now.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-stop-circle"></i> Yes, Stop',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ff6b6b',
                cancelButtonColor: '#6c757d',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Stopping...',
                        html: 'Please wait...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send AJAX request
                    $.ajax({
                        url: '{{ route('qr.library.my-reading-logs.stop') }}',
                        method: 'POST',
                        data: {
                            log_id: logId
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'ជោគជ័យ! Stopped!',
                                    html: '<strong>' + response.message + '</strong><br><br>' +
                                          '<div style="background: #d4edda; padding: 15px; border-radius: 10px; margin-top: 15px;">' +
                                          '<p style="margin: 5px 0; color: #155724;"><strong>Book:</strong> ' + response.data.book_title + '</p>' +
                                          '<p style="margin: 5px 0; color: #155724;"><strong>End Time:</strong> ' + response.data.end_time + '</p>' +
                                          '<p style="margin: 5px 0; color: #155724;"><strong>Duration:</strong> ' + response.data.duration + '</p>' +
                                          '</div>',
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#667eea'
                                }).then(() => {
                                    // Reload page to show updated status
                                    window.location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'កំហុស! Error!',
                                text: xhr.responseJSON?.message || 'Failed to stop reading. Please try again.',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }

        // Show Details function
        function showDetails(logId) {
            // Show loading
            Swal.fire({
                title: 'Loading...',
                html: 'Fetching details...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Fetch details
            $.ajax({
                url: '{{ route('qr.library.my-reading-logs.detail', ':id') }}'.replace(':id', logId),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        
                        Swal.fire({
                            title: '<i class="fas fa-book-open"></i> Reading Log Details',
                            html: `
                                <div style="text-align: left; padding: 10px;">
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                                        <h6 style="color: #667eea; font-weight: bold; margin-bottom: 10px;">
                                            <i class="fas fa-book"></i> Book Information
                                        </h6>
                                        <p style="margin: 5px 0;"><strong>Title:</strong> ${data.book_title}</p>
                                        <p style="margin: 5px 0;"><strong>ISBN:</strong> ${data.book_isbn}</p>
                                        <p style="margin: 5px 0;"><strong>Copy Barcode:</strong> ${data.copy_barcode}</p>
                                    </div>
                                    
                                    <div style="background: #e3f2fd; padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                                        <h6 style="color: #1976d2; font-weight: bold; margin-bottom: 10px;">
                                            <i class="fas fa-clock"></i> Time Information
                                        </h6>
                                        <p style="margin: 5px 0;"><strong>Start Time:</strong> ${data.start_time}</p>
                                        <p style="margin: 5px 0;"><strong>End Time:</strong> ${data.end_time}</p>
                                        <p style="margin: 5px 0;"><strong>Duration:</strong> <span style="color: #28a745; font-weight: bold;">${data.duration}</span></p>
                                    </div>
                                    
                                    <div style="background: #fff3cd; padding: 15px; border-radius: 10px;">
                                        <h6 style="color: #856404; font-weight: bold; margin-bottom: 10px;">
                                            <i class="fas fa-calendar"></i> Visit Information
                                        </h6>
                                        <p style="margin: 5px 0;"><strong>Visit Date:</strong> ${data.visit_date}</p>
                                        <p style="margin: 5px 0;"><strong>Session:</strong> ${data.visit_session}</p>
                                        <p style="margin: 5px 0;"><strong>Type:</strong> ${data.reading_type}</p>
                                        <p style="margin: 5px 0;"><strong>Status:</strong> 
                                            <span style="background: ${data.is_active ? '#ff6b6b' : '#28a745'}; color: white; padding: 3px 10px; border-radius: 10px; font-size: 0.85rem;">
                                                ${data.is_active ? 'Reading' : 'Completed'}
                                            </span>
                                        </p>
                                    </div>
                                    
                                    ${data.note !== 'No notes' ? `
                                        <div style="background: #f1f3f5; padding: 15px; border-radius: 10px; margin-top: 15px;">
                                            <h6 style="color: #495057; font-weight: bold; margin-bottom: 10px;">
                                                <i class="fas fa-sticky-note"></i> Note
                                            </h6>
                                            <p style="margin: 0;">${data.note}</p>
                                        </div>
                                    ` : ''}
                                </div>
                            `,
                            width: '600px',
                            confirmButtonText: 'Close',
                            confirmButtonColor: '#667eea'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to load details.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    </script>
</body>
</html>



