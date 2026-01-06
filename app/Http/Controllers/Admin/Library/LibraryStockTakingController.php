<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryStockTaking;
use App\Models\Staff;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

/**
 * Library Stock Taking Controller (Header CRUD Only)
 * 
 * Manages stock taking audit sessions (header records only).
 * Does NOT handle scanning or items.
 */
class LibraryStockTakingController extends Controller
{
    use LogsActivity;
    /**
     * Check if user can write (create/update)
     */
    private function canWrite(): bool
    {
        $user = Auth::user();
        return $user && $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Check if user can delete
     */
    private function canDelete(): bool
    {
        $user = Auth::user();
        return $user && $user->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Check if user can force delete
     */
    private function canForceDelete(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('admin');
    }

    /**
     * Get current staff ID from authenticated user
     */
    private function getCurrentStaffId(): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $staff = Staff::where('user_id', $user->id)->first();
        return $staff ? $staff->id : null;
    }

    /**
     * Generate unique reference number
     * Format: STK-YYYYMMDD-0001
     */
    private function generateReferenceNo(): string
    {
        $date = Carbon::now()->format('Ymd');
        $prefix = "STK-{$date}-";

        // Find last reference number for today
        $lastRecord = LibraryStockTaking::where('reference_no', 'like', $prefix . '%')
            ->orderByDesc('reference_no')
            ->first();

        if ($lastRecord) {
            // Extract sequence number and increment
            $lastSeq = (int) substr($lastRecord->reference_no, -4);
            $newSeq = $lastSeq + 1;
        } else {
            $newSeq = 1;
        }

        return $prefix . str_pad($newSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Display listing of stock takings
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = LibraryStockTaking::with(['conductedBy.user', 'creator'])
                ->select('library_stock_takings.*');

            // Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by active status
            if ($request->filled('is_active') && $request->is_active !== 'all') {
                $query->where('is_active', $request->is_active == '1');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('reference_no', 'like', "%{$search}%")
                              ->orWhere('note', 'like', "%{$search}%");
                        });
                    }
                })
                ->editColumn('started_at', function ($item) {
                    return $item->started_at ? $item->started_at->format('Y-m-d H:i') : 'N/A';
                })
                ->editColumn('ended_at', function ($item) {
                    return $item->ended_at ? $item->ended_at->format('Y-m-d H:i') : 'N/A';
                })
                ->addColumn('status_badge', function ($item) {
                    $badges = [
                        'in_progress' => '<span class="badge bg-primary"><i class="fas fa-spinner fa-spin"></i> IN PROGRESS</span>',
                        'completed' => '<span class="badge bg-success"><i class="fas fa-check-circle"></i> COMPLETED</span>',
                        'cancelled' => '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> CANCELLED</span>',
                    ];
                    return $badges[$item->status] ?? '<span class="badge bg-secondary">' . strtoupper($item->status) . '</span>';
                })
                ->addColumn('conducted_by', function ($item) {
                    if ($item->conductedBy && $item->conductedBy->user) {
                        return e($item->conductedBy->user->name);
                    } elseif ($item->creator) {
                        return e($item->creator->name);
                    }
                    return 'N/A';
                })
                ->addColumn('active_toggle', function ($item) {
                    $checked = $item->is_active ? 'checked' : '';
                    $disabled = !Auth::user()->hasAnyRole(['admin', 'manager', 'staff']) ? 'disabled' : '';
                    return '<div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" ' . $checked . ' ' . $disabled . ' 
                                       onchange="toggleStatus(' . $item->id . ', this)">
                            </div>';
                })
                ->addColumn('actions', function ($item) {
                    $buttons = '<a href="' . route('admin.library.stock-takings.show', $item->id) . '" 
                                   class="btn btn-sm btn-info" title="View/Scan">
                                    <i class="fas fa-qrcode"></i> Scan
                                </a>';

                    if (Auth::user()->hasAnyRole(['admin', 'manager', 'staff'])) {
                        $buttons .= ' <button class="btn btn-sm btn-primary" onclick="openEditModal(' . $item->id . ')" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>';
                    }

                    if (Auth::user()->hasAnyRole(['admin', 'manager'])) {
                        $buttons .= ' <button class="btn btn-sm btn-danger" onclick="deleteStockTaking(' . $item->id . ')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>';
                    }

                    return $buttons;
                })
                ->rawColumns(['status_badge', 'active_toggle', 'actions'])
                ->make(true);
        }

        // Normal view
        return view('admin.library.stock_takings.index');
    }

    /**
     * Store a new stock taking
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create stock taking.'
            ], 403);
        }

        $validated = $request->validate([
            'note' => 'nullable|string|max:2000',
        ]);

        try {
            DB::beginTransaction();

            $stockTaking = LibraryStockTaking::create([
                'reference_no' => $this->generateReferenceNo(),
                'started_at' => Carbon::now(),
                'ended_at' => null,
                'status' => 'in_progress',
                'conducted_by_staff_id' => $this->getCurrentStaffId(),
                'note' => $validated['note'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'is_active' => true,
            ]);

            // Log activity
            $this->logActivity(
                "Created stock taking: {$stockTaking->reference_no}",
                $stockTaking,
                [
                    'reference_no' => $stockTaking->reference_no,
                    'status' => $stockTaking->status,
                    'started_at' => $stockTaking->started_at->format('Y-m-d H:i:s'),
                    'conducted_by_staff_id' => $stockTaking->conducted_by_staff_id,
                    'note' => $stockTaking->note,
                ],
                'library_stock_takings'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock taking created successfully!',
                'data' => [
                    'id' => $stockTaking->id,
                    'reference_no' => $stockTaking->reference_no,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating stock taking: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create stock taking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display stock taking details
     * 
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $stockTaking = LibraryStockTaking::with(['conductedBy.user', 'creator', 'updater'])
            ->findOrFail($id);

        // For AJAX requests, return JSON
        if (request()->ajax()) {
            $conductedBy = $stockTaking->conductedBy && $stockTaking->conductedBy->user 
                ? $stockTaking->conductedBy->user->name 
                : ($stockTaking->creator ? $stockTaking->creator->name : 'System');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $stockTaking->id,
                    'reference_no' => $stockTaking->reference_no,
                    'started_at' => $stockTaking->started_at ? $stockTaking->started_at->format('Y-m-d H:i:s') : 'N/A',
                    'ended_at' => $stockTaking->ended_at ? $stockTaking->ended_at->format('Y-m-d H:i:s') : 'N/A',
                    'status' => $stockTaking->status,
                    'conducted_by' => $conductedBy,
                    'note' => $stockTaking->note ?? 'No notes',
                    'is_active' => $stockTaking->is_active,
                    'created_by' => $stockTaking->creator ? $stockTaking->creator->name : 'N/A',
                    'created_at' => $stockTaking->created_at->format('Y-m-d H:i:s'),
                    'updated_by' => $stockTaking->updater ? $stockTaking->updater->name : 'N/A',
                    'updated_at' => $stockTaking->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);
        }

        // Return view (will be created later)
        return view('admin.library.stock_takings.show', compact('stockTaking'));
    }

    /**
     * Update stock taking (note/status only)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update stock taking.'
            ], 403);
        }

        $stockTaking = LibraryStockTaking::findOrFail($id);

        $validated = $request->validate([
            'note' => 'nullable|string|max:2000',
            'status' => 'nullable|in:in_progress,completed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            // Get old values for logging
            $oldAttributes = [
                'status' => $stockTaking->status,
                'ended_at' => $stockTaking->ended_at ? $stockTaking->ended_at->format('Y-m-d H:i:s') : null,
                'note' => $stockTaking->note,
            ];

            $updateData = [
                'updated_by' => Auth::id(),
            ];

            if ($request->has('note')) {
                $updateData['note'] = $validated['note'];
            }

            // If changing status to completed/cancelled, set ended_at
            if (isset($validated['status']) && $validated['status'] !== 'in_progress') {
                if ($stockTaking->status === 'in_progress' && !$stockTaking->ended_at) {
                    $updateData['ended_at'] = Carbon::now();
                }
                $updateData['status'] = $validated['status'];
            }

            $stockTaking->update($updateData);
            $stockTaking->refresh();

            // Log activity
            $newAttributes = [
                'status' => $stockTaking->status,
                'ended_at' => $stockTaking->ended_at ? $stockTaking->ended_at->format('Y-m-d H:i:s') : null,
                'note' => $stockTaking->note,
            ];
            $this->logActivityUpdate(
                "Updated stock taking: {$stockTaking->reference_no}",
                $stockTaking,
                $oldAttributes,
                $newAttributes,
                'library_stock_takings'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock taking updated successfully!',
                'data' => [
                    'id' => $stockTaking->id,
                    'status' => $stockTaking->status,
                    'ended_at' => $stockTaking->ended_at ? $stockTaking->ended_at->format('Y-m-d H:i') : null,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating stock taking: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock taking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete stock taking (set is_active = 0)
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete stock taking.'
            ], 403);
        }

        try {
            $stockTaking = LibraryStockTaking::findOrFail($id);

            $stockTaking->update([
                'is_active' => false,
                'updated_by' => Auth::id(),
            ]);

            // Log activity
            $this->logActivity(
                "Deleted stock taking: {$stockTaking->reference_no} (ID: {$stockTaking->id})",
                $stockTaking,
                [
                    'reference_no' => $stockTaking->reference_no,
                    'status' => $stockTaking->status,
                    'started_at' => $stockTaking->started_at ? $stockTaking->started_at->format('Y-m-d H:i:s') : null,
                    'ended_at' => $stockTaking->ended_at ? $stockTaking->ended_at->format('Y-m-d H:i:s') : null,
                ],
                'library_stock_takings'
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock taking deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting stock taking: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete stock taking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to toggle status.'
            ], 403);
        }

        try {
            $stockTaking = LibraryStockTaking::findOrFail($id);
            $oldStatus = $stockTaking->is_active;

            $stockTaking->update([
                'is_active' => !$stockTaking->is_active,
                'updated_by' => Auth::id(),
            ]);
            $stockTaking->refresh();

            // Log activity
            $this->logActivity(
                "Toggled stock taking status: {$stockTaking->reference_no} (ID: {$stockTaking->id}) - " . ($stockTaking->is_active ? 'Activated' : 'Deactivated'),
                $stockTaking,
                [
                    'reference_no' => $stockTaking->reference_no,
                    'old_is_active' => $oldStatus,
                    'new_is_active' => $stockTaking->is_active,
                    'status' => $stockTaking->status,
                ],
                'library_stock_takings'
            );

            return response()->json([
                'success' => true,
                'message' => 'Status toggled successfully!',
                'data' => [
                    'is_active' => $stockTaking->is_active
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling stock taking status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete stock taking (admin only)
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {
        if (!$this->canForceDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can permanently delete stock taking.'
            ], 403);
        }

        try {
            $stockTaking = LibraryStockTaking::findOrFail($id);
            $referenceNo = $stockTaking->reference_no;
            $stockTakingId = $stockTaking->id;

            // Log activity before deletion
            $this->logActivity(
                "Permanently deleted stock taking: {$referenceNo} (ID: {$stockTakingId})",
                null,
                [
                    'reference_no' => $referenceNo,
                    'status' => $stockTaking->status,
                    'started_at' => $stockTaking->started_at ? $stockTaking->started_at->format('Y-m-d H:i:s') : null,
                    'ended_at' => $stockTaking->ended_at ? $stockTaking->ended_at->format('Y-m-d H:i:s') : null,
                    'conducted_by_staff_id' => $stockTaking->conducted_by_staff_id,
                ],
                'library_stock_takings'
            );

            // Delete permanently
            $stockTaking->delete();

            return response()->json([
                'success' => true,
                'message' => 'Stock taking permanently deleted!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error force deleting stock taking: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete: ' . $e->getMessage()
            ], 500);
        }
    }
}

