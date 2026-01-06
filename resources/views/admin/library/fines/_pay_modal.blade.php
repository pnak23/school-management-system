<!-- Pay Fine Modal -->
<div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="payModalLabel">
                    <i class="fas fa-dollar-sign"></i> Process Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="payFineId">

                <!-- Fine Summary -->
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Fine Details</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th>Total Amount:</th>
                                <td class="text-end"><strong id="payTotalAmount">-</strong></td>
                            </tr>
                            <tr>
                                <th>Already Paid:</th>
                                <td class="text-end"><span id="payPaidAmount">-</span></td>
                            </tr>
                            <tr class="table-warning">
                                <th><strong>Balance Due:</strong></th>
                                <td class="text-end"><strong class="text-danger" id="payBalance">-</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Payment Amount Input -->
                <div class="mb-3">
                    <label for="pay_amount" class="form-label">Payment Amount (៛) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-lg" id="pay_amount" step="0.01" min="0.01" placeholder="Enter amount to pay" required>
                    
                    <!-- Quick Amount Buttons -->
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill" onclick="addPayAmount(1000)">
                            +1,000៛
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill" onclick="addPayAmount(2000)">
                            +2,000៛
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill" onclick="addPayAmount(5000)">
                            +5,000៛
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm flex-fill" onclick="payFullBalance()">
                            Pay Full
                        </button>
                    </div>
                </div>

                <!-- Payment Note -->
                <div class="mb-3">
                    <label for="payment_note" class="form-label">Payment Note (Optional)</label>
                    <textarea class="form-control" id="payment_note" rows="2" maxlength="500" placeholder="Add a note about this payment..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitPayment()">
                    <i class="fas fa-check"></i> Process Payment
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentBalance = 0;

// Open payment modal
function openPayModal(id) {
    // Reset form
    $('#pay_amount').val('');
    $('#payment_note').val('');
    $('#payFineId').val(id);

    // Load fine details
    $.ajax({
        url: `/admin/library/fines/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const fine = response.data;
                currentBalance = parseFloat(fine.balance);

                $('#payTotalAmount').text(Number(fine.amount).toLocaleString() + ' ៛');
                $('#payPaidAmount').text(Number(fine.paid_amount).toLocaleString() + ' ៛');
                $('#payBalance').text(Number(fine.balance).toLocaleString() + ' ៛');

                $('#payModal').modal('show');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to load fine details.', 'error');
        }
    });
}

// Add amount to payment input
function addPayAmount(amount) {
    const currentValue = parseFloat($('#pay_amount').val()) || 0;
    const newValue = currentValue + amount;
    $('#pay_amount').val(newValue);
}

// Set payment to full balance
function payFullBalance() {
    $('#pay_amount').val(currentBalance);
}

// Submit payment
function submitPayment() {
    const fineId = $('#payFineId').val();
    const payAmount = parseFloat($('#pay_amount').val());
    const paymentNote = $('#payment_note').val();

    if (!payAmount || payAmount <= 0) {
        Swal.fire('Error', 'Please enter a valid payment amount.', 'error');
        return;
    }

    // Confirmation
    Swal.fire({
        title: 'Confirm Payment',
        html: `<p>Process payment of <strong>${payAmount.toLocaleString()} ៛</strong>?</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, process payment',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/library/fines/${fineId}/pay`,
                type: 'POST',
                data: {
                    pay_amount: payAmount,
                    payment_note: paymentNote
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#payModal').modal('hide');
                        
                        Swal.fire({
                            title: 'Payment Processed',
                            html: `
                                <p><strong>Payment successful!</strong></p>
                                <table class="table table-sm">
                                    <tr><th>Paid:</th><td>${Number(response.data.paid_amount).toLocaleString()} ៛</td></tr>
                                    <tr><th>Remaining Balance:</th><td class="text-${response.data.balance > 0 ? 'danger' : 'success'}"><strong>${Number(response.data.balance).toLocaleString()} ៛</strong></td></tr>
                                    <tr><th>Status:</th><td><span class="badge bg-${response.data.status === 'paid' ? 'success' : 'danger'}">${response.data.status.toUpperCase()}</span></td></tr>
                                </table>
                            `,
                            icon: 'success'
                        });
                        
                        finesTable.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let message = 'Failed to process payment.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    Swal.fire('Error', message, 'error');
                }
            });
        }
    });
}

// Waive fine
function waiveFine(id) {
    Swal.fire({
        title: 'Waive Fine',
        html: `
            <p>Are you sure you want to <strong>waive</strong> this fine?</p>
            <p class="text-muted small">This will mark the fine as paid without requiring payment.</p>
            <textarea class="form-control mt-2" id="waiveReason" rows="2" placeholder="Reason for waiving (optional)..."></textarea>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, waive it',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ffc107',
        preConfirm: () => {
            return $('#waiveReason').val();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/library/fines/${id}/waive`,
                type: 'POST',
                data: {
                    waive_reason: result.value
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Waived', response.message, 'success');
                        finesTable.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to waive fine.', 'error');
                }
            });
        }
    });
}
</script>
@endpush

