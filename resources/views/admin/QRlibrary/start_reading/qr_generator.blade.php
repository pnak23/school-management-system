@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
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
                            <li><strong>Print this QR code</strong> and place it in reading areas (near tables, book shelves, etc.)</li>
                            <li>Visitors who have <strong>already checked-in</strong> can scan this QR code</li>
                            <li>System will <strong>auto-detect their open visit session</strong></li>
                            <li>They simply <strong>select a book</strong> and submit</li>
                            <li>Reading log is <strong>created immediately</strong> with start_time = now()</li>
                        </ol>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong>
                        <ul class="mb-0 mt-2">
                            <li>User must be <strong>logged in</strong></li>
                            <li>User must have <strong>checked-in today</strong> (open visit)</li>
                            <li>If not checked-in, they will see an error message</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- QR Code Card -->
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book-reader"></i> Start Reading QR Code
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
                            <i class="fas fa-mobile-alt"></i> Scan to Start Reading
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
                        <a href="{{ route('admin.library.visits.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Visits
                        </a>
                    </div>
                </div>
            </div>

            <!-- Usage Stats Card (Optional) -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> What Happens After Scanning?
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-mobile-alt fa-2x text-primary mb-2"></i>
                                <h6>1. Scan QR</h6>
                                <small class="text-muted">User scans QR from mobile</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-search fa-2x text-info mb-2"></i>
                                <h6>2. Auto-Detect</h6>
                                <small class="text-muted">System finds their open visit</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-book-open fa-2x text-success mb-2"></i>
                                <h6>3. Select & Start</h6>
                                <small class="text-muted">Choose book → Reading starts!</small>
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
        document.execCommand('copy');
        
        // Show feedback
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'URL copied to clipboard',
            timer: 1500,
            showConfirmButton: false
        });
    }

    // Download QR as PNG
    function downloadQR() {
        const canvas = document.querySelector('#qrcode canvas');
        if (canvas) {
            const link = document.createElement('a');
            link.download = 'start-reading-qr-code.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            Swal.fire({
                icon: 'success',
                title: 'Downloaded!',
                text: 'QR code saved as PNG',
                timer: 1500,
                showConfirmButton: false
            });
        }
    }
</script>
@endsection



