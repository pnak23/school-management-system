<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryFine;
use App\Models\LibraryLoan;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class LibraryFineController extends Controller
{
    use LogsActivity;
    /**
     * Permission check: can read
     */
    private function canRead()
    {
        $user = Auth::user();
        return $user->hasAnyRole(['admin', 'manager', 'staff', 'principal']);
    }

    /**
     * Permission check: can write (create/update)
     */
    private function canWrite()
    {
        $user = Auth::user();
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Permission check: can delete (soft delete)
     */
    private function canDelete()
    {
        $user = Auth::user();
        return $user->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Permission check: can force delete (permanent)
     */
    private function canForceDelete()
    {
        return Auth::user()->hasRole('admin');
    }

    /**
     * Display fines list / return DataTables JSON
     */
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Return view for non-Ajax requests
        if (!$request->ajax()) {
            $loans = LibraryLoan::with(['copy.item'])
                ->orderBy('id', 'desc')
                ->limit(100)
                ->get();
            
            $users = User::select('id', 'name', 'email')
                ->where('is_active', true)
                ->orderBy('name')
                ->limit(100)
                ->get();

            return view('admin.library.fines.index', compact('loans', 'users'));
        }

        // DataTables server-side processing
        $query = LibraryFine::with(['loan.copy.item', 'user', 'creator', 'updater']);

        // Apply filters
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('fine_type') && $request->fine_type !== 'all') {
            $query->where('fine_type', $request->fine_type);
        }

        if ($request->filled('assessed_from')) {
            $query->whereDate('assessed_at', '>=', $request->assessed_from);
        }

        if ($request->filled('assessed_to')) {
            $query->whereDate('assessed_at', '<=', $request->assessed_to);
        }

        if ($request->filled('is_active')) {
            if ($request->is_active === 'active') {
                $query->where('is_active', true);
            } elseif ($request->is_active === 'inactive') {
                $query->where('is_active', false);
            }
        } else {
            // Default: active only
            $query->where('is_active', true);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function($q) use ($search) {
                        $q->where('fine_type', 'like', "%{$search}%")
                          ->orWhere('note', 'like', "%{$search}%")
                          ->orWhere('amount', 'like', "%{$search}%")
                          ->orWhereHas('user', function($q) use ($search) {
                              $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                          })
                          ->orWhereHas('loan', function($q) use ($search) {
                              $q->where('id', 'like', "%{$search}%");
                          });
                    });
                }
            })
            ->addColumn('loan_info', function ($fine) {
                if ($fine->loan) {
                    $loanId = '#' . $fine->loan->id;
                    $bookTitle = $fine->book_title;
                    $borrower = $fine->borrower_name;
                    
                    return '<div>' .
                           '<strong>' . $loanId . '</strong><br>' .
                           '<small class="text-muted">' . $bookTitle . '</small><br>' .
                           '<small><i class="fas fa-user"></i> ' . $borrower . '</small>' .
                           '</div>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('user_info', function ($fine) {
                if ($fine->user) {
                    return '<div>' .
                           '<strong>' . $fine->user->name . '</strong><br>' .
                           '<small class="text-muted">' . $fine->user->email . '</small>' .
                           '</div>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('fine_type_badge', function ($fine) {
                $colors = [
                    'overdue' => 'warning',
                    'lost' => 'danger',
                    'damaged' => 'orange',
                    'other' => 'secondary'
                ];
                $color = $colors[$fine->fine_type] ?? 'info';
                return '<span class="badge bg-' . $color . '">' . ucfirst($fine->fine_type) . '</span>';
            })
            ->addColumn('amount_display', function ($fine) {
                return number_format($fine->amount, 0) . ' ៛';
            })
            ->addColumn('paid_display', function ($fine) {
                return number_format($fine->paid_amount, 0) . ' ៛';
            })
            ->addColumn('balance_display', function ($fine) {
                $balance = $fine->balance;
                $color = $balance > 0 ? 'danger' : 'success';
                return '<strong class="text-' . $color . '">' . number_format($balance, 0) . ' ៛</strong>';
            })
            ->addColumn('status_badge', function ($fine) {
                $colors = [
                    'paid' => 'success',
                    'waived' => 'info',
                    'unpaid' => 'danger'
                ];
                $color = $colors[$fine->status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($fine->status) . '</span>';
            })
            ->editColumn('assessed_at', function ($fine) {
                return $fine->assessed_at ? $fine->assessed_at->format('Y-m-d H:i') : '-';
            })
            ->editColumn('paid_at', function ($fine) {
                return $fine->paid_at ? $fine->paid_at->format('Y-m-d H:i') : '-';
            })
            ->addColumn('active_badge', function ($fine) {
                if ($fine->is_active) {
                    return '<span class="badge bg-success">Active</span>';
                } else {
                    return '<span class="badge bg-secondary">Inactive</span>';
                }
            })
            ->addColumn('actions', function ($fine) {
                $canWrite = $this->canWrite();
                $canDelete = $this->canDelete();
                $isAdmin = $this->canForceDelete();

                $actions = '<div class="btn-group btn-group-sm" role="group">';
                
                // View button
                $actions .= '<button type="button" class="btn btn-info" onclick="viewFine(' . $fine->id . ')" title="View">
                    <i class="fas fa-eye"></i>
                </button>';
                
                // Edit button (admin/manager/staff)
                if ($canWrite) {
                    $actions .= '<button type="button" class="btn btn-primary" onclick="openEditModal(' . $fine->id . ')" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>';
                }

                // Pay button (if not fully paid)
                if ($canWrite && $fine->balance > 0) {
                    $actions .= '<button type="button" class="btn btn-success" onclick="openPayModal(' . $fine->id . ')" title="Pay">
                        <i class="fas fa-dollar-sign"></i>
                    </button>';
                }

                // Toggle active button
                if ($canWrite) {
                    $actions .= '<button type="button" class="btn btn-warning" onclick="toggleFineActive(' . $fine->id . ')" title="Toggle Status">
                        <i class="fas fa-toggle-on"></i>
                    </button>';
                }

                // Delete button (admin/manager)
                if ($canDelete) {
                    $actions .= '<button type="button" class="btn btn-danger" onclick="deleteFine(' . $fine->id . ')" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
                }

                $actions .= '</div>';
                
                return $actions;
            })
            ->rawColumns(['loan_info', 'user_info', 'fine_type_badge', 'balance_display', 'status_badge', 'active_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show single fine (JSON)
     */
    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $fine = LibraryFine::with(['loan.copy.item', 'user', 'creator', 'updater'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $fine->id,
                    'loan_id' => $fine->loan_id,
                    'loan_info' => $fine->loan ? '#' . $fine->loan->id . ' - ' . $fine->book_title : null,
                    'user_id' => $fine->user_id,
                    'user_name' => $fine->user ? $fine->user->name : null,
                    'user_email' => $fine->user ? $fine->user->email : null,
                    'borrower_name' => $fine->borrower_name,
                    'book_title' => $fine->book_title,
                    'fine_type' => $fine->fine_type,
                    'amount' => $fine->amount,
                    'paid_amount' => $fine->paid_amount,
                    'balance' => $fine->balance,
                    'status' => $fine->status,
                    'is_paid' => $fine->is_paid,
                    'assessed_at' => $fine->assessed_at ? $fine->assessed_at->format('Y-m-d H:i:s') : null,
                    'paid_at' => $fine->paid_at ? $fine->paid_at->format('Y-m-d H:i:s') : null,
                    'note' => $fine->note,
                    'is_active' => $fine->is_active,
                    'created_by' => $fine->creator ? $fine->creator->name : null,
                    'updated_by' => $fine->updater ? $fine->updater->name : null,
                    'created_at' => $fine->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $fine->updated_at->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing fine: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Fine not found.'], 404);
        }
    }

    /**
     * Get dashboard statistics (JSON)
     */
    public function stats()
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $total = LibraryFine::count();
            $unpaid = LibraryFine::where('status', 'unpaid')->where('is_active', true)->count();
            $paid = LibraryFine::where('status', 'paid')->where('is_active', true)->count();
            $waived = LibraryFine::where('status', 'waived')->where('is_active', true)->count();
            
            // Calculate total amounts
            $totalAmount = LibraryFine::where('is_active', true)->sum('amount');
            $totalPaid = LibraryFine::where('is_active', true)->sum('paid_amount');
            $totalBalance = $totalAmount - $totalPaid;
            $unpaidAmount = LibraryFine::where('status', 'unpaid')
                ->where('is_active', true)
                ->selectRaw('SUM(amount - paid_amount) as balance')
                ->value('balance') ?? 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'unpaid' => $unpaid,
                    'paid' => $paid,
                    'waived' => $waived,
                    'total_amount' => $totalAmount,
                    'total_paid' => $totalPaid,
                    'total_balance' => max(0, $totalBalance),
                    'unpaid_amount' => max(0, $unpaidAmount),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching fines stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.'
            ], 500);
        }
    }

    /**
     * Store new fine
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'loan_id' => 'required|exists:library_loans,id',
            'user_id' => 'required|exists:users,id',
            'fine_type' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:unpaid,paid,waived',
            'assessed_at' => 'nullable|date',
            'paid_at' => 'nullable|date',
            'note' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Defaults
            $validated['assessed_at'] = $validated['assessed_at'] ?? now();
            $validated['paid_amount'] = $validated['paid_amount'] ?? 0;
            $validated['created_by'] = Auth::id();
            $validated['is_active'] = true;

            // Normalize status and paid_at
            $this->normalizePaymentStatus($validated);

            $fine = LibraryFine::create($validated);
            $fine->load(['user', 'loan.copy.item']);

            // Log activity
            $user = $fine->user;
            $userName = $user ? $user->name : 'N/A';
            $userEmail = $user ? $user->email : 'N/A';
            $bookTitle = $fine->book_title ?? 'N/A';
            $this->logActivity(
                "Created fine: {$userName} ({$userEmail}) - '{$bookTitle}' - {$fine->fine_type} - Amount: " . number_format($fine->amount, 0) . ' ៛',
                $fine,
                [
                    'fine_id' => $fine->id,
                    'loan_id' => $fine->loan_id,
                    'user_id' => $fine->user_id,
                    'user_name' => $userName,
                    'user_email' => $userEmail,
                    'borrower_name' => $fine->borrower_name,
                    'book_title' => $bookTitle,
                    'fine_type' => $fine->fine_type,
                    'amount' => $fine->amount,
                    'paid_amount' => $fine->paid_amount,
                    'balance' => $fine->balance,
                    'status' => $fine->status,
                    'assessed_at' => $fine->assessed_at ? $fine->assessed_at->format('Y-m-d H:i:s') : null,
                    'note' => $fine->note,
                ],
                'library_fines'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fine created successfully.',
                'data' => $fine
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating fine: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create fine.'], 500);
        }
    }

    /**
     * Update fine
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'loan_id' => 'required|exists:library_loans,id',
            'user_id' => 'required|exists:users,id',
            'fine_type' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:unpaid,paid,waived',
            'assessed_at' => 'nullable|date',
            'paid_at' => 'nullable|date',
            'note' => 'nullable|string|max:1000'
        ]);

        try {
            $fine = LibraryFine::findOrFail($id);
            $fine->load(['user', 'loan.copy.item']);

            DB::beginTransaction();

            // Get old values for logging
            $oldAttributes = [
                'loan_id' => $fine->loan_id,
                'user_id' => $fine->user_id,
                'fine_type' => $fine->fine_type,
                'amount' => $fine->amount,
                'paid_amount' => $fine->paid_amount,
                'balance' => $fine->balance,
                'status' => $fine->status,
                'assessed_at' => $fine->assessed_at ? $fine->assessed_at->format('Y-m-d H:i:s') : null,
                'paid_at' => $fine->paid_at ? $fine->paid_at->format('Y-m-d H:i:s') : null,
                'note' => $fine->note,
            ];

            $validated['updated_by'] = Auth::id();
            $validated['paid_amount'] = $validated['paid_amount'] ?? 0;

            // Normalize status and paid_at
            $this->normalizePaymentStatus($validated);

            $fine->update($validated);
            $fine->refresh();

            // Log activity
            $user = $fine->user;
            $userName = $user ? $user->name : 'N/A';
            $userEmail = $user ? $user->email : 'N/A';
            $bookTitle = $fine->book_title ?? 'N/A';
            $newAttributes = [
                'loan_id' => $fine->loan_id,
                'user_id' => $fine->user_id,
                'fine_type' => $fine->fine_type,
                'amount' => $fine->amount,
                'paid_amount' => $fine->paid_amount,
                'balance' => $fine->balance,
                'status' => $fine->status,
                'assessed_at' => $fine->assessed_at ? $fine->assessed_at->format('Y-m-d H:i:s') : null,
                'paid_at' => $fine->paid_at ? $fine->paid_at->format('Y-m-d H:i:s') : null,
                'note' => $fine->note,
            ];
            $this->logActivityUpdate(
                "Updated fine: {$userName} ({$userEmail}) - '{$bookTitle}' - {$fine->fine_type} (ID: {$fine->id})",
                $fine,
                $oldAttributes,
                $newAttributes,
                'library_fines'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fine updated successfully.',
                'data' => $fine
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating fine: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update fine.'], 500);
        }
    }

    /**
     * Soft delete fine
     */
    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $fine = LibraryFine::findOrFail($id);
            $fine->load(['user', 'loan.copy.item']);
            
            $fine->update([
                'is_active' => false,
                'updated_by' => Auth::id()
            ]);

            // Log activity
            $user = $fine->user;
            $userName = $user ? $user->name : 'N/A';
            $userEmail = $user ? $user->email : 'N/A';
            $bookTitle = $fine->book_title ?? 'N/A';
            $this->logActivity(
                "Deleted fine: {$userName} ({$userEmail}) - '{$bookTitle}' - {$fine->fine_type} - Amount: " . number_format($fine->amount, 0) . " ៛ (ID: {$fine->id})",
                $fine,
                [
                    'fine_id' => $fine->id,
                    'loan_id' => $fine->loan_id,
                    'user_id' => $fine->user_id,
                    'user_name' => $userName,
                    'user_email' => $userEmail,
                    'borrower_name' => $fine->borrower_name,
                    'book_title' => $bookTitle,
                    'fine_type' => $fine->fine_type,
                    'amount' => $fine->amount,
                    'paid_amount' => $fine->paid_amount,
                    'balance' => $fine->balance,
                    'status' => $fine->status,
                ],
                'library_fines'
            );

            return response()->json([
                'success' => true,
                'message' => 'Fine deleted (deactivated) successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting fine: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete fine.'], 500);
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $fine = LibraryFine::findOrFail($id);
            $fine->load(['user', 'loan.copy.item']);
            $oldStatus = $fine->is_active;
            
            $fine->update([
                'is_active' => !$fine->is_active,
                'updated_by' => Auth::id()
            ]);
            $fine->refresh();

            $status = $fine->is_active ? 'activated' : 'deactivated';

            // Log activity
            $user = $fine->user;
            $userName = $user ? $user->name : 'N/A';
            $userEmail = $user ? $user->email : 'N/A';
            $bookTitle = $fine->book_title ?? 'N/A';
            $this->logActivity(
                "Toggled fine status: {$userName} ({$userEmail}) - '{$bookTitle}' - {$fine->fine_type} (ID: {$fine->id}) - " . ucfirst($status),
                $fine,
                [
                    'fine_id' => $fine->id,
                    'user_id' => $fine->user_id,
                    'user_name' => $userName,
                    'user_email' => $userEmail,
                    'book_title' => $bookTitle,
                    'fine_type' => $fine->fine_type,
                    'old_is_active' => $oldStatus,
                    'new_is_active' => $fine->is_active,
                    'amount' => $fine->amount,
                    'status' => $fine->status,
                ],
                'library_fines'
            );

            return response()->json([
                'success' => true,
                'message' => "Fine {$status} successfully."
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling fine status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to toggle status.'], 500);
        }
    }

    /**
     * Permanent delete (admin only)
     */
    public function forceDelete($id)
    {
        if (!$this->canForceDelete()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Admin only.'], 403);
        }

        try {
            $fine = LibraryFine::findOrFail($id);
            $fine->load(['user', 'loan.copy.item']);
            
            // Log activity before deletion
            $user = $fine->user;
            $userName = $user ? $user->name : 'N/A';
            $userEmail = $user ? $user->email : 'N/A';
            $bookTitle = $fine->book_title ?? 'N/A';
            $fineId = $fine->id;
            
            $this->logActivity(
                "Permanently deleted fine: {$userName} ({$userEmail}) - '{$bookTitle}' - {$fine->fine_type} - Amount: " . number_format($fine->amount, 0) . " ៛ (ID: {$fineId})",
                null,
                [
                    'fine_id' => $fineId,
                    'loan_id' => $fine->loan_id,
                    'user_id' => $fine->user_id,
                    'user_name' => $userName,
                    'user_email' => $userEmail,
                    'borrower_name' => $fine->borrower_name,
                    'book_title' => $bookTitle,
                    'fine_type' => $fine->fine_type,
                    'amount' => $fine->amount,
                    'paid_amount' => $fine->paid_amount,
                    'balance' => $fine->balance,
                    'status' => $fine->status,
                    'assessed_at' => $fine->assessed_at ? $fine->assessed_at->format('Y-m-d H:i:s') : null,
                    'paid_at' => $fine->paid_at ? $fine->paid_at->format('Y-m-d H:i:s') : null,
                ],
                'library_fines'
            );

            $fine->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fine permanently deleted.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error force deleting fine: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete fine.'], 500);
        }
    }

    /**
     * Process payment
     */
    public function pay(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'pay_amount' => 'required|numeric|min:0.01',
            'payment_note' => 'nullable|string|max:500'
        ]);

        try {
            $fine = LibraryFine::findOrFail($id);

            DB::beginTransaction();

            // Add payment but cap at total amount
            $newPaidAmount = $fine->paid_amount + $validated['pay_amount'];
            if ($newPaidAmount > $fine->amount) {
                $newPaidAmount = $fine->amount;
            }

            $data = [
                'paid_amount' => $newPaidAmount,
                'updated_by' => Auth::id()
            ];

            // Determine new status
            if ($newPaidAmount <= 0) {
                $data['status'] = 'unpaid';
                $data['paid_at'] = null;
            } elseif ($newPaidAmount < $fine->amount) {
                // Database doesn't have 'partial' status - keep as 'unpaid'
                $data['status'] = 'unpaid';
            } else {
                $data['status'] = 'paid';
                $data['paid_at'] = $data['paid_at'] ?? now();
            }

            // Append payment note if provided
            if (!empty($validated['payment_note'])) {
                $existingNote = $fine->note ?? '';
                $paymentNote = "\n[PAYMENT " . now()->format('Y-m-d H:i') . '] ' . $validated['payment_note'];
                $data['note'] = $existingNote . $paymentNote;
            }

            // Load relationships before update
            $fine->load(['user', 'loan.copy.item']);
            $oldPaidAmount = $fine->paid_amount;
            $oldBalance = $fine->balance;
            $oldStatus = $fine->status;

            $fine->update($data);
            $fine->refresh();

            // Log activity
            $user = $fine->user;
            $userName = $user ? $user->name : 'N/A';
            $userEmail = $user ? $user->email : 'N/A';
            $bookTitle = $fine->book_title ?? 'N/A';
            $this->logActivity(
                "Payment processed for fine: {$userName} ({$userEmail}) - '{$bookTitle}' - {$fine->fine_type} - Payment: " . number_format($validated['pay_amount'], 0) . ' ៛ (ID: ' . $fine->id . ')',
                $fine,
                [
                    'fine_id' => $fine->id,
                    'loan_id' => $fine->loan_id,
                    'user_id' => $fine->user_id,
                    'user_name' => $userName,
                    'user_email' => $userEmail,
                    'book_title' => $bookTitle,
                    'fine_type' => $fine->fine_type,
                    'payment_amount' => $validated['pay_amount'],
                    'old_paid_amount' => $oldPaidAmount,
                    'new_paid_amount' => $fine->paid_amount,
                    'old_balance' => $oldBalance,
                    'new_balance' => $fine->balance,
                    'old_status' => $oldStatus,
                    'new_status' => $fine->status,
                    'payment_note' => $validated['payment_note'] ?? null,
                ],
                'library_fines'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully.',
                'data' => [
                    'paid_amount' => $fine->paid_amount,
                    'balance' => $fine->balance,
                    'status' => $fine->status
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to process payment.'], 500);
        }
    }

    /**
     * Waive fine
     */
    public function waive(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'waive_reason' => 'nullable|string|max:500'
        ]);

        try {
            $fine = LibraryFine::findOrFail($id);
            $fine->load(['user', 'loan.copy.item']);

            DB::beginTransaction();

            // Get old values for logging
            $oldAmount = $fine->amount;
            $oldBalance = $fine->balance;
            $oldStatus = $fine->status;

            // Implement waiver by setting amount = paid_amount (balance becomes 0)
            $data = [
                'amount' => $fine->paid_amount, // Adjust amount down to match paid
                'status' => 'paid',
                'paid_at' => now(),
                'updated_by' => Auth::id()
            ];

            // Append waiver note
            $existingNote = $fine->note ?? '';
            $waiveReason = $validated['waive_reason'] ?? 'No reason provided';
            $waiveNote = "\n[WAIVED " . now()->format('Y-m-d H:i') . '] ' . $waiveReason;
            $data['note'] = $existingNote . $waiveNote;

            $fine->update($data);
            $fine->refresh();

            // Log activity
            $user = $fine->user;
            $userName = $user ? $user->name : 'N/A';
            $userEmail = $user ? $user->email : 'N/A';
            $bookTitle = $fine->book_title ?? 'N/A';
            $this->logActivity(
                "Waived fine: {$userName} ({$userEmail}) - '{$bookTitle}' - {$fine->fine_type} - Waived Amount: " . number_format($oldBalance, 0) . " ៛ (ID: {$fine->id})",
                $fine,
                [
                    'fine_id' => $fine->id,
                    'loan_id' => $fine->loan_id,
                    'user_id' => $fine->user_id,
                    'user_name' => $userName,
                    'user_email' => $userEmail,
                    'book_title' => $bookTitle,
                    'fine_type' => $fine->fine_type,
                    'old_amount' => $oldAmount,
                    'new_amount' => $fine->amount,
                    'old_balance' => $oldBalance,
                    'new_balance' => $fine->balance,
                    'old_status' => $oldStatus,
                    'new_status' => $fine->status,
                    'waive_reason' => $waiveReason,
                    'waived_at' => now()->format('Y-m-d H:i:s'),
                ],
                'library_fines'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fine waived successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error waiving fine: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to waive fine.'], 500);
        }
    }

    /**
     * Helper: Normalize payment status and paid_at
     */
    private function normalizePaymentStatus(&$data)
    {
        $amount = $data['amount'];
        $paidAmount = $data['paid_amount'];

        if ($paidAmount <= 0) {
            $data['status'] = 'unpaid';
            $data['paid_at'] = null;
        } elseif ($paidAmount < $amount) {
            // Database doesn't have 'partial' - keep as 'unpaid' until fully paid
            $data['status'] = 'unpaid';
            $data['paid_at'] = null;
        } else {
            // Paid in full
            $data['paid_amount'] = $amount; // Cap at amount
            $data['status'] = 'paid';
            $data['paid_at'] = $data['paid_at'] ?? now();
        }
    }
}

