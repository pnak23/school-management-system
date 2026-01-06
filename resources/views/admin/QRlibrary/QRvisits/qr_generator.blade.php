@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mt-4">
                <i class="fas fa-qrcode text-primary"></i> QR Code Generator - Library Visits
            </h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.library.visits.index') }}">Library Visits</a></li>
                <li class="breadcrumb-item active">QR Generator</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <!-- QR Code Display -->
        <div class="col-lg-6">
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-qrcode"></i> QR Code - Library Check-in/Out
                    </h5>
                </div>
                <div class="card-body text-center p-5" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                    <!-- QR Code Container -->
                    <div id="qrcode" class="mb-4 d-inline-block p-4 bg-white rounded shadow" style="border: 5px solid #667eea;"></div>
                    
                    <!-- URL Display -->
                    <div class="alert alert-info mb-4">
                        <strong><i class="fas fa-link"></i> QR URL:</strong><br>
                        <code id="qrUrlText" style="font-size: 14px; word-break: break-all;">{{ $qrUrl }}</code>
                    </div>

                    <!-- Action Buttons -->
                    <div class="btn-group-vertical w-100 gap-2">
                        <button type="button" class="btn btn-lg btn-success" onclick="downloadQR()">
                            <i class="fas fa-download"></i> Download QR Code (PNG)
                        </button>
                        <button type="button" class="btn btn-lg btn-primary" onclick="printQR()">
                            <i class="fas fa-print"></i> Print QR Code
                        </button>
                        <button type="button" class="btn btn-lg btn-info text-white" onclick="copyURL()">
                            <i class="fas fa-copy"></i> Copy URL to Clipboard
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions & Information -->
        <div class="col-lg-6">
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> How to Use
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="text-primary"><i class="fas fa-print"></i> Print Instructions:</h6>
                    <ol class="mb-4">
                        <li>Click <strong>"Download QR Code"</strong> or <strong>"Print QR Code"</strong> button above</li>
                        <li>Print the QR code poster</li>
                        <li>Display at library entrance</li>
                        <li>Visitors scan with their mobile phones</li>
                    </ol>

                    <hr>

                    <h6 class="text-success"><i class="fas fa-mobile-alt"></i> Visitor Instructions:</h6>
                    <ol class="mb-4">
                        <li><strong>Scan QR code</strong> using mobile phone camera or QR scanner app</li>
                        <li><strong>Login</strong> with library account (if not already logged in)</li>
                        <li><strong>Check-in</strong> by selecting visit purpose</li>
                        <li><strong>Check-out</strong> when leaving the library</li>
                    </ol>

                    <hr>

                    <h6 class="text-info"><i class="fas fa-shield-alt"></i> Security Notes:</h6>
                    <ul class="mb-4">
                        <li><i class="fas fa-check text-success"></i> Users <strong>must have an account</strong> to use QR check-in</li>
                        <li><i class="fas fa-check text-success"></i> <strong>Login required</strong> before check-in/out</li>
                        <li><i class="fas fa-check text-success"></i> Users can only check-in/out <strong>themselves</strong></li>
                        <li><i class="fas fa-check text-success"></i> <strong>No duplicate check-ins</strong> allowed</li>
                    </ul>

                    <hr>

                    <h6 class="text-warning"><i class="fas fa-chart-line"></i> Features:</h6>
                    <ul>
                        <li><i class="fas fa-star text-warning"></i> Auto-detect session (morning/afternoon/evening)</li>
                        <li><i class="fas fa-star text-warning"></i> Mobile-friendly interface</li>
                        <li><i class="fas fa-star text-warning"></i> Real-time status display</li>
                        <li><i class="fas fa-star text-warning"></i> Bilingual (Khmer + English)</li>
                    </ul>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card shadow-lg border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> QR Visit Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-primary mb-0" id="todayQRVisits">-</h3>
                                <small class="text-muted">QR Visits Today</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-success mb-0" id="totalQRVisits">-</h3>
                                <small class="text-muted">Total QR Visits</small>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="loadQRStats()">
                        <i class="fas fa-sync-alt"></i> Refresh Stats
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview for Print -->
    <div class="d-none" id="printArea">
        <div style="text-align: center; padding: 40px; font-family: Arial, sans-serif;">
            <h1 style="color: #667eea; margin-bottom: 30px;">
                <i class="fas fa-book-reader"></i> Library Visit System
            </h1>
            <h2 style="color: #333; margin-bottom: 20px;">Scan to Check-in / Check-out</h2>
            <h2 style="color: #333; margin-bottom: 40px;">ស្កេនដើម្បីចូល / ចេញ</h2>
            
            <div id="qrcodePrint" style="display: inline-block; padding: 20px; background: white; border: 5px solid #667eea; border-radius: 10px;"></div>
            
            <h3 style="color: #666; margin-top: 40px; margin-bottom: 20px;">How to Use / របៀបប្រើប្រាស់</h3>
            <div style="text-align: left; max-width: 600px; margin: 0 auto; background: #f8f9fa; padding: 30px; border-radius: 10px;">
                <ol style="font-size: 16px; line-height: 2;">
                    <li><strong>Scan QR code</strong> with your mobile phone / ស្កេន QR code ដោយប្រើទូរសព្ទ</li>
                    <li><strong>Login</strong> with your library account / ចូលប្រើដោយគណនីបណ្ណាល័យ</li>
                    <li><strong>Select purpose</strong> and check-in / ជ្រើសរើសគោលបំណង ហើយចូល</li>
                    <li><strong>Check-out</strong> when leaving / ចេញនៅពេលចាកចេញ</li>
                </ol>
                <div style="background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin-top: 20px; border-radius: 5px;">
                    <strong style="color: #1565c0;"><i class="fas fa-info-circle"></i> Note:</strong> You must have a library account to use this system.
                </div>
            </div>
            
            <div style="margin-top: 40px; padding: 20px; background: #f1f1f1; border-radius: 10px;">
                <p style="margin: 0; color: #666;">
                    <strong>Contact:</strong> Library Administration<br>
                    <strong>URL:</strong> <code style="background: white; padding: 5px 10px; border-radius: 5px;">{{ $qrUrl }}</code>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- QRCode.js Library (Lightweight, no dependencies) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    // QR Code instances
    let qrCodeInstance = null;
    let qrCodePrintInstance = null;

    // Generate QR Code on page load
    document.addEventListener('DOMContentLoaded', function() {
        generateQRCode();
        loadQRStats();
    });

    function generateQRCode() {
        const qrUrl = "{{ $qrUrl }}";
        
        // Clear existing QR codes
        document.getElementById('qrcode').innerHTML = '';
        document.getElementById('qrcodePrint').innerHTML = '';
        
        // Generate QR for display
        qrCodeInstance = new QRCode(document.getElementById("qrcode"), {
            text: qrUrl,
            width: 300,
            height: 300,
            colorDark: "#667eea",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        
        // Generate QR for print (larger)
        qrCodePrintInstance = new QRCode(document.getElementById("qrcodePrint"), {
            text: qrUrl,
            width: 400,
            height: 400,
            colorDark: "#667eea",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }

    function downloadQR() {
        const canvas = document.querySelector('#qrcode canvas');
        if (!canvas) {
            Swal.fire('Error', 'QR Code not generated yet. Please wait a moment.', 'error');
            return;
        }
        
        // Convert canvas to image
        const link = document.createElement('a');
        link.download = 'library-visit-qr-code.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
        
        Swal.fire({
            icon: 'success',
            title: 'Downloaded!',
            text: 'QR Code image has been downloaded.',
            timer: 2000,
            showConfirmButton: false
        });
    }

    function printQR() {
        const printContent = document.getElementById('printArea').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        
        // Reload page to restore functionality
        location.reload();
    }

    function copyURL() {
        const url = document.getElementById('qrUrlText').textContent;
        
        // Copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'URL has been copied to clipboard.',
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(() => {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = url;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'URL has been copied to clipboard.',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }

    function loadQRStats() {
        // Load QR visit statistics
        $.ajax({
            url: '{{ route('admin.library.visits.index') }}',
            method: 'GET',
            data: {
                ajax: true,
                qr_stats_only: true
            },
            success: function(response) {
                // This would require adding a stats endpoint
                // For now, show placeholder
                document.getElementById('todayQRVisits').textContent = '-';
                document.getElementById('totalQRVisits').textContent = '-';
            },
            error: function() {
                document.getElementById('todayQRVisits').textContent = 'N/A';
                document.getElementById('totalQRVisits').textContent = 'N/A';
            }
        });
    }
</script>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printArea, #printArea * {
            visibility: visible;
        }
        #printArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
    
    .btn-group-vertical .btn {
        margin-bottom: 10px;
    }
    
    #qrcode img, #qrcodePrint img {
        margin: 0 auto;
        display: block;
    }
</style>
@endsection






