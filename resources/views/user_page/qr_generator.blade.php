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
                            <li><strong>Print this QR code</strong> and place it in strategic locations (library entrance, information desk, reading areas, etc.)</li>
                            <li>Users can <strong>scan this QR code</strong> with their mobile phone</li>
                            <li>They will be <strong>directed to the User Page</strong> with quick access to all library services</li>
                            <li>Users can quickly access:
                                <ul class="mt-2 mb-0">
                                    <li>Library Check-in/Check-out</li>
                                    <li>Start Reading sessions</li>
                                    <li>Browse and search books</li>
                                    <li>View library reports</li>
                                    <li>About and Contact pages</li>
                                </ul>
                            </li>
                        </ol>
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <strong>Features Available:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Quick Access:</strong> All library services in one place</li>
                            <li><strong>Mobile Friendly:</strong> Responsive design works on all devices</li>
                            <li><strong>Easy Navigation:</strong> Large, easy-to-click buttons</li>
                            <li><strong>Self-Service:</strong> Users can manage their own library activities</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- QR Code Card -->
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h5 class="mb-0">
                        <i class="fas fa-mobile-alt"></i> User Page QR Code
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
                            <i class="fas fa-mobile-alt"></i> Scan to Access Services
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
                        <a href="{{ route('user_page.index') }}" class="btn btn-info">
                            <i class="fas fa-external-link-alt"></i> View User Page
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
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
                                <i class="fas fa-home fa-2x text-info mb-2"></i>
                                <h6>2. User Page</h6>
                                <small class="text-muted">Access all services</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-mouse-pointer fa-2x text-warning mb-2"></i>
                                <h6>3. Click Service</h6>
                                <small class="text-muted">Choose desired service</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h6>4. Complete</h6>
                                <small class="text-muted">Service accessed!</small>
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
            link.download = 'user-page-qr-code.png';
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

