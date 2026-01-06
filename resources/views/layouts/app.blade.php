<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'School Management System'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        
        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
        
        <!-- Select2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <!-- Select2 CSS Fallback -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" onerror="console.warn('Select2 CSS fallback failed')" />
        <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
        
        <!-- SweetAlert2 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Page-specific Styles -->
        @stack('styles')
        
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen flex flex-col">
            <!-- Header -->
            @include('layouts.header')

            <!-- Main Content with Sidebar -->
            <div class="flex flex-1">
                <!-- Sidebar -->
                @include('layouts.sidebar')

            <!-- Page Content -->
                <main class="flex-1 p-6">
                    @yield('content')
            </main>
            </div>

            <!-- Footer -->
            @include('layouts.footer')
        </div>

        <!-- jQuery (required for DataTables and Bootstrap JS) -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        
        <!-- Bootstrap 5 JS Bundle (includes Popper) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        
        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        
        <!-- Select2 Fallback (if CDN fails) -->
        <script>
            // Check if Select2 loaded, if not try alternative CDN
            window.addEventListener('load', function() {
                if (typeof $.fn.select2 === 'undefined') {
                    console.warn('⚠️ Select2 failed to load from primary CDN, trying fallback...');
                    
                    // Try fallback CDN
                    var script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js';
                    script.onload = function() {
                        console.log('✅ Select2 loaded from fallback CDN');
                    };
                    script.onerror = function() {
                        console.error('❌ Select2 failed to load from both CDNs');
                    };
                    document.head.appendChild(script);
                } else {
                    console.log('✅ Select2 loaded successfully');
                }
            });
            
            // Helper function to check libraries
            window.checkLibraries = function() {
                console.log('=== Library Check ===');
                console.log('jQuery:', typeof $ !== 'undefined' ? '✅ v' + $.fn.jquery : '❌');
                console.log('Bootstrap:', typeof bootstrap !== 'undefined' ? '✅' : '❌');
                console.log('Select2:', typeof $.fn.select2 !== 'undefined' ? '✅' : '❌');
                console.log('DataTables:', typeof $.fn.DataTable !== 'undefined' ? '✅' : '❌');
                console.log('SweetAlert2:', typeof Swal !== 'undefined' ? '✅' : '❌');
                console.log('====================');
            };
        </script>
        
        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>

        <!-- Scripts Stack -->
        @stack('scripts')
        
        <!-- Global Loan Requests Notification Badge -->
        @if(auth()->check() && auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
        <script>
            // Function to update header pending requests badge
            function updateHeaderPendingRequestsBadge() {
                $.ajax({
                    url: '{{ route("admin.library.loans.stats") }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            const count = response.data.pending_requests || 0;
                            const badge = $('#headerPendingRequestsBadge');
                            
                            if (count > 0) {
                                badge.text(count).show();
                                // Add pulse animation
                                badge.addClass('animate-pulse');
                                setTimeout(function() {
                                    badge.removeClass('animate-pulse');
                                }, 1000);
                            } else {
                                badge.hide();
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to fetch pending requests count:', xhr);
                    }
                });
            }
            
            // Function to update header overdue loans badge
            function updateHeaderOverdueBadge() {
                $.ajax({
                    url: '{{ route("admin.library.loans.stats") }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            const count = response.data.overdue_count || 0;
                            const badge = $('#headerOverdueBadge');
                            
                            if (count > 0) {
                                badge.text(count).show();
                                // Add pulse animation
                                badge.addClass('animate-pulse');
                                setTimeout(function() {
                                    badge.removeClass('animate-pulse');
                                }, 1000);
                            } else {
                                badge.hide();
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to fetch overdue loans count:', xhr);
                    }
                });
            }
            
            // Show pending loan requests when clicking header notification
            function showPendingLoanRequests(event) {
                // Check if we're already on the loans page
                const currentPath = window.location.pathname;
                const loansPath = '{{ route("admin.library.loans.index") }}';
                
                if (currentPath === loansPath || currentPath.includes('/admin/library/loans')) {
                    // Already on loans page, just filter and show message
                    event.preventDefault();
                    
                    // Set status filter to 'requested'
                    if (typeof $('#statusFilter') !== 'undefined' && $('#statusFilter').length > 0) {
                        $('#statusFilter').val('requested');
                        
                        // Reload table with filter
                        if (typeof loansTable !== 'undefined') {
                            loansTable.ajax.reload();
                        }
                    }
                    
                    // Show notification message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Pending Requests',
                            text: 'Showing all pending loan requests. Click "Approve" to approve them.',
                            timer: 3000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                } else {
                    // Not on loans page, navigate and let the page handle it
                    // The URL will include the filter parameter
                    window.location.href = loansPath + '?status=requested';
                }
            }
            
            // Show overdue loan requests when clicking header notification
            function showOverdueLoanRequests(event) {
                // Check if we're already on the loans page
                const currentPath = window.location.pathname;
                const loansPath = '{{ route("admin.library.loans.index") }}';
                
                if (currentPath === loansPath || currentPath.includes('/admin/library/loans')) {
                    // Already on loans page, just filter and show message
                    event.preventDefault();
                    
                    // Set status filter to 'overdue'
                    if (typeof $('#statusFilter') !== 'undefined' && $('#statusFilter').length > 0) {
                        $('#statusFilter').val('overdue');
                        
                        // Reload table with filter
                        if (typeof loansTable !== 'undefined') {
                            loansTable.ajax.reload();
                        }
                    }
                    
                    // Show notification message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Overdue Loans',
                            text: 'Showing all overdue loans. Please contact borrowers to return books.',
                            timer: 3000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                } else {
                    // Not on loans page, navigate and let the page handle it
                    window.location.href = loansPath + '?status=overdue';
                }
            }
            
            // Update badge on page load
            $(document).ready(function() {
                updateHeaderPendingRequestsBadge();
                updateHeaderOverdueBadge();
                
                // Update badges every 30 seconds
                setInterval(function() {
                    updateHeaderPendingRequestsBadge();
                    updateHeaderOverdueBadge();
                }, 30000);
                
                // If we're on loans page with status parameter, show message
                const urlParams = new URLSearchParams(window.location.search);
                const status = urlParams.get('status');
                
                if (status === 'requested') {
                    // Wait a bit for page to load, then show message
                    setTimeout(function() {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Pending Requests',
                                text: 'Showing all pending loan requests. Click "Approve" to approve them.',
                                timer: 3000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        }
                    }, 500);
                } else if (status === 'overdue') {
                    // Wait a bit for page to load, then show message
                    setTimeout(function() {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Overdue Loans',
                                text: 'Showing all overdue loans. Please contact borrowers to return books.',
                                timer: 3000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        }
                    }, 500);
                }
            });
        </script>
        @endif
    </body>
</html>
