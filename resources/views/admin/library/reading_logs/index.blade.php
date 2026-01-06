@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mt-4">
                <i class="fas fa-book-reader text-primary"></i> Library Reading Logs (In-Library)
            </h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Library</li>
                <li class="breadcrumb-item active">Reading Logs</li>
            </ol>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateTo">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Session</label>
                    <select class="form-select form-select-sm" id="filterSession">
                        <option value="all">All</option>
                        <option value="morning">Morning</option>
                        <option value="afternoon">Afternoon</option>
                        <option value="evening">Evening</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Visitor Type</label>
                    <select class="form-select form-select-sm" id="filterVisitorType">
                        <option value="all">All</option>
                        <option value="user">User</option>
                        <option value="guest">Guest</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Running Status</label>
                    <select class="form-select form-select-sm" id="filterRunningStatus">
                        <option value="">All</option>
                        <option value="running">Running</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Active Status</label>
                    <select class="form-select form-select-sm" id="filterStatus">
                        <option value="active" selected>Active Only</option>
                        <option value="all">All</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-10">
                    <label class="form-label">Library Item</label>
                    <select class="form-select form-select-sm" id="filterLibraryItem">
                        <option value="">All Items</option>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-primary btn-sm" onclick="applyFilters()">
                    <i class="fas fa-search"></i> Apply Filters
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetFilters()">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </div>
    </div>

    {{-- Action Buttons Card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            @if(auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
            <button type="button" class="btn btn-success" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Add Reading Log
            </button>
            @endif
            <button type="button" class="btn btn-info" onclick="refreshTable()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-table"></i> Reading Logs</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" id="readingLogsTable" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Visitor</th>
                            <th>Visit Date</th>
                            <th>Session</th>
                            <th>Book/Item</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Duration</th>
                            <th>Books in Visit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Create/Edit Modal --}}
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="formModalTitle">
                    <i class="fas fa-book-reader"></i> <span id="formModalTitleText">Add Reading Log</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="readingLogForm">
                @csrf
                <input type="hidden" id="logId" name="id">
                <input type="hidden" id="formMethod" value="POST">
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Visit <span class="text-danger">*</span></label>
                            <select class="form-select" id="visit_id" name="visit_id" required>
                                <option value="">Select Visit...</option>
                            </select>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Search by visitor name or date</small>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Library Item (Book) <span class="text-danger">*</span></label>
                            <select class="form-select" id="library_item_id" name="library_item_id" required>
                                <option value="">Select Item...</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Copy Barcode (Optional)</label>
                            <select class="form-select" id="copy_id" name="copy_id">
                                <option value="">No specific copy</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start Time</label>
                            <input type="datetime-local" class="form-control" id="start_time" name="start_time">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End Time</label>
                            <input type="datetime-local" class="form-control" id="end_time" name="end_time">
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Must be after start time</small>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Note</label>
                            <textarea class="form-control" id="note" name="note" rows="3" maxlength="1000"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Modal --}}
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Reading Log Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Visitor:</strong>
                        <p id="view_visitor_name"></p>
                    </div>
                    <div class="col-md-3">
                        <strong>Visit Date:</strong>
                        <p id="view_visit_date"></p>
                    </div>
                    <div class="col-md-3">
                        <strong>Session:</strong>
                        <p id="view_session"></p>
                    </div>
                    <div class="col-md-12">
                        <strong>Book/Item:</strong>
                        <p id="view_book_title"></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Copy Barcode:</strong>
                        <p id="view_barcode"></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Books Read in This Visit:</strong>
                        <p id="view_books_in_visit"></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Start Time:</strong>
                        <p id="view_start_time"></p>
                    </div>
                    <div class="col-md-4">
                        <strong>End Time:</strong>
                        <p id="view_end_time"></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Duration:</strong>
                        <p id="view_duration"></p>
                    </div>
                    <div class="col-md-12">
                        <strong>Note:</strong>
                        <p id="view_note"></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Created By:</strong>
                        <p id="view_created_by"></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Updated By:</strong>
                        <p id="view_updated_by"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let table;
    const formModal = new bootstrap.Modal(document.getElementById('formModal'));
    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

    // Wait for all libraries to load
    function initializeWhenReady() {
        // Check if all required libraries are loaded
        if (typeof $.fn.select2 === 'undefined') {
            console.log('⏳ Waiting for Select2...');
            setTimeout(initializeWhenReady, 100);
            return;
        }
        
        if (typeof $.fn.DataTable === 'undefined') {
            console.log('⏳ Waiting for DataTables...');
            setTimeout(initializeWhenReady, 100);
            return;
        }
        
        console.log('✅ All libraries ready, initializing page...');
        
        // Initialize Select2 for filters
        $('#filterLibraryItem').select2({
            placeholder: 'All Items',
            allowClear: true,
            ajax: {
                url: '{{ route('admin.library.reading-logs.search-items') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return { results: data.results };
                }
            }
        });

        // Initialize DataTable
        loadTable();

        // Set default date range (last 30 days)
        const today = new Date().toISOString().split('T')[0];
        const lastMonth = new Date();
        lastMonth.setDate(lastMonth.getDate() - 30);
        $('#filterDateFrom').val(lastMonth.toISOString().split('T')[0]);
        $('#filterDateTo').val(today);
    }

    $(document).ready(function() {
        initializeWhenReady();
    });

    function loadTable() {
        table = $('#readingLogsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.library.reading-logs.index') }}',
                type: 'GET',
                data: function(d) {
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                    d.session = $('#filterSession').val();
                    d.visitor_type = $('#filterVisitorType').val();
                    d.running_status = $('#filterRunningStatus').val();
                    d.library_item_id = $('#filterLibraryItem').val();
                    d.status = $('#filterStatus').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables Error:', error);
                    console.error('Response:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Load Data',
                        html: 'Error: ' + error + '<br>Status: ' + xhr.status,
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'visitor', name: 'visitor' },
                { data: 'visit_date', name: 'visit_date' },
                { data: 'session', name: 'session' },
                { data: 'book_title', name: 'book_title' },
                { data: 'start_time', name: 'start_time' },
                { data: 'end_time', name: 'end_time' },
                { data: 'duration', name: 'duration', orderable: false },
                { data: 'books_in_visit', name: 'books_in_visit', orderable: false },
                { data: 'status', name: 'is_active' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[2, 'desc']],
            pageLength: 25,
            responsive: true,
            language: {
                processing: '<i class="fas fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
                emptyTable: 'No reading logs found. Click "Add Reading Log" to create one.',
                zeroRecords: 'No matching records found. Try adjusting your filters.'
            }
        });
    }

    function applyFilters() {
        table.ajax.reload();
    }

    function resetFilters() {
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        $('#filterSession').val('all');
        $('#filterVisitorType').val('all');
        $('#filterRunningStatus').val('');
        $('#filterStatus').val('active');
        $('#filterLibraryItem').val(null).trigger('change');
        table.ajax.reload();
    }

    function refreshTable() {
        table.ajax.reload();
        Swal.fire({
            icon: 'success',
            title: 'Refreshed',
            timer: 1000,
            showConfirmButton: false
        });
    }

    function openCreateModal() {
        $('#readingLogForm')[0].reset();
        $('#logId').val('');
        $('#formMethod').val('POST');
        $('#formModalTitleText').text('Add Reading Log');
        
        // Clear all validation
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Reset Select2
        $('#visit_id').val(null).trigger('change');
        $('#library_item_id').val(null).trigger('change');
        $('#copy_id').val(null).trigger('change');
        
        // Disable copy field initially
        $('#copy_id').prop('disabled', true);
        
        // Initialize Select2 for visit and item
        initializeSelect2();
        
        formModal.show();
    }

    function initializeSelect2() {
        // Visit Select2
        if (!$('#visit_id').hasClass('select2-hidden-accessible')) {
            $('#visit_id').select2({
                dropdownParent: $('#formModal'),
                placeholder: 'Search by visitor name or date...',
                ajax: {
                    url: '{{ route('admin.library.reading-logs.search-visits') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        return { results: data.results };
                    }
                }
            });
        }

        // Item Select2
        if (!$('#library_item_id').hasClass('select2-hidden-accessible')) {
            $('#library_item_id').select2({
                dropdownParent: $('#formModal'),
                placeholder: 'Search by title or ISBN...',
                ajax: {
                    url: '{{ route('admin.library.reading-logs.search-items') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        return { results: data.results };
                    }
                }
            });
            
            // When item changes, reload copy dropdown
            $('#library_item_id').on('change', function() {
                const itemId = $(this).val();
                const $copyField = $('#copy_id');
                
                console.log('📚 Book selected, ID:', itemId);
                
                // Clear current value
                $copyField.val(null).trigger('change');
                
                // Destroy existing Select2 if any
                if ($copyField.hasClass('select2-hidden-accessible')) {
                    $copyField.select2('destroy');
                    console.log('🗑️ Destroyed old Select2 on copy field');
                }
                
                if (itemId) {
                    // Enable the field and initialize with ajax
                    $copyField.prop('disabled', false);
                    console.log('✅ Copy field enabled');
                    
                    $copyField.select2({
                        dropdownParent: $('#formModal'),
                        placeholder: 'Search by barcode or call number...',
                        allowClear: true,
                        ajax: {
                            url: '{{ route('admin.library.reading-logs.search-copies') }}',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                console.log('🔍 Searching copies for book:', itemId, 'Query:', params.term);
                                return { 
                                    q: params.term,
                                    item_id: itemId  // Filter by selected item
                                };
                            },
                            processResults: function(data) {
                                console.log('📦 Copy search results:', data.results.length, 'copies found');
                                return { results: data.results };
                            }
                        }
                    });
                    
                    console.log('✅ Select2 initialized on copy field');
                } else {
                    // No item selected - disable field
                    console.log('❌ No book selected, disabling copy field');
                    $copyField.prop('disabled', true);
                    $copyField.select2({
                        dropdownParent: $('#formModal'),
                        placeholder: 'Select a book first...',
                        allowClear: true,
                        disabled: true
                    });
                }
            });
        }

        // Copy Select2 (initially disabled until item selected)
        if (!$('#copy_id').hasClass('select2-hidden-accessible')) {
            $('#copy_id').select2({
                dropdownParent: $('#formModal'),
                placeholder: 'Select a book first...',
                allowClear: true,
                disabled: true
            });
        }
    }

    // Form submission
    $('#readingLogForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const method = $('#formMethod').val();
        const id = $('#logId').val();
        
        let url = '{{ route('admin.library.reading-logs.store') }}';
        if (method === 'PUT') {
            url = `/admin/library/reading-logs/${id}`;
        }
        
        Swal.fire({
            title: 'Processing...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                Swal.fire('Success!', response.message, 'success');
                formModal.hide();
                table.ajax.reload();
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    displayValidationErrors(xhr.responseJSON.errors);
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Operation failed.', 'error');
                }
            }
        });
    });

    function displayValidationErrors(errors) {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        $.each(errors, function(field, messages) {
            const input = $(`#${field}`);
            input.addClass('is-invalid');
            input.next('.invalid-feedback').text(messages[0]);
        });
    }

    function viewLog(id) {
        $.get(`/admin/library/reading-logs/${id}`, function(response) {
            const data = response.data;
            $('#view_visitor_name').text(data.visitor_name);
            $('#view_visit_date').text(data.visit_date || 'N/A');
            $('#view_session').text(data.session);
            $('#view_book_title').text(data.book_title);
            $('#view_barcode').text(data.barcode);
            $('#view_books_in_visit').text(data.books_in_visit + ' book(s)');
            $('#view_start_time').text(data.start_time || 'Not started');
            $('#view_end_time').text(data.end_time || 'Not ended');
            $('#view_duration').text(data.duration);
            $('#view_note').text(data.note || 'N/A');
            $('#view_created_by').text(data.created_by || 'N/A');
            $('#view_updated_by').text(data.updated_by || 'N/A');
            
            viewModal.show();
        });
    }

    function editLog(id) {
        $.get(`/admin/library/reading-logs/${id}`, function(response) {
            const data = response.data;
            
            $('#logId').val(data.id);
            $('#formMethod').val('PUT');
            $('#formModalTitleText').text('Edit Reading Log');
            
            // Set visit
            $('#visit_id').empty().append(new Option(data.visitor_name + ' - ' + data.visit_date, data.visit_id, true, true));
            
            // Set item
            $('#library_item_id').empty().append(new Option(data.book_title, data.library_item_id, true, true));
            
            // Trigger item change to enable copy dropdown
            $('#library_item_id').trigger('change');
            
            // Set copy if exists (after a small delay to let item change event complete)
            if (data.copy_id) {
                setTimeout(function() {
                    $('#copy_id').empty().append(new Option(data.barcode, data.copy_id, true, true));
                }, 500);
            }
            
            // Set times
            if (data.start_time) {
                $('#start_time').val(data.start_time.replace(' ', 'T').substring(0, 16));
            }
            if (data.end_time) {
                $('#end_time').val(data.end_time.replace(' ', 'T').substring(0, 16));
            }
            $('#note').val(data.note);
            
            initializeSelect2();
            formModal.show();
        });
    }

    function startReading(id) {
        Swal.fire({
            title: 'Start Reading?',
            text: 'This will set start time to now.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Start',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`/admin/library/reading-logs/${id}/start`, { _token: '{{ csrf_token() }}' }, function(response) {
                    Swal.fire('Started!', response.message, 'success');
                    table.ajax.reload();
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to start reading.', 'error');
                });
            }
        });
    }

    function stopReading(id) {
        Swal.fire({
            title: 'Stop Reading?',
            text: 'This will set end time to now.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Stop',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`/admin/library/reading-logs/${id}/stop`, { _token: '{{ csrf_token() }}' }, function(response) {
                    if (response.success) {
                        const minutes = response.minutes_read || 0;
                        const duration = response.duration || 'N/A';
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Stopped!',
                            html: `
                                <p><strong>${response.message}</strong></p>
                                <hr>
                                <div class="text-start">
                                    <p><i class="fas fa-clock text-primary"></i> <strong>End Time:</strong> ${response.end_time}</p>
                                    <p><i class="fas fa-hourglass-half text-success"></i> <strong>Duration:</strong> ${duration}</p>
                                    <p><i class="fas fa-stopwatch text-info"></i> <strong>Total Minutes:</strong> ${minutes} min</p>
                                </div>
                            `,
                            confirmButtonText: 'OK'
                        });
                        table.ajax.reload();
                    }
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to stop reading.', 'error');
                });
            }
        });
    }

    function deleteLog(id) {
        Swal.fire({
            title: 'Delete Reading Log?',
            text: 'This will deactivate the log (soft delete).',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/library/reading-logs/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire('Deleted!', response.message, 'success');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete.', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush


