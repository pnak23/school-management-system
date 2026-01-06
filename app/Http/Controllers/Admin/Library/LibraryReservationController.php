<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryReservation;
use App\Models\LibraryItem;
use App\Models\LibraryCopy;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class LibraryReservationController extends Controller
{
    use LogsActivity;
    /**
     * Permission helper methods
     */
    private function canRead()
    {
        $user = Auth::user();
        return $user->hasAnyRole(['admin', 'manager', 'staff', 'principal']);
    }

    private function canWrite()
    {
        $user = Auth::user();
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    private function canDelete()
    {
        return Auth::user()->hasRole('admin');
    }

    private function canForceDelete()
    {
        return Auth::user()->hasRole('admin');
    }

    /**
     * Display listing (View + Ajax DataTables)
     */
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $query = LibraryReservation::with(['user', 'libraryItem', 'assignedCopy'])
                ->select('library_reservations.*');

            // Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                if ($request->status === 'expired') {
                    $query->where('status', 'ready')
                        ->where('expires_at', '<', Carbon::now());
                } else {
                    $query->where('status', $request->status);
                }
            }

            // Filter by item
            if ($request->filled('library_item_id') && $request->library_item_id !== 'all') {
                $query->where('library_item_id', $request->library_item_id);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->where('reserved_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('reserved_at', '<=', $request->date_to . ' 23:59:59');
            }

            // Filter by user (for user role)
            if (Auth::user()->hasRole('user')) {
                $query->where('user_id', Auth::id());
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            })
                            ->orWhereHas('libraryItem', function ($itemQuery) use ($search) {
                                $itemQuery->where('title', 'like', "%{$search}%");
                            })
                            ->orWhereHas('assignedCopy', function ($copyQuery) use ($search) {
                                $copyQuery->where('barcode', 'like', "%{$search}%");
                            });
                        });
                    }
                })
                ->editColumn('reserved_at', function ($reservation) {
                    return $reservation->reserved_at ? $reservation->reserved_at->format('Y-m-d H:i') : '-';
                })
                ->editColumn('expires_at', function ($reservation) {
                    if ($reservation->expires_at) {
                        $expiresAt = $reservation->expires_at->format('Y-m-d H:i');
                        $isExpired = $reservation->isExpired();
                        if ($isExpired) {
                            return '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' . $expiresAt . '</span>';
                        }
                        return $expiresAt;
                    }
                    return '-';
                })
                ->addColumn('queue_no', function ($reservation) {
                    if ($reservation->status === 'pending' && $reservation->queue_no) {
                        return '<span class="badge bg-warning text-dark">#' . $reservation->queue_no . '</span>';
                    }
                    return '-';
                })
                ->addColumn('user_name', function ($reservation) {
                    if ($reservation->user) {
                        $name = e($reservation->user->name);
                        $email = '<br><small class="text-muted">' . e($reservation->user->email) . '</small>';
                        return $name . $email;
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('book_title', function ($reservation) {
                    if ($reservation->libraryItem) {
                        return e($reservation->libraryItem->title);
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('copy_barcode', function ($reservation) {
                    if ($reservation->assignedCopy) {
                        return '<span class="badge bg-info">' . e($reservation->assignedCopy->barcode) . '</span>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('status_badge', function ($reservation) {
                    $status = $reservation->status;
                    $badges = [
                        'pending' => 'warning',
                        'ready' => 'info',
                        'fulfilled' => 'success',
                        'cancelled' => 'secondary'
                    ];
                    $color = $badges[$status] ?? 'secondary';
                    $text = ucfirst($status);
                    
                    if ($status === 'ready' && $reservation->isExpired()) {
                        $color = 'danger';
                        $text = 'Expired';
                    }
                    
                    return '<span class="badge bg-' . $color . '">' . $text . '</span>';
                })
                ->addColumn('actions', function ($reservation) {
                    $actions = '<div class="btn-group btn-group-sm" role="group">';
                    
                    // View
                    $actions .= '<button type="button" class="btn btn-info" onclick="viewReservation(' . $reservation->id . ')" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>';
                    
                    // Assign Copy (only if pending and staff has permission)
                    if ($reservation->status === 'pending' && $this->canWrite()) {
                        $actions .= '<button type="button" class="btn btn-primary" onclick="openAssignCopyModal(' . $reservation->id . ')" title="Assign Copy">
                                        <i class="fas fa-check-circle"></i>
                                    </button>';
                    }
                    
                    // Fulfill (mark as fulfilled - only if ready and staff has permission)
                    if ($reservation->status === 'ready' && $this->canWrite()) {
                        $actions .= '<button type="button" class="btn btn-success" onclick="fulfillReservation(' . $reservation->id . ')" title="Mark as Fulfilled">
                                        <i class="fas fa-check"></i>
                                    </button>';
                    }
                    
                    // Cancel (user can cancel own pending/ready, staff can cancel any)
                    $canCancel = false;
                    if (Auth::user()->hasRole('user') && $reservation->user_id === Auth::id() && in_array($reservation->status, ['pending', 'ready'])) {
                        $canCancel = true;
                    }
                    if ($this->canWrite() && in_array($reservation->status, ['pending', 'ready'])) {
                        $canCancel = true;
                    }
                    
                    if ($canCancel) {
                        $actions .= '<button type="button" class="btn btn-warning" onclick="cancelReservation(' . $reservation->id . ')" title="Cancel">
                                        <i class="fas fa-times"></i>
                                    </button>';
                    }
                    
                    // Delete (admin only)
                    if ($this->canDelete()) {
                        $actions .= '<button type="button" class="btn btn-danger" onclick="deleteReservation(' . $reservation->id . ')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>';
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['queue_no', 'user_name', 'book_title', 'copy_barcode', 'status_badge', 'expires_at', 'actions'])
                ->make(true);
        }

        // Get data for filters
        $libraryItems = LibraryItem::where('is_active', 1)
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return view('admin.library.reservations.index', compact('libraryItems'));
    }

    /**
     * Store new reservation
     */
    public function store(Request $request)
    {
        if (!$this->canWrite() && !Auth::user()->hasRole('user')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'library_item_id' => 'required|exists:library_items,id',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Check if user already has an active reservation for this item
            $existingReservation = LibraryReservation::where('user_id', $validated['user_id'])
                ->where('library_item_id', $validated['library_item_id'])
                ->whereIn('status', ['pending', 'ready'])
                ->first();

            if ($existingReservation) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active reservation for this book.'
                ], 400);
            }

            // Get next queue number
            $queueNo = LibraryReservation::getNextQueueNumber($validated['library_item_id']);

            $reservation = LibraryReservation::create([
                'user_id' => $validated['user_id'],
                'library_item_id' => $validated['library_item_id'],
                'queue_no' => $queueNo,
                'status' => 'pending',
                'reserved_at' => Carbon::now(),
                'note' => $validated['note'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'is_active' => 1,
            ]);

            // Load relationships for logging
            $reservation->load(['user', 'libraryItem']);

            // Log activity
            $user = $reservation->user;
            $book = $reservation->libraryItem;
            $this->logActivity(
                "Created reservation: {$user->name} ({$user->email}) - '{$book->title}' (Queue #{$queueNo})",
                $reservation,
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'library_item_id' => $book->id,
                    'book_title' => $book->title,
                    'queue_no' => $queueNo,
                    'status' => 'pending',
                    'reserved_at' => $reservation->reserved_at->format('Y-m-d H:i:s'),
                    'note' => $reservation->note,
                ],
                'library_reservations'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reservation created successfully! Queue number: #' . $queueNo,
                'data' => $reservation
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating reservation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show reservation details
     */
    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $reservation = LibraryReservation::with(['user', 'libraryItem', 'assignedCopy.item', 'creator', 'updater'])
            ->findOrFail($id);

        // Check if user can view this reservation
        if (Auth::user()->hasRole('user') && $reservation->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this reservation.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $reservation->id,
                'user_id' => $reservation->user_id,
                'library_item_id' => $reservation->library_item_id,
                'user_name' => $reservation->user ? $reservation->user->name : 'N/A',
                'user_email' => $reservation->user ? $reservation->user->email : 'N/A',
                'book_title' => $reservation->libraryItem ? $reservation->libraryItem->title : 'N/A',
                'assigned_copy_barcode' => $reservation->assignedCopy ? $reservation->assignedCopy->barcode : null,
                'queue_no' => $reservation->queue_no,
                'status' => $reservation->status,
                'reserved_at' => $reservation->reserved_at ? $reservation->reserved_at->format('Y-m-d H:i:s') : null,
                'expires_at' => $reservation->expires_at ? $reservation->expires_at->format('Y-m-d H:i:s') : null,
                'is_expired' => $reservation->isExpired(),
                'note' => $reservation->note,
                'created_by' => $reservation->creator ? $reservation->creator->name : 'System',
                'updated_by' => $reservation->updater ? $reservation->updater->name : 'System',
            ]
        ]);
    }

    /**
     * Update reservation (assign copy, change status, etc.)
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $reservation = LibraryReservation::findOrFail($id);

        // Prevent updates to fulfilled reservations
        if ($reservation->status === 'fulfilled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update fulfilled reservations.'
            ], 400);
        }

        $validated = $request->validate([
            'assigned_copy_id' => 'nullable|exists:library_copies,id',
            'status' => 'nullable|in:pending,ready,fulfilled,cancelled',
            'expires_at' => 'nullable|date',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Load relationships and get old values for logging
            $reservation->load(['user', 'libraryItem', 'assignedCopy']);
            $oldAttributes = [
                'status' => $reservation->status,
                'assigned_copy_id' => $reservation->assigned_copy_id,
                'expires_at' => $reservation->expires_at ? $reservation->expires_at->format('Y-m-d H:i:s') : null,
                'note' => $reservation->note,
            ];

            $updateData = [
                'updated_by' => Auth::id(),
            ];

            if (isset($validated['assigned_copy_id'])) {
                // Check if copy is already assigned to another active reservation
                $existingAssignment = LibraryReservation::where('assigned_copy_id', $validated['assigned_copy_id'])
                    ->where('status', 'ready')
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingAssignment) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'This copy is already assigned to another reservation.'
                    ], 400);
                }

                $updateData['assigned_copy_id'] = $validated['assigned_copy_id'];
            }

            if (isset($validated['status'])) {
                $updateData['status'] = $validated['status'];
            }

            if (isset($validated['expires_at'])) {
                $updateData['expires_at'] = $validated['expires_at'];
            }

            if (isset($validated['note'])) {
                $updateData['note'] = $validated['note'];
            }

            $reservation->update($updateData);
            $reservation->refresh();

            // Log activity
            $user = $reservation->user;
            $book = $reservation->libraryItem;
            $newAttributes = [
                'status' => $reservation->status,
                'assigned_copy_id' => $reservation->assigned_copy_id,
                'expires_at' => $reservation->expires_at ? $reservation->expires_at->format('Y-m-d H:i:s') : null,
                'note' => $reservation->note,
            ];
            $this->logActivityUpdate(
                "Updated reservation: {$user->name} ({$user->email}) - '{$book->title}' (ID: {$reservation->id})",
                $reservation,
                $oldAttributes,
                $newAttributes,
                'library_reservations'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reservation updated successfully!',
                'data' => $reservation
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating reservation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign copy to reservation
     */
    public function assignCopy(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $reservation = LibraryReservation::findOrFail($id);

        if ($reservation->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only assign copy to pending reservations.'
            ], 400);
        }

        $validated = $request->validate([
            'assigned_copy_id' => 'required|exists:library_copies,id',
            'expires_in_days' => 'nullable|integer|min:1|max:14',
        ]);

        try {
            DB::beginTransaction();

            $copy = LibraryCopy::findOrFail($validated['assigned_copy_id']);

            // Verify copy belongs to the same item
            if ($copy->library_item_id !== $reservation->library_item_id) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Copy does not belong to the reserved book.'
                ], 400);
            }

            // Check copy availability (not borrowed)
            if ($copy->status !== 'available') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Copy is not available for assignment.'
                ], 400);
            }

            // Check if copy is already assigned to another active reservation
            $existingAssignment = LibraryReservation::where('assigned_copy_id', $validated['assigned_copy_id'])
                ->where('status', 'ready')
                ->where('id', '!=', $id)
                ->first();

            if ($existingAssignment) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This copy is already assigned to another reservation.'
                ], 400);
            }

            $expiresInDays = isset($validated['expires_in_days']) ? (int) $validated['expires_in_days'] : 2;

            // Load relationships before update
            $reservation->load(['user', 'libraryItem']);

            $reservation->assignCopy($validated['assigned_copy_id'], $expiresInDays);
            $reservation->refresh()->load(['assignedCopy']);

            // Log activity
            $user = $reservation->user;
            $book = $reservation->libraryItem;
            $copyBarcode = $reservation->assignedCopy ? $reservation->assignedCopy->barcode : 'N/A';
            $this->logActivity(
                "Assigned copy to reservation: {$user->name} ({$user->email}) - '{$book->title}' (Copy: {$copyBarcode}, Expires in {$expiresInDays} days)",
                $reservation,
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'library_item_id' => $book->id,
                    'book_title' => $book->title,
                    'assigned_copy_id' => $validated['assigned_copy_id'],
                    'copy_barcode' => $copyBarcode,
                    'expires_in_days' => $expiresInDays,
                    'status' => $reservation->status,
                    'expires_at' => $reservation->expires_at ? $reservation->expires_at->format('Y-m-d H:i:s') : null,
                ],
                'library_reservations'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Copy assigned successfully! Reservation is now ready.',
                'data' => $reservation->fresh(['assignedCopy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning copy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign copy: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fulfill reservation (mark as fulfilled)
     */
    public function fulfill($id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $reservation = LibraryReservation::findOrFail($id);

        if ($reservation->status !== 'ready') {
            return response()->json([
                'success' => false,
                'message' => 'Can only fulfill ready reservations.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Load relationships before update
            $reservation->load(['user', 'libraryItem', 'assignedCopy']);

            $reservation->markAsFulfilled();
            $reservation->refresh();

            // Log activity
            $user = $reservation->user;
            $book = $reservation->libraryItem;
            $copyBarcode = $reservation->assignedCopy ? $reservation->assignedCopy->barcode : 'N/A';
            $this->logActivity(
                "Fulfilled reservation: {$user->name} ({$user->email}) - '{$book->title}' (Copy: {$copyBarcode})",
                $reservation,
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'library_item_id' => $book->id,
                    'book_title' => $book->title,
                    'assigned_copy_id' => $reservation->assigned_copy_id,
                    'copy_barcode' => $copyBarcode,
                    'status' => $reservation->status,
                ],
                'library_reservations'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reservation fulfilled successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error fulfilling reservation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fulfill reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel reservation
     */
    public function cancel(Request $request, $id)
    {
        $reservation = LibraryReservation::findOrFail($id);

        // Check permissions
        $canCancel = false;
        if (Auth::user()->hasRole('user') && $reservation->user_id === Auth::id() && in_array($reservation->status, ['pending', 'ready'])) {
            $canCancel = true;
        }
        if ($this->canWrite() && in_array($reservation->status, ['pending', 'ready'])) {
            $canCancel = true;
        }

        if (!$canCancel) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel this reservation.'
            ], 403);
        }

        $validated = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Load relationships before update
            $reservation->load(['user', 'libraryItem', 'assignedCopy']);

            $note = $validated['note'] ?? 'Cancelled by user';
            $oldStatus = $reservation->status;
            $reservation->markAsCancelled($note);
            $reservation->refresh();

            // Log activity
            $user = $reservation->user;
            $book = $reservation->libraryItem;
            $copyBarcode = $reservation->assignedCopy ? $reservation->assignedCopy->barcode : 'N/A';
            $this->logActivity(
                "Cancelled reservation: {$user->name} ({$user->email}) - '{$book->title}' (Copy: {$copyBarcode})",
                $reservation,
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'library_item_id' => $book->id,
                    'book_title' => $book->title,
                    'assigned_copy_id' => $reservation->assigned_copy_id,
                    'copy_barcode' => $copyBarcode,
                    'old_status' => $oldStatus,
                    'new_status' => $reservation->status,
                    'cancellation_note' => $note,
                ],
                'library_reservations'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reservation cancelled successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling reservation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete reservation (admin only) - marks as inactive
     */
    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            $reservation = LibraryReservation::findOrFail($id);
            $reservation->load(['user', 'libraryItem', 'assignedCopy']);
            
            $reservation->update(['is_active' => false, 'updated_by' => Auth::id()]);

            // Log activity
            $user = $reservation->user;
            $book = $reservation->libraryItem;
            $copyBarcode = $reservation->assignedCopy ? $reservation->assignedCopy->barcode : 'N/A';
            $this->logActivity(
                "Deleted reservation: {$user->name} ({$user->email}) - '{$book->title}' (Copy: {$copyBarcode}, ID: {$reservation->id})",
                $reservation,
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'library_item_id' => $book->id,
                    'book_title' => $book->title,
                    'assigned_copy_id' => $reservation->assigned_copy_id,
                    'copy_barcode' => $copyBarcode,
                    'status' => $reservation->status,
                    'queue_no' => $reservation->queue_no,
                ],
                'library_reservations'
            );

            return response()->json([
                'success' => true,
                'message' => 'Reservation deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting reservation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete reservation (admin only)
     */
    public function forceDelete($id)
    {
        if (!$this->canForceDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            $reservation = LibraryReservation::findOrFail($id);
            $reservation->load(['user', 'libraryItem', 'assignedCopy']);
            
            // Log activity before deletion
            $user = $reservation->user;
            $book = $reservation->libraryItem;
            $copyBarcode = $reservation->assignedCopy ? $reservation->assignedCopy->barcode : 'N/A';
            $this->logActivity(
                "Permanently deleted reservation: {$user->name} ({$user->email}) - '{$book->title}' (Copy: {$copyBarcode}, ID: {$reservation->id})",
                null,
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'library_item_id' => $book->id,
                    'book_title' => $book->title,
                    'assigned_copy_id' => $reservation->assigned_copy_id,
                    'copy_barcode' => $copyBarcode,
                    'status' => $reservation->status,
                    'queue_no' => $reservation->queue_no,
                    'reserved_at' => $reservation->reserved_at ? $reservation->reserved_at->format('Y-m-d H:i:s') : null,
                ],
                'library_reservations'
            );

            $reservation->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Reservation permanently deleted!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error force deleting reservation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available copies for a specific library item
     */
    public function getAvailableCopies($itemId)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            // Verify item exists
            $item = LibraryItem::findOrFail($itemId);

            $copies = LibraryCopy::with('shelf')
                ->where('library_item_id', $itemId)
                ->where('status', 'available')
                ->where('is_active', 1)
                ->select('id', 'barcode', 'call_number', 'shelf_id', 'condition', 'status')
                ->orderBy('barcode')
                ->get()
                ->map(function ($copy) {
                    return [
                        'id' => $copy->id,
                        'barcode' => $copy->barcode,
                        'call_number' => $copy->call_number,
                        'location' => $copy->shelf ? $copy->shelf->location : 'N/A',
                        'shelf_code' => $copy->shelf ? $copy->shelf->shelf_code : 'N/A',
                        'condition' => $copy->condition,
                        'status' => $copy->status,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $copies,
                'count' => $copies->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading available copies: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load available copies.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-cancel expired reservations (can be called by scheduler)
     */
    public function autoCancelExpiredReservations()
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            $count = LibraryReservation::cancelExpiredReservations();

            return response()->json([
                'success' => true,
                'message' => "Auto-cancelled {$count} expired reservation(s)."
            ]);

        } catch (\Exception $e) {
            Log::error('Error auto-cancelling expired reservations: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-cancel expired reservations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users for reservation form (AJAX Select2)
     */
    public function searchUsers(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $query = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        try {
            $usersQuery = User::where('is_active', 1);

            // Filter by search term
            if (!empty($query)) {
                $usersQuery->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                });
            }

            // Paginate results
            $users = $usersQuery->orderBy('name')
                ->skip(($page - 1) * $perPage)
                ->take($perPage + 1) // Take one extra to check if there are more
                ->get();

            // Check if there are more results
            $hasMore = $users->count() > $perPage;
            if ($hasMore) {
                $users = $users->take($perPage);
            }

            // Format results for Select2
            $results = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->name . ' (' . $user->email . ')',
                    'name' => $user->name,
                    'email' => $user->email
                ];
            });

            return response()->json([
                'success' => true,
                'results' => $results,
                'pagination' => [
                    'more' => $hasMore
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search users.',
                'results' => []
            ], 500);
        }
    }
}

