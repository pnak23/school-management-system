@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-qrcode"></i> {{ $title }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5 class="alert-heading">
                            <i class="fas fa-info-circle"></i> How to Use This QR Code
                        </h5>
                        <hr>
                        <ol class="mb-0">
                            <li><strong>Print this QR code</strong> and place it in library areas (entrance, reading areas, book shelves, etc.)</li>
                            <li>Users can <strong>scan this QR code</strong> with their mobile phone</li>
                            <li>They will be <strong>directed to the Library Books Report page</strong></li>
                            <li>Users can <strong>browse, search, and filter books</strong> by category</li>
                            <li>Users can <strong>view book details</strong>, <strong>reserve unavailable books</strong>, <strong>borrow available books</strong>, and <strong>start reading</strong></li>
                        </ol>
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <strong>Features Available:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Browse Books:</strong> View all library books in a grid layout</li>
                            <li><strong>Search:</strong> Search by title, author, category, or ISBN</li>
                            <li><strong>Filter:</strong> Filter books by category</li>
                            <li><strong>Reserve:</strong> Reserve unavailable books</li>
                            <li><strong>Borrow:</strong> Request to borrow available books</li>
                            <li><strong>Start Reading:</strong> Start a reading session for any book</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- QR Code Card -->
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book"></i> Library Books Report QR Code
                    </h5>
                </div>
                <div class="card-body text-center bg-light">
                    <!-- QR Code Container -->
                    <div class="qr-code-container p-4 bg-white d-inline-block rounded shadow-sm">
                        <div id="qrcode"></div>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-4">
                        <h5 class="text-primary">
                            <i class="fas fa-mobile-alt"></i> Scan to Browse Books
                        </h5>
                        <p class="text-muted mb-0">
                            <small>{{ $description }}</small>
                        </p>
                    </div>

                    <!-- URL Display -->
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">QR Code URL:</small>
                        <div class="input-group mt-2">
                            <input type="text" class="form-control form-control-sm" value="{{ $qrUrl }}" id="qrUrlInput" readonly>
                            <button class="btn btn-sm btn-outline-secondary" type="button" onclick="copyToClipboard()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print QR Code
                        </button>
                        <button class="btn btn-success" onclick="downloadQR()">
                            <i class="fas fa-download"></i> Download PNG
                        </button>
                        <a href="{{ route('admin.library.books_report.index') }}" class="btn btn-info">
                            <i class="fas fa-book"></i> View Books Report
                        </a>
                        <a href="{{ route('admin.library.books_report.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Usage Stats Card -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> What Happens After Scanning?
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-mobile-alt fa-2x text-primary mb-2"></i>
                                <h6>1. Scan QR</h6>
                                <small class="text-muted">User scans QR from mobile</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-book fa-2x text-info mb-2"></i>
                                <h6>2. Browse Books</h6>
                                <small class="text-muted">View all library books</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-search fa-2x text-warning mb-2"></i>
                                <h6>3. Search & Filter</h6>
                                <small class="text-muted">Find books easily</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-hand-holding fa-2x text-success mb-2"></i>
                                <h6>4. Reserve/Borrow</h6>
                                <small class="text-muted">Request books instantly</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include QRCode.js -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<style>
    @media print {
        .btn, .card-header, .alert, .input-group, small, .card:last-child {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .qr-code-container {
            border: 3px solid #000 !important;
            padding: 20px !important;
        }
        @page {
            margin: 20mm;
        }
        body {
            background: white !important;
        }
    }

    .qr-code-container {
        border: 2px solid #ddd;
        display: inline-block;
    }

    #qrcode {
        display: inline-block;
    }

    .gap-2 {
        gap: 0.5rem;
    }
</style>

<script>
    // Generate QR Code
    document.addEventListener('DOMContentLoaded', function() {
        new QRCode(document.getElementById('qrcode'), {
            text: '{{ $qrUrl }}',
            width: 300,
            height: 300,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    });

    // Copy URL to clipboard
    function copyToClipboard() {
        const input = document.getElementById('qrUrlInput');
        input.select();
        input.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            document.execCommand('copy');
            
            // Show feedback
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'URL copied to clipboard',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                alert('URL copied to clipboard!');
            }
        } catch (err) {
            // Fallback for older browsers
            alert('Please copy the URL manually: ' + input.value);
        }
    }

    // Download QR as PNG
    function downloadQR() {
        const canvas = document.querySelector('#qrcode canvas');
        if (canvas) {
            const link = document.createElement('a');
            link.download = 'library-books-report-qr-code.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Downloaded!',
                    text: 'QR code saved as PNG',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                alert('QR code downloaded!');
            }
        } else {
            alert('QR code not ready yet. Please wait a moment and try again.');
        }
    }
</script>
@endsection

