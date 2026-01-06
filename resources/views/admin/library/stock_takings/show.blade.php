@extends('layouts.app')

@section('title', 'Stock Taking Scanner - ' . $stockTaking->reference_no)

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-barcode text-primary"></i> Stock Taking Scanner
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.library.stock-takings.index') }}">Stock Taking</a></li>
                    <li class="breadcrumb-item active">{{ $stockTaking->reference_no }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.library.stock-takings.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Stock Taking Info Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h5 class="mb-1"><i class="fas fa-hashtag"></i> Reference No</h5>
                    <p class="mb-0 text-muted">{{ $stockTaking->reference_no }}</p>
                </div>
                <div class="col-md-3">
                    <h5 class="mb-1"><i class="fas fa-info-circle"></i> Status</h5>
                    <p class="mb-0">
                        @if($stockTaking->status === 'in_progress')
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-spinner fa-spin"></i> In Progress
                            </span>
                        @elseif($stockTaking->status === 'completed')
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle"></i> Completed
                            </span>
                        @elseif($stockTaking->status === 'cancelled')
                            <span class="badge bg-danger">
                                <i class="fas fa-times-circle"></i> Cancelled
                            </span>
                        @endif
                    </p>
                </div>
                <div class="col-md-3">
                    <h5 class="mb-1"><i class="fas fa-calendar"></i> Started At</h5>
                    <p class="mb-0 text-muted">
                        {{ $stockTaking->started_at ? $stockTaking->started_at->format('Y-m-d H:i') : 'N/A' }}
                    </p>
                </div>
                <div class="col-md-3 text-end">
                    @if($stockTaking->status === 'in_progress' && (auth()->user()->hasAnyRole(['admin', 'manager'])))
                        <button type="button" class="btn btn-success btn-lg" id="completeBtn">
                            <i class="fas fa-check-double"></i> Complete Audit
                        </button>
                    @endif
                </div>
            </div>

            @if($stockTaking->note)
            <div class="row mt-3">
                <div class="col-12">
                    <h5 class="mb-1"><i class="fas fa-sticky-note"></i> Note</h5>
                    <p class="mb-0 text-muted">{{ $stockTaking->note }}</p>
                </div>
            </div>
            @endif

            @if($stockTaking->ended_at)
            <div class="row mt-3">
                <div class="col-12">
                    <h5 class="mb-1"><i class="fas fa-flag-checkered"></i> Ended At</h5>
                    <p class="mb-0 text-muted">{{ $stockTaking->ended_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-success shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Found</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="summaryFound">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-danger shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Lost</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="summaryLost">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Damaged</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="summaryDamaged">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-info shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Scanned</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="summaryTotal">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list-ol fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Section -->
    <div class="card shadow-sm mb-4" id="scannerCard">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-barcode"></i> Barcode Scanner</h5>
        </div>
        <div class="card-body">
            @if($stockTaking->status === 'in_progress')
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-barcode"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control form-control-lg" 
                                id="barcodeInput" 
                                placeholder="Scan or type barcode and press Enter..."
                                autocomplete="off"
                                autofocus>
                            <button class="btn btn-success" type="button" id="scanBtn">
                                <i class="fas fa-search"></i> Scan
                            </button>
                        </div>
                        <div class="form-text text-center mt-2">
                            <i class="fas fa-info-circle"></i> 
                            Use barcode scanner or type manually. Press Enter to scan.
                        </div>
                    </div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="row mt-3">
                    <div class="col-md-8 mx-auto text-center">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-success btn-sm" data-result="found" id="btnFound">
                                <i class="fas fa-check"></i> Mark Found <kbd>F1</kbd>
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" data-result="damaged" id="btnDamaged">
                                <i class="fas fa-tools"></i> Mark Damaged <kbd>F2</kbd>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" data-result="lost" id="btnLost">
                                <i class="fas fa-exclamation-triangle"></i> Mark Lost <kbd>F3</kbd>
                            </button>
                        </div>
                        <p class="text-muted small mt-2 mb-0" id="quickActionHint" style="display:none;">
                            Click action then scan barcode
                        </p>
                    </div>
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> 
                    Scanner is disabled. This stock taking is {{ $stockTaking->status }}.
                </div>
            @endif
        </div>
    </div>

    <!-- Scanned Items Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list"></i> Scanned Items</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="refreshTableBtn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="scannedItemsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Barcode</th>
                            <th width="25%">Book Title</th>
                            <th width="10%">Call Number</th>
                            <th width="10%">Result</th>
                            <th width="15%">Scanned At</th>
                            <th width="10%">Scanned By</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Flash Overlay for Visual Feedback -->
<div id="flashOverlay" class="flash-overlay"></div>

<!-- Audio Elements for Beep Sounds -->
<audio id="successBeep" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjGJ0fHRgDIHHGi57OirVxUJTKXh8axjHQU2jdT0yXcrBSh+zPDdkUAKFV+07OqqVxYKSKHh8rZsIQYyi9Lx0oM0Bxtmue7oq1cWCk2m4vGsZB4FNYzU9Mp5KwUpeM3v3ZFDC" type="audio/wav">
</audio>
<audio id="errorBeep" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjGJ0fHRgDIHHGi57OirVxUJTKXh8axjHQU2jdT0yXcrBSh+zPDdkUAKFV+07OqqVxYKSKHh8rZsIQYyi9Lx0oM0Bxtmue7oq1cWCk2m4vGsZB4FNYzU9Mp5KwUpeM3v3ZFDC" type="audio/wav">
</audio>

<!-- Keyboard Shortcuts Hint -->
@if($stockTaking->status === 'in_progress')
<div class="keyboard-hint">
    <div><strong>⌨️ Keyboard Shortcuts:</strong></div>
    <div class="mt-2">
        <kbd>F1</kbd> = Found &nbsp;
        <kbd>F2</kbd> = Damaged &nbsp;
        <kbd>F3</kbd> = Lost
    </div>
    <div class="mt-1 text-muted" style="font-size: 10px;">
        Press shortcut then scan barcode
    </div>
</div>
@endif

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Scanned Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editItemForm">
                <div class="modal-body">
                    <input type="hidden" id="editItemId">
                    
                    <div class="mb-3">
                        <label class="form-label">Barcode</label>
                        <input type="text" class="form-control" id="editItemBarcode" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Scan Result <span class="text-danger">*</span></label>
                        <select class="form-select" id="editScanResult" required>
                            <option value="found">Found</option>
                            <option value="lost">Lost</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Condition Note</label>
                        <textarea class="form-control" id="editConditionNote" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .border-left-success {
        border-left: 4px solid #1cc88a !important;
    }
    .border-left-danger {
        border-left: 4px solid #e74a3b !important;
    }
    .border-left-warning {
        border-left: 4px solid #f6c23e !important;
    }
    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }
    #barcodeInput {
        font-size: 1.5rem;
        font-weight: bold;
        text-align: center;
    }
    .scan-success {
        animation: pulse-green 0.5s;
    }
    @keyframes pulse-green {
        0% { background-color: #fff; border: 3px solid #28a745; }
        50% { background-color: #d4edda; border: 3px solid #28a745; }
        100% { background-color: #fff; border: 3px solid #28a745; }
    }
    .scan-error {
        animation: pulse-red 0.5s;
    }
    @keyframes pulse-red {
        0% { background-color: #fff; border: 3px solid #dc3545; }
        50% { background-color: #f8d7da; border: 3px solid #dc3545; }
        100% { background-color: #fff; border: 3px solid #dc3545; }
    }
    
    /* Full screen flash overlay for enhanced feedback */
    .flash-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .flash-overlay.flash-success {
        background-color: rgba(40, 167, 69, 0.2);
        animation: flash-success-animation 0.5s;
    }
    .flash-overlay.flash-error {
        background-color: rgba(220, 53, 69, 0.2);
        animation: flash-error-animation 0.5s;
    }
    @keyframes flash-success-animation {
        0%, 100% { opacity: 0; }
        50% { opacity: 1; }
    }
    @keyframes flash-error-animation {
        0%, 100% { opacity: 0; }
        50% { opacity: 1; }
    }
    
    /* Keyboard shortcut hints */
    .keyboard-hint {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 15px;
        border-radius: 8px;
        font-size: 12px;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }
    .keyboard-hint kbd {
        background: #555;
        padding: 3px 8px;
        border-radius: 4px;
        margin: 0 5px;
        font-weight: bold;
    }
    
    /* Active button highlight for keyboard shortcuts */
    .btn-shortcut-active {
        animation: button-pulse 0.3s;
        box-shadow: 0 0 10px rgba(255, 193, 7, 0.8);
    }
    @keyframes button-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    const stockTakingId = {{ $stockTaking->id }};
    const isInProgress = {{ $stockTaking->status === 'in_progress' ? 'true' : 'false' }};
    let selectedResult = 'found'; // Default scan result
    let table;

    // Initialize DataTable
    function initDataTable() {
        table = $('#scannedItemsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.library.stock-taking-items.data", $stockTaking->id) }}',
                type: 'GET',
                error: function(xhr) {
                    console.error('DataTable error:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load scanned items'
                    });
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'barcode', name: 'copy.barcode' },
                { data: 'book_title', name: 'copy.item.title' },
                { data: 'call_number', name: 'copy.call_number' },
                { data: 'scan_result', name: 'scan_result', orderable: true, searchable: false },
                { data: 'scanned_at', name: 'scanned_at' },
                { data: 'scanned_by', name: 'scannedBy.name' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            columnDefs: [
                {
                    targets: [4, 7], // scan_result and actions columns
                    render: function(data, type, row) {
                        return data; // Render as raw HTML
                    }
                }
            ],
            order: [[5, 'desc']], // Sort by scanned_at desc
            pageLength: 25,
            drawCallback: function() {
                updateSummary();
            }
        });
    }

    initDataTable();

    // Update summary cards (with animation)
    function updateSummary() {
        $.ajax({
            url: '{{ route("admin.library.stock-taking-items.data", $stockTaking->id) }}',
            method: 'GET',
            data: { summary: 1 },
            success: function(response) {
                if (response.summary) {
                    // Update each card with animation if value changed
                    const $foundCard = $('#summaryFound').closest('.card');
                    const $lostCard = $('#summaryLost').closest('.card');
                    const $damagedCard = $('#summaryDamaged').closest('.card');
                    const $totalCard = $('#summaryTotal').closest('.card');
                    
                    // Found
                    if ($('#summaryFound').text() != (response.summary.found || 0)) {
                        $foundCard.addClass('summary-update');
                        setTimeout(() => $foundCard.removeClass('summary-update'), 500);
                    }
                    $('#summaryFound').text(response.summary.found || 0);
                    
                    // Lost
                    if ($('#summaryLost').text() != (response.summary.lost || 0)) {
                        $lostCard.addClass('summary-update');
                        setTimeout(() => $lostCard.removeClass('summary-update'), 500);
                    }
                    $('#summaryLost').text(response.summary.lost || 0);
                    
                    // Damaged
                    if ($('#summaryDamaged').text() != (response.summary.damaged || 0)) {
                        $damagedCard.addClass('summary-update');
                        setTimeout(() => $damagedCard.removeClass('summary-update'), 500);
                    }
                    $('#summaryDamaged').text(response.summary.damaged || 0);
                    
                    // Total
                    if ($('#summaryTotal').text() != (response.summary.total || 0)) {
                        $totalCard.addClass('summary-update');
                        setTimeout(() => $totalCard.removeClass('summary-update'), 500);
                    }
                    $('#summaryTotal').text(response.summary.total || 0);
                }
            }
        });
    }

    // Initial summary load
    updateSummary();

    // Scanner functionality
    if (isInProgress) {
        const $barcodeInput = $('#barcodeInput');
        const $scanBtn = $('#scanBtn');

        // Auto-focus on page load
        $barcodeInput.trigger('focus');

        // Quick action buttons
        $('[data-result]').on('click', function() {
            selectedResult = $(this).data('result');
            $('[data-result]').removeClass('active');
            $(this).addClass('active');
            $('#quickActionHint').show();
            $barcodeInput.trigger('focus');
        });

        // Keyboard shortcuts (F1, F2, F3)
        $(document).on('keydown', function(e) {
            // Only handle F-keys when scanner is active and not in modal
            if (!isInProgress || $('.modal:visible').length > 0) return;
            
            let targetBtn = null;
            let newResult = null;
            
            if (e.key === 'F1') {
                e.preventDefault();
                targetBtn = $('#btnFound');
                newResult = 'found';
            } else if (e.key === 'F2') {
                e.preventDefault();
                targetBtn = $('#btnDamaged');
                newResult = 'damaged';
            } else if (e.key === 'F3') {
                e.preventDefault();
                targetBtn = $('#btnLost');
                newResult = 'lost';
            }
            
            if (targetBtn && newResult) {
                selectedResult = newResult;
                $('[data-result]').removeClass('active');
                targetBtn.addClass('active');
                $('#quickActionHint').show();
                
                // Visual feedback for keyboard shortcut
                targetBtn.addClass('btn-shortcut-active');
                setTimeout(() => targetBtn.removeClass('btn-shortcut-active'), 300);
                
                $barcodeInput.trigger('focus');
            }
        });

        // Scan on Enter key
        $barcodeInput.on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                performScan();
            }
        });

        // Scan on button click
        $scanBtn.on('click', function() {
            performScan();
        });

        // Perform scan
        function performScan() {
            const barcode = $barcodeInput.val().trim();
            
            if (!barcode) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Empty Barcode',
                    text: 'Please enter a barcode',
                    timer: 2000
                });
                return;
            }

            // Disable input during scan
            $barcodeInput.prop('disabled', true);
            $scanBtn.prop('disabled', true);

            $.ajax({
                url: '{{ route("admin.library.stock-taking-items.scan", $stockTaking->id) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    barcode: barcode,
                    scan_result: selectedResult
                },
                success: function(response) {
                    if (response.success) {
                        // 🔊 Play success beep sound
                        try {
                            const successBeep = document.getElementById('successBeep');
                            if (successBeep) {
                                successBeep.currentTime = 0;
                                successBeep.play().catch(e => console.log('Audio play prevented:', e));
                            }
                        } catch (e) {
                            console.log('Audio error:', e);
                        }

                        // 🌈 Full screen flash overlay (green)
                        $('#flashOverlay').addClass('flash-success');
                        setTimeout(() => $('#flashOverlay').removeClass('flash-success'), 500);

                        // ✅ Input field success animation
                        $barcodeInput.addClass('scan-success');
                        setTimeout(() => $barcodeInput.removeClass('scan-success'), 600);

                        // 📊 Animate summary cards
                        $('.card.border-left-success, .card.border-left-warning, .card.border-left-danger, .card.border-left-info')
                            .addClass('summary-update');
                        setTimeout(() => {
                            $('.card').removeClass('summary-update');
                        }, 500);

                        // 🔔 Toast notification
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });

                        Toast.fire({
                            icon: 'success',
                            title: response.message || 'Scanned successfully!'
                        });

                        // 🔄 Refresh table and summary (live update)
                        table.ajax.reload(null, false);
                        updateSummary();

                        // 🎯 Clear input and refocus
                        $barcodeInput.val('').trigger('focus');

                        // 🔄 Reset quick action
                        selectedResult = 'found';
                        $('[data-result]').removeClass('active');
                        $('#quickActionHint').hide();
                    }
                },
                error: function(xhr) {
                    // 🔊 Play error beep sound
                    try {
                        const errorBeep = document.getElementById('errorBeep');
                        if (errorBeep) {
                            errorBeep.currentTime = 0;
                            errorBeep.play().catch(e => console.log('Audio play prevented:', e));
                        }
                    } catch (e) {
                        console.log('Audio error:', e);
                    }

                    // 🌈 Full screen flash overlay (red)
                    $('#flashOverlay').addClass('flash-error');
                    setTimeout(() => $('#flashOverlay').removeClass('flash-error'), 500);

                    // ❌ Input field error animation
                    $barcodeInput.addClass('scan-error');
                    setTimeout(() => $barcodeInput.removeClass('scan-error'), 600);

                    let errorMsg = 'Scan failed';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    // 🚨 Error alert
                    Swal.fire({
                        icon: 'error',
                        title: 'Scan Error',
                        text: errorMsg,
                        timer: 3000
                    });

                    $barcodeInput.val('').trigger('focus');
                },
                complete: function() {
                    // Re-enable input
                    $barcodeInput.prop('disabled', false);
                    $scanBtn.prop('disabled', false);
                }
            });
        }

        // Keep focus on input (scanner mode)
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.modal').length && isInProgress) {
                $barcodeInput.trigger('focus');
            }
        });
    }

    // Refresh table button
    $('#refreshTableBtn').on('click', function() {
        table.ajax.reload();
        updateSummary();
    });

    // Complete audit button
    $('#completeBtn').on('click', function() {
        Swal.fire({
            title: 'Complete Stock Taking?',
            text: 'This will finalize the audit. You cannot scan more items after completion.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Complete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.library.stock-takings.update", $stockTaking->id) }}',
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: 'completed'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Completed!',
                                text: 'Stock taking has been completed.',
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to complete audit'
                        });
                    }
                });
            }
        });
    });

    // Edit item
    $(document).on('click', '.edit-item-btn', function() {
        const itemId = $(this).data('id');
        
        $.ajax({
            url: `/admin/library/stock-taking-items/${itemId}`,
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    const item = response.data;
                    $('#editItemId').val(item.id);
                    $('#editItemBarcode').val(item.copy?.barcode || 'N/A');
                    $('#editScanResult').val(item.scan_result);
                    $('#editConditionNote').val(item.condition_note || '');
                    $('#editItemModal').modal('show');
                }
            }
        });
    });

    // Submit edit form
    $('#editItemForm').on('submit', function(e) {
        e.preventDefault();
        const itemId = $('#editItemId').val();

        $.ajax({
            url: `/admin/library/stock-taking-items/${itemId}`,
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                scan_result: $('#editScanResult').val(),
                condition_note: $('#editConditionNote').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#editItemModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Item updated successfully',
                        timer: 2000
                    });
                    table.ajax.reload(null, false);
                    updateSummary();
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to update item'
                });
            }
        });
    });

    // Delete item
    $(document).on('click', '.delete-item-btn', function() {
        const itemId = $(this).data('id');
        
        Swal.fire({
            title: 'Delete this scanned item?',
            text: 'This will remove it from the list',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/library/stock-taking-items/${itemId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Item removed successfully',
                                timer: 2000
                            });
                            table.ajax.reload(null, false);
                            updateSummary();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to delete item'
                        });
                    }
                });
            }
        });
    });

    // Global functions for onclick handlers (called from DataTables)
    window.editItem = function(itemId) {
        $.ajax({
            url: '/admin/library/stock-taking-items/' + itemId,
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    const item = response.data;
                    // Open edit modal (you can create this modal or show SweetAlert)
                    Swal.fire({
                        title: 'Edit Scanned Item',
                        html: `
                            <div class="text-start">
                                <div class="mb-3">
                                    <label class="form-label">Barcode</label>
                                    <input type="text" class="form-control" value="${item.barcode || 'N/A'}" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Scan Result</label>
                                    <select class="form-select" id="swal-scan-result">
                                        <option value="found" ${item.scan_result === 'found' ? 'selected' : ''}>Found</option>
                                        <option value="damaged" ${item.scan_result === 'damaged' ? 'selected' : ''}>Damaged</option>
                                        <option value="lost" ${item.scan_result === 'lost' ? 'selected' : ''}>Lost</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Condition Note (Optional)</label>
                                    <textarea class="form-control" id="swal-condition-note" rows="3">${item.condition_note || ''}</textarea>
                                </div>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update',
                        cancelButtonText: 'Cancel',
                        preConfirm: () => {
                            return {
                                scan_result: document.getElementById('swal-scan-result').value,
                                condition_note: document.getElementById('swal-condition-note').value
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/admin/library/stock-taking-items/' + itemId,
                                method: 'PUT',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    scan_result: result.value.scan_result,
                                    condition_note: result.value.condition_note
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Updated!',
                                            text: 'Item updated successfully',
                                            timer: 2000
                                        });
                                        table.ajax.reload(null, false);
                                        updateSummary();
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: xhr.responseJSON?.message || 'Failed to update item'
                                    });
                                }
                            });
                        }
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load item data'
                });
            }
        });
    };

    window.deleteItem = function(itemId) {
        Swal.fire({
            title: 'Delete this scanned item?',
            text: 'This will remove it from the scan list',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/library/stock-taking-items/' + itemId,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Item removed successfully',
                                timer: 2000
                            });
                            table.ajax.reload(null, false);
                            updateSummary();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to delete item'
                        });
                    }
                });
            }
        });
    };
});
</script>
@endpush

