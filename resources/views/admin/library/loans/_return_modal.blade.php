<!-- Return Book Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Return Book</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="returnLoanInfo" class="mb-3"></div>
                
                <form id="returnForm">
                    <input type="hidden" id="returnLoanId">
                    
                    <div class="mb-3">
                        <label for="returnNote" class="form-label">Return Note (Optional)</label>
                        <textarea class="form-control" id="returnNote" rows="3" maxlength="500" 
                                  placeholder="Add notes about book condition, damages, etc..."></textarea>
                        <small class="text-muted">Document any damage or special circumstances</small>
                    </div>

                    <!-- Received By Staff -->
                    <div class="mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-secondary"><i class="fas fa-user-check"></i> Received By (Staff)</h6>
                                
                                <div class="mb-2">
                                    <label class="form-label">Current Staff</label>
                                    <input type="text" class="form-control" value="{{ $authStaff ? ($authStaff->english_name ?? $authStaff->khmer_name) . ' (' . ($authStaff->staff_code ?? 'N/A') . ')' : auth()->user()->name . ' (No staff record)' }}" readonly>
                                    <small class="text-muted">This return will be received by you</small>
                                </div>

                                @if(auth()->user()->hasAnyRole(['admin', 'manager']))
                                <div>
                                    <label for="receivedByOverride" class="form-label">Override Received By (Optional)</label>
                                    <select class="form-select" id="receivedByOverride">
                                        <option value="">Use current staff (default)</option>
                                        @foreach($staffList as $staff)
                                            <option value="{{ $staff->id }}">
                                                {{ $staff->khmer_name ?? $staff->english_name }} ({{ $staff->staff_code ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Only override if receiving on behalf of another staff</small>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> 
                        Returning this book will:
                        <ul class="mb-0 mt-2">
                            <li>Set the loan status to "Returned"</li>
                            <li>Set the copy status back to "Available"</li>
                            <li>Record the return timestamp</li>
                            <li>Record you as the receiving staff</li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmReturn()">
                    <i class="fas fa-check"></i> Confirm Return
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Open return modal
function openReturnModal(id) {
    $.ajax({
        url: `/admin/library/loans/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const loan = response.data;
                
                // Check if already returned
                if (loan.returned_at) {
                    Swal.fire('Already Returned', 'This book has already been returned.', 'info');
                    return;
                }
                
                // Display loan info
                let html = `
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary">Loan Details</h6>
                            <p class="mb-1"><strong>Barcode:</strong> ${loan.barcode}</p>
                            <p class="mb-1"><strong>Title:</strong> ${loan.book_title}</p>
                            <p class="mb-1"><strong>Borrower:</strong> ${loan.borrower_type.charAt(0).toUpperCase() + loan.borrower_type.slice(1)} - ${loan.borrower_name}</p>
                            <p class="mb-1"><strong>Borrowed:</strong> ${loan.borrowed_at}</p>
                            <p class="mb-1"><strong>Due Date:</strong> ${loan.due_date}</p>
                `;
                
                // Check if overdue
                if (loan.is_overdue) {
                    const daysOverdue = Math.floor(loan.days_overdue); // Convert to integer
                    html += `
                        <div class="alert alert-danger mt-2 mb-0">
                            <i class="fas fa-exclamation-triangle"></i> <strong>OVERDUE!</strong><br>
                            This book is ${daysOverdue} day${daysOverdue !== 1 ? 's' : ''} overdue.
                        </div>
                    `;
                } else {
                    html += '<p class="mb-0 text-success"><i class="fas fa-check"></i> On time</p>';
                }
                
                html += `
                        </div>
                    </div>
                `;
                
                $('#returnLoanInfo').html(html);
                $('#returnLoanId').val(loan.id);
                $('#returnNote').val('');
                $('#receivedByOverride').val(''); // Reset override dropdown
                $('#returnModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load loan data.', 'error');
        }
    });
}

// Confirm return
function confirmReturn() {
    const loanId = $('#returnLoanId').val();
    const receivedByOverride = $('#receivedByOverride').val();
    
    const formData = {
        return_note: $('#returnNote').val()
    };

    // Only include received_by_staff_id if admin/manager selected an override
    if (receivedByOverride) {
        formData.received_by_staff_id = receivedByOverride;
    }

    // If override selected, confirm
    if (receivedByOverride) {
        Swal.fire({
            title: 'Staff Override Selected',
            text: 'You selected a staff override. This return will be received by the selected staff, not you. Continue?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                submitReturn(loanId, formData);
            }
        });
    } else {
        submitReturn(loanId, formData);
    }
}

// Submit return request
function submitReturn(loanId, formData) {
    $.ajax({
        url: `/admin/library/loans/${loanId}/return`,
        type: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#returnModal').modal('hide');
                Swal.fire({
                    title: 'Book Returned!',
                    text: response.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                loansTable.ajax.reload();
                
                // Refresh dashboard stats
                if (typeof fetchLoanStats === 'function') {
                    fetchLoanStats();
                }
                
                // Refresh trend chart
                if (typeof fetchLoanTrends === 'function') {
                    fetchLoanTrends();
                }
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Failed to return book.';
            Swal.fire('Error', message, 'error');
        }
    });
}
</script>
@endpush

