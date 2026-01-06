<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryStockTaking;
use App\Models\LibraryStockTakingItem;
use App\Models\LibraryCopy;
use App\Models\Staff;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Library Stock Taking Item Controller (Scanner & Items CRUD)
 * 
 * Handles scanning barcodes and managing stock taking items.
 */
class LibraryStockTakingItemController extends Controller
{
    use LogsActivity;
    /**
     * Check if user can write
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
     * Get current staff ID
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
     * Calculate summary counts for a stock taking
     * 
     * @param int $stockTakingId
     * @return array
     */
    private function calculateSummary(int $stockTakingId): array
    {
        $items = LibraryStockTakingItem::where('stock_taking_id', $stockTakingId)
            ->where('is_active', true)
            ->select('scan_result', DB::raw('COUNT(*) as count'))
            ->groupBy('scan_result')
            ->get();

        $summary = [
            'found' => 0,
            'lost' => 0,
            'damaged' => 0,
            'not_checked' => 0,
            'total' => 0, // For view compatibility
        ];

        foreach ($items as $item) {
            $summary[$item->scan_result] = $item->count;
            $summary['total'] += $item->count;
        }

        // Calculate not_checked (total active copies - scanned)
        $totalActiveCopies = LibraryCopy::where('is_active', true)->count();
        $summary['not_checked'] = max(0, $totalActiveCopies - $summary['total']);

        return $summary;
    }

    /**
     * Get items data for DataTables
     * 
     * @param Request $request
     * @param int $stockTakingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function itemsData(Request $request, $stockTakingId)
    {
        $query = LibraryStockTakingItem::with(['copy.item', 'scannedBy.user'])
            ->where('stock_taking_id', $stockTakingId);

        // Filter by scan_result
        if ($scanResult = $request->input('scan_result')) {
            $query->where('scan_result', $scanResult);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $isActive = $request->input('is_active');
            if ($isActive !== 'all') {
                $query->where('is_active', $isActive === '1');
            }
        } else {
            // Default: active only
            $query->where('is_active', true);
        }

        // Search
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('copy', function ($copyQuery) use ($search) {
                    $copyQuery->where('barcode', 'like', "%{$search}%");
                })
                ->orWhere('condition_note', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%");
            });
        }

        // Total records
        $totalRecords = $query->count();

        // Order
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = ['id', 'copy_id', 'scan_result', 'scanned_at', 'created_at'];
        $orderColumn = $columns[$orderColumnIndex] ?? 'scanned_at';
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $data = $query->skip($start)->take($length)->get();

        // Format data
        $formattedData = $data->map(function ($item, $index) use ($start) {
            $copy = $item->copy;
            $bookTitle = $copy && $copy->item ? $copy->item->title : 'N/A';
            $callNumber = $copy ? ($copy->call_number ?: 'N/A') : 'N/A';
            $barcode = $copy ? ($copy->barcode ?: 'N/A') : 'N/A';
            $scannedBy = $item->scannedBy && $item->scannedBy->user 
                ? $item->scannedBy->user->name 
                : 'System';
            
            // Status badge
            $statusBadges = [
                'found' => '<span class="badge bg-success"><i class="fas fa-check"></i> FOUND</span>',
                'damaged' => '<span class="badge bg-warning"><i class="fas fa-tools"></i> DAMAGED</span>',
                'lost' => '<span class="badge bg-danger"><i class="fas fa-times"></i> LOST</span>',
            ];
            $statusBadge = $statusBadges[$item->scan_result] ?? '<span class="badge bg-secondary">' . strtoupper($item->scan_result) . '</span>';
            
            // Actions
            $canEdit = auth()->user()->hasAnyRole(['admin', 'manager', 'staff']);
            $canDelete = auth()->user()->hasAnyRole(['admin', 'manager']);
            
            $actions = '';
            if ($canEdit) {
                $actions .= '<button class="btn btn-sm btn-primary" onclick="editItem(' . $item->id . ')" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button> ';
            }
            if ($canDelete) {
                $actions .= '<button class="btn btn-sm btn-danger" onclick="deleteItem(' . $item->id . ')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>';
            }

            return [
                'DT_RowIndex' => $start + $index + 1,
                'id' => $item->id,
                'copy_id' => $item->copy_id,
                'barcode' => $barcode,
                'book_title' => $bookTitle,
                'call_number' => $callNumber,
                'scan_result' => $statusBadge,
                'condition_note' => $item->condition_note ?? 'N/A',
                'scanned_at' => $item->scanned_at ? $item->scanned_at->format('Y-m-d H:i') : 'N/A',
                'scanned_by' => $scannedBy,
                'is_active' => $item->is_active,
                'actions' => $actions,
            ];
        });

        $response = [
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $formattedData,
        ];
        
        // Add summary if requested
        if ($request->input('summary')) {
            $response['summary'] = $this->calculateSummary($stockTakingId);
        }
        
        return response()->json($response);
    }

    /**
     * Scan barcode and upsert item
     * 
     * @param Request $request
     * @param int $stockTakingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function scan(Request $request, $stockTakingId)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to scan items.'
            ], 403);
        }

        // Validate stock taking exists and is in progress
        $stockTaking = LibraryStockTaking::findOrFail($stockTakingId);

        if ($stockTaking->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Stock taking is not in progress. Cannot scan items.'
            ], 400);
        }

        $validated = $request->validate([
            'barcode' => 'required|string',
            'scan_result' => 'nullable|in:found,lost,damaged',
            'condition_note' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $barcode = trim($validated['barcode']);

            // Find copy by barcode or ID
            $copy = LibraryCopy::where('barcode', $barcode)
                ->orWhere('id', $barcode)
                ->first();

            if (!$copy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Copy not found! Barcode: ' . $barcode
                ], 404);
            }

            // Check if not active
            if (!$copy->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This copy is inactive and cannot be scanned.'
                ], 400);
            }

            $scanResult = $validated['scan_result'] ?? 'found';
            $conditionNote = $validated['condition_note'] ?? null;
            $staffId = $this->getCurrentStaffId();
            $now = Carbon::now();

            // Upsert logic: check if item already exists
            $existingItem = LibraryStockTakingItem::where('stock_taking_id', $stockTakingId)
                ->where('copy_id', $copy->id)
                ->first();

            if ($existingItem) {
                // Update existing item
                $existingItem->update([
                    'scan_result' => $scanResult,
                    'condition_note' => $conditionNote,
                    'scanned_by_staff_id' => $staffId,
                    'scanned_at' => $now,
                    'updated_by' => Auth::id(),
                ]);

                $item = $existingItem;
                $action = 'updated';

            } else {
                // Create new item
                $item = LibraryStockTakingItem::create([
                    'stock_taking_id' => $stockTakingId,
                    'copy_id' => $copy->id,
                    'scan_result' => $scanResult,
                    'condition_note' => $conditionNote,
                    'scanned_by_staff_id' => $staffId,
                    'scanned_at' => $now,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'is_active' => true,
                ]);

                $action = 'created';
            }

            // Load relationships for logging
            $item->load(['copy.item', 'stockTaking']);

            // Log activity
            $bookTitle = $copy->item ? $copy->item->title : 'Unknown Book';
            $copyBarcode = $copy->barcode ?: $copy->id;
            $this->logActivity(
                "Scanned stock taking item: '{$bookTitle}' (Barcode: {$copyBarcode}, Result: {$scanResult}) - Stock Taking: {$item->stockTaking->reference_no}",
                $item,
                [
                    'stock_taking_id' => $stockTakingId,
                    'stock_taking_reference_no' => $item->stockTaking->reference_no,
                    'copy_id' => $copy->id,
                    'copy_barcode' => $copyBarcode,
                    'book_title' => $bookTitle,
                    'scan_result' => $scanResult,
                    'condition_note' => $conditionNote,
                    'scanned_by_staff_id' => $staffId,
                    'scanned_at' => $now->format('Y-m-d H:i:s'),
                    'action' => $action,
                ],
                'library_stock_taking_items'
            );

            // Calculate summary
            $summary = $this->calculateSummary($stockTakingId);

            DB::commit();

            // Get book title for response
            $bookTitle = $copy->item ? $copy->item->title : 'Unknown Book';
            $copyBarcode = $copy->barcode ?: $copy->id;

            return response()->json([
                'success' => true,
                'message' => $action === 'created' 
                    ? "Scanned successfully! {$bookTitle}" 
                    : "Updated scan! {$bookTitle}",
                'data' => [
                    'item' => [
                        'id' => $item->id,
                        'copy_id' => $item->copy_id,
                        'barcode' => $copyBarcode,
                        'book_title' => $bookTitle,
                        'scan_result' => $item->scan_result,
                        'scanned_at' => $item->scanned_at->format('Y-m-d H:i:s'),
                        'action' => $action,
                    ],
                    'summary' => $summary,
                ]
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            
            // Check for unique constraint violation
            if ($e->getCode() == 23000) {
                return response()->json([
                    'success' => false,
                    'message' => 'This copy has already been scanned in this stock taking.'
                ], 400);
            }

            Log::error('Error scanning item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to scan item: Database error.'
            ], 500);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error scanning item: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to scan item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single item data
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getItem($id)
    {
        $item = LibraryStockTakingItem::with(['copy.item', 'scannedBy.user', 'stockTaking'])->findOrFail($id);
        
        $copy = $item->copy;
        $barcode = $copy ? ($copy->barcode ?: 'N/A') : 'N/A';
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'copy_id' => $item->copy_id,
                'barcode' => $barcode,
                'book_title' => $copy && $copy->item ? $copy->item->title : 'N/A',
                'scan_result' => $item->scan_result,
                'condition_note' => $item->condition_note,
                'note' => $item->note,
                'scanned_at' => $item->scanned_at ? $item->scanned_at->format('Y-m-d H:i') : 'N/A',
                'scanned_by' => $item->scannedBy && $item->scannedBy->user ? $item->scannedBy->user->name : 'System',
            ]
        ]);
    }

    /**
     * Update stock taking item
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItem(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update items.'
            ], 403);
        }

        $item = LibraryStockTakingItem::with('stockTaking')->findOrFail($id);

        // Check if stock taking is in progress
        if ($item->stockTaking->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update item. Stock taking is not in progress.'
            ], 400);
        }

        $validated = $request->validate([
            'scan_result' => 'required|in:found,lost,damaged,not_checked',
            'condition_note' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            // Load relationships and get old values
            $item->load(['copy.item', 'stockTaking']);
            $oldAttributes = [
                'scan_result' => $item->scan_result,
                'condition_note' => $item->condition_note,
                'note' => $item->note,
            ];

            $item->update([
                'scan_result' => $validated['scan_result'],
                'condition_note' => $validated['condition_note'] ?? null,
                'note' => $validated['note'] ?? null,
                'updated_by' => Auth::id(),
            ]);
            $item->refresh();

            // Log activity
            $bookTitle = $item->copy && $item->copy->item ? $item->copy->item->title : 'Unknown Book';
            $copyBarcode = $item->copy ? ($item->copy->barcode ?: 'N/A') : 'N/A';
            $newAttributes = [
                'scan_result' => $item->scan_result,
                'condition_note' => $item->condition_note,
                'note' => $item->note,
            ];
            $this->logActivityUpdate(
                "Updated stock taking item: '{$bookTitle}' (Barcode: {$copyBarcode}, ID: {$item->id}) - Stock Taking: {$item->stockTaking->reference_no}",
                $item,
                $oldAttributes,
                $newAttributes,
                'library_stock_taking_items'
            );

            // Calculate updated summary
            $summary = $this->calculateSummary($item->stock_taking_id);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully!',
                'data' => [
                    'item' => $item,
                    'summary' => $summary,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating stock taking item: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete item
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyItem($id)
    {
        if (!$this->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete items.'
            ], 403);
        }

        try {
            $item = LibraryStockTakingItem::findOrFail($id);
            $item->load(['copy.item', 'stockTaking']);

            $item->update([
                'is_active' => false,
                'updated_by' => Auth::id(),
            ]);

            // Log activity
            $bookTitle = $item->copy && $item->copy->item ? $item->copy->item->title : 'Unknown Book';
            $copyBarcode = $item->copy ? ($item->copy->barcode ?: 'N/A') : 'N/A';
            $this->logActivity(
                "Deleted stock taking item: '{$bookTitle}' (Barcode: {$copyBarcode}, ID: {$item->id}) - Stock Taking: {$item->stockTaking->reference_no}",
                $item,
                [
                    'stock_taking_id' => $item->stock_taking_id,
                    'stock_taking_reference_no' => $item->stockTaking->reference_no,
                    'copy_id' => $item->copy_id,
                    'copy_barcode' => $copyBarcode,
                    'book_title' => $bookTitle,
                    'scan_result' => $item->scan_result,
                ],
                'library_stock_taking_items'
            );

            // Calculate updated summary
            $summary = $this->calculateSummary($item->stock_taking_id);

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully!',
                'data' => [
                    'summary' => $summary,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting stock taking item: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete item (admin only)
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDeleteItem($id)
    {
        if (!$this->canForceDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can permanently delete items.'
            ], 403);
        }

        try {
            $item = LibraryStockTakingItem::findOrFail($id);
            $item->load(['copy.item', 'stockTaking']);
            $stockTakingId = $item->stock_taking_id;
            $stockTakingRef = $item->stockTaking->reference_no;
            $bookTitle = $item->copy && $item->copy->item ? $item->copy->item->title : 'Unknown Book';
            $copyBarcode = $item->copy ? ($item->copy->barcode ?: 'N/A') : 'N/A';
            $itemId = $item->id;

            // Log activity before deletion
            $this->logActivity(
                "Permanently deleted stock taking item: '{$bookTitle}' (Barcode: {$copyBarcode}, ID: {$itemId}) - Stock Taking: {$stockTakingRef}",
                null,
                [
                    'stock_taking_id' => $stockTakingId,
                    'stock_taking_reference_no' => $stockTakingRef,
                    'copy_id' => $item->copy_id,
                    'copy_barcode' => $copyBarcode,
                    'book_title' => $bookTitle,
                    'scan_result' => $item->scan_result,
                    'scanned_at' => $item->scanned_at ? $item->scanned_at->format('Y-m-d H:i:s') : null,
                ],
                'library_stock_taking_items'
            );

            $item->delete();

            // Calculate updated summary
            $summary = $this->calculateSummary($stockTakingId);

            return response()->json([
                'success' => true,
                'message' => 'Item permanently deleted!',
                'data' => [
                    'summary' => $summary,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error force deleting stock taking item: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete item: ' . $e->getMessage()
            ], 500);
        }
    }
}

