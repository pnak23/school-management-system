@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-clipboard-check text-primary"></i> Library Stock Taking</h2>
                @if(Auth::user()->hasAnyRole(['admin', 'manager', 'staff']))
                <div>
                    <button class="btn btn-success" onclick="openCreateModal()">
                        <i class="fas fa-plus"></i> New Audit
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <i class="fas fa-filter"></i> Filters
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="all">All Status</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Active Status</label>
                    <select id="filterIsActive" class="form-select form-select-sm">
                        <option value="1" selected>Active Only</option>
                        <option value="all">All</option>
                        <option value="0">Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm w-100" onclick="loadStockTakings()">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Takings Table Card -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Stock Takings List
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="stockTakingsTable" class="table table-striped table-bordered table-hover" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Reference No</th>
                            <th width="15%">Started At</th>
                            <th width="15%">Ended At</th>
                            <th width="12%">Status</th>
                            <th width="15%">Conducted By</th>
                            <th width="8%">Active</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Stock Taking Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createModalLabel">
                    <i class="fas fa-plus"></i> Create New Stock Taking
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note" class="form-label">Note (Optional)</label>
                        <textarea class="form-control" id="note" name="note" rows="3" placeholder="Enter notes for this stock taking audit..."></textarea>
                        <div class="form-text">
                            Reference number will be auto-generated. Status will be set to "In Progress".
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Create & Start Scanning
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Stock Taking Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="fas fa-edit"></i> Edit Stock Taking
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm">
                <input type="hidden" id="editId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reference No</label>
                        <input type="text" class="form-control" id="editReferenceNo" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label for="editNote" class="form-label">Note</label>
                        <textarea class="form-control" id="editNote" name="note" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="status">
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <div class="form-text">
                            Changing to "Completed" or "Cancelled" will set the end time automatically.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let stockTakingsTable;

    $(document).ready(function() {
        initDataTable();
        loadStockTakings();
    });

    // Initialize DataTable
    function initDataTable() {
        console.log('Initializing Stock Takings DataTable...');
        console.log('AJAX URL:', '{{ route("admin.library.stock-takings.index") }}');
        
        stockTakingsTable = $('#stockTakingsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.library.stock-takings.index") }}',
                type: 'GET',
                data: function(d) {
                    d.status = $('#filterStatus').val();
                    d.is_active = $('#filterIsActive').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'reference_no', name: 'reference_no' },
                { data: 'started_at', name: 'started_at' },
                { data: 'ended_at', name: 'ended_at' },
                { data: 'status_badge', name: 'status', orderable: true, searchable: false },
                { data: 'conducted_by', name: 'conducted_by' },
                { data: 'active_toggle', name: 'is_active', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[1, 'desc']], // Order by reference_no DESC
            pageLength: 10,
            responsive: true
        });
    }

    // Load/reload stock takings
    function loadStockTakings() {
        stockTakingsTable.ajax.reload();
    }

    // Open create modal
    function openCreateModal() {
        $('#createForm')[0].reset();
        $('#createModal').modal('show');
    }

    // Create stock taking
    $('#createForm').on('submit', function(e) {
        e.preventDefault();

        let formData = {
            note: $('#note').val(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: '{{ route("admin.library.stock-takings.store") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#createModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Created!',
                        text: response.message,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-qrcode"></i> Start Scanning',
                        cancelButtonText: 'Stay Here',
                        confirmButtonColor: '#28a745'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to scan page
                            window.location.href = '{{ url("admin/library/stock-takings") }}/' + response.data.id;
                        } else {
                            loadStockTakings();
                        }
                    });
                }
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'Failed to create stock taking.';
                Swal.fire('Error!', message, 'error');
            }
        });
    });

    // Open edit modal
    function openEditModal(id) {
        $.ajax({
            url: '{{ url("admin/library/stock-takings") }}/' + id,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    let data = response.data;
                    $('#editId').val(data.id);
                    $('#editReferenceNo').val(data.reference_no);
                    $('#editNote').val(data.note || '');
                    $('#editStatus').val(data.status);
                    $('#editModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Failed to load stock taking data.', 'error');
            }
        });
    }

    // Update stock taking
    $('#editForm').on('submit', function(e) {
        e.preventDefault();

        let id = $('#editId').val();
        let formData = {
            note: $('#editNote').val(),
            status: $('#editStatus').val(),
            _token: '{{ csrf_token() }}',
            _method: 'PUT'
        };

        $.ajax({
            url: '{{ url("admin/library/stock-takings") }}/' + id,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editModal').modal('hide');
                    Swal.fire('Updated!', response.message, 'success');
                    loadStockTakings();
                }
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'Failed to update stock taking.';
                Swal.fire('Error!', message, 'error');
            }
        });
    });

    // Toggle active status
    function toggleStatus(id, checkbox) {
        $.ajax({
            url: '{{ url("admin/library/stock-takings") }}/' + id + '/toggle-status',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                // Revert checkbox
                checkbox.checked = !checkbox.checked;
                let message = xhr.responseJSON?.message || 'Failed to toggle status.';
                Swal.fire('Error!', message, 'error');
            }
        });
    }

    // Delete stock taking
    function deleteStockTaking(id) {
        @if(Auth::user()->hasRole('admin'))
        // Admin: Choose soft or permanent delete
        Swal.fire({
            title: 'Delete Stock Taking?',
            text: 'Choose delete type:',
            icon: 'warning',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash"></i> Soft Delete',
            denyButtonText: '<i class="fas fa-trash-alt"></i> Permanent Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ffc107',
            denyButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                // Soft delete
                performDelete(id, false);
            } else if (result.isDenied) {
                // Permanent delete
                Swal.fire({
                    title: 'Permanently Delete?',
                    text: 'This action cannot be undone!',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete permanently!',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc3545'
                }).then((confirmResult) => {
                    if (confirmResult.isConfirmed) {
                        performDelete(id, true);
                    }
                });
            }
        });
        @else
        // Manager: Soft delete only
        Swal.fire({
            title: 'Delete Stock Taking?',
            text: 'This will deactivate the stock taking.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                performDelete(id, false);
            }
        });
        @endif
    }

    // Perform delete
    function performDelete(id, permanent) {
        let url = permanent 
            ? '{{ url("admin/library/stock-takings") }}/' + id + '/force-delete'
            : '{{ url("admin/library/stock-takings") }}/' + id;

        $.ajax({
            url: url,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Deleted!', response.message, 'success');
                    loadStockTakings();
                }
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'Failed to delete stock taking.';
                Swal.fire('Error!', message, 'error');
            }
        });
    }
</script>
@endpush

