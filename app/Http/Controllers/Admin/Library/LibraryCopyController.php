<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryCopy;
use App\Models\LibraryCopyStatusHistory;
use App\Models\LibraryItem;
use App\Models\LibraryShelf;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;


class LibraryCopyController extends Controller
{
    use LogsActivity;
    // Permission helper methods
    private function canRead()
    {
        $user = auth()->user();
        return $user->hasAnyRole(['admin', 'manager', 'staff', 'principal']);
    }

    private function canWrite()
    {
        $user = auth()->user();
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    private function canDelete()
    {
        $user = auth()->user();
        return $user->hasAnyRole(['admin', 'manager']);
    }

    private function isAdmin()
    {
        return auth()->user()->hasRole('admin');
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
            $query = LibraryCopy::with(['item', 'shelf', 'creator', 'updater'])
                ->select('library_copies.*');

            // Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by shelf
            if ($request->filled('shelf_id') && $request->shelf_id !== 'all') {
                $query->where('shelf_id', $request->shelf_id);
            }

            // Filter by item
            if ($request->filled('library_item_id') && $request->library_item_id !== 'all') {
                $query->where('library_item_id', $request->library_item_id);
            }

            // Filter by is_active
            if ($request->filled('is_active')) {
                if ($request->is_active === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->is_active === 'inactive') {
                    $query->where('is_active', false);
                }
            } else {
                // Default: show active only
                $query->where('is_active', true);
            }

            return DataTables::of($query)
                ->addIndexColumn() // Add DT_RowIndex for row numbering
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('barcode', 'like', "%{$search}%")
                                ->orWhere('call_number', 'like', "%{$search}%")
                                ->orWhereHas('item', function ($itemQuery) use ($search) {
                                    $itemQuery->where('title', 'like', "%{$search}%")
                                        ->orWhere('isbn', 'like', "%{$search}%");
                                });
                        });
                    }
                })
                ->editColumn('acquired_date', function ($copy) {
                    return $copy->acquired_date ? $copy->acquired_date->format('Y-m-d') : '-';
                })
                ->addColumn('item_info', function ($copy) {
                    if ($copy->item) {
                        $title = e($copy->item->title);
                        $isbn = $copy->item->isbn ? '<small class="text-muted">ISBN: ' . e($copy->item->isbn) . '</small>' : '';
                        $edition = $copy->item->edition ? '<small class="text-muted"> | Ed: ' . e($copy->item->edition) . '</small>' : '';
                        return $title . '<br>' . $isbn . $edition;
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('shelf_location', function ($copy) {
                    if ($copy->shelf) {
                        return '<span class="badge bg-info">' . e($copy->shelf->code ?? $copy->shelf->location) . '</span>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('status_badge', function ($copy) {
                    $badges = [
                        'available' => 'success',
                        'on_loan' => 'warning',
                        'reserved' => 'info',
                        'lost' => 'danger',
                        'damaged' => 'danger',
                        'withdrawn' => 'secondary'
                    ];
                    $color = $badges[$copy->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst(str_replace('_', ' ', $copy->status)) . '</span>';
                })
                ->addColumn('condition_badge', function ($copy) {
                    if (!$copy->condition) return '<span class="text-muted">-</span>';
                    
                    $badges = [
                        'new' => 'success',
                        'good' => 'primary',
                        'fair' => 'warning',
                        'poor' => 'danger',
                        'damaged' => 'danger'
                    ];
                    $color = $badges[$copy->condition] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($copy->condition) . '</span>';
                })
                ->addColumn('active_badge', function ($copy) {
                    return $copy->is_active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-secondary">Inactive</span>';
                })
                ->addColumn('actions', function ($copy) {
                    $actions = '<div class="btn-group btn-group-sm" role="group">';
                    
                    // View
                    $actions .= '<button type="button" class="btn btn-info" onclick="viewCopy(' . $copy->id . ')" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>';
                    
                    // History
                    $actions .= '<button type="button" class="btn btn-secondary" onclick="viewHistory(' . $copy->id . ')" title="View History">
                                    <i class="fas fa-history"></i>
                                </button>';
                    
                    // Edit
                    if ($this->canWrite()) {
                        $actions .= '<button type="button" class="btn btn-primary" onclick="openEditModal(' . $copy->id . ')" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>';
                    }
                    
                    // Toggle Active
                    if ($this->canWrite()) {
                        $icon = $copy->is_active ? 'toggle-on' : 'toggle-off';
                        $title = $copy->is_active ? 'Deactivate' : 'Activate';
                        $actions .= '<button type="button" class="btn btn-warning" onclick="toggleCopyActive(' . $copy->id . ')" title="' . $title . '">
                                        <i class="fas fa-' . $icon . '"></i>
                                    </button>';
                    }
                    
                    // Delete
                    if ($this->canDelete()) {
                        $actions .= '<button type="button" class="btn btn-danger" onclick="deleteCopy(' . $copy->id . ')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>';
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['item_info', 'shelf_location', 'status_badge', 'condition_badge', 'active_badge', 'actions'])
                ->make(true);
        }

        // Load items and shelves for filters
        $items = LibraryItem::where('is_active', true)->orderBy('title')->get();
        $shelves = LibraryShelf::where('is_active', true)->orderBy('code')->get();

        return view('admin.library.copies.index', compact('items', 'shelves'));
    }

    /**
     * Show single copy (JSON)
     */
    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $copy = LibraryCopy::with(['item', 'shelf', 'creator', 'updater'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $copy
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Copy not found.'
            ], 404);
        }
    }

    /**
     * Store new copy
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'library_item_id' => 'required|exists:library_items,id',
            'barcode' => 'required|max:100|unique:library_copies,barcode',
            'call_number' => 'nullable|max:100',
            'shelf_id' => 'nullable|exists:library_shelves,id',
            'acquired_date' => 'nullable|date',
            'condition' => 'nullable|max:50',
            'status' => 'required|max:30'
        ]);

        try {
            DB::beginTransaction();

            $validated['created_by'] = Auth::id();
            $validated['updated_by'] = Auth::id();
            $validated['is_active'] = true;

            $copy = LibraryCopy::create($validated);
            $copy->load(['item', 'shelf']);

            // Log activity
            $bookTitle = $copy->item ? $copy->item->title : 'Unknown Book';
            $shelfLocation = $copy->shelf ? ($copy->shelf->code ?? $copy->shelf->location) : 'N/A';
            $this->logActivity(
                "Created library copy: Barcode '{$copy->barcode}' - '{$bookTitle}' (Shelf: {$shelfLocation})",
                $copy,
                [
                    'copy_id' => $copy->id,
                    'barcode' => $copy->barcode,
                    'call_number' => $copy->call_number,
                    'library_item_id' => $copy->library_item_id,
                    'book_title' => $bookTitle,
                    'shelf_id' => $copy->shelf_id,
                    'shelf_location' => $shelfLocation,
                    'status' => $copy->status,
                    'condition' => $copy->condition,
                    'acquired_date' => $copy->acquired_date ? $copy->acquired_date->format('Y-m-d') : null,
                ],
                'library_copies'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Copy added successfully.',
                'data' => $copy
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating copy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add copy. Please try again.'
            ], 500);
        }
    }

    /**
     * Update copy
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $copy = LibraryCopy::findOrFail($id);

        $validated = $request->validate([
            'library_item_id' => 'required|exists:library_items,id',
            'barcode' => 'required|max:100|unique:library_copies,barcode,' . $id,
            'call_number' => 'nullable|max:100',
            'shelf_id' => 'nullable|exists:library_shelves,id',
            'acquired_date' => 'nullable|date',
            'condition' => 'nullable|max:50',
            'status' => 'required|max:30',
            'change_note' => 'nullable|string|max:500' // Optional note for status/condition changes
        ]);

        try {
            // Check if trying to set status to 'available' while on loan
            if ($validated['status'] === 'available' && $copy->status === 'on_loan') {
                // Check if there are active loans
                if (Schema::hasTable('library_loans')) {
                    $hasActiveLoan = DB::table('library_loans')
                        ->where('library_copy_id', $copy->id)
                        ->whereIn('status', ['active', 'on_loan', 'borrowed'])
                        ->exists();
                    
                    if ($hasActiveLoan) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot set available while copy is on loan. Please return the loan first.'
                        ], 422);
                    }
                }
            }

            DB::beginTransaction();

            // Load relationships and store old values before update
            $copy->load(['item', 'shelf']);
            $oldStatus = $copy->status;
            $oldCondition = $copy->condition;
            $oldAttributes = [
                'barcode' => $copy->barcode,
                'call_number' => $copy->call_number,
                'library_item_id' => $copy->library_item_id,
                'shelf_id' => $copy->shelf_id,
                'status' => $copy->status,
                'condition' => $copy->condition,
                'acquired_date' => $copy->acquired_date ? $copy->acquired_date->format('Y-m-d') : null,
            ];

            // Prepare update data (remove change_note from copy update)
            $updateData = collect($validated)->except('change_note')->toArray();
            $updateData['updated_by'] = Auth::id();

            // Update the copy
            $copy->update($updateData);
            $copy->refresh()->load(['item', 'shelf']);

            // Check if status or condition changed - create history record
            $statusChanged = $oldStatus !== $validated['status'];
            $conditionChanged = $oldCondition !== ($validated['condition'] ?? null);

            if ($statusChanged || $conditionChanged) {
                // Determine action type
                if ($statusChanged && $conditionChanged) {
                    $action = 'status_condition_change';
                } elseif ($statusChanged) {
                    $action = 'status_change';
                } else {
                    $action = 'condition_change';
                }

                // Create history record
                LibraryCopyStatusHistory::create([
                    'copy_id' => $copy->id,
                    'old_status' => $oldStatus,
                    'new_status' => $validated['status'],
                    'old_condition' => $oldCondition,
                    'new_condition' => $validated['condition'] ?? null,
                    'action' => $action,
                    'note' => $validated['change_note'] ?? null,
                    'changed_by' => Auth::id(),
                    'changed_at' => now(),
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'is_active' => true
                ]);
            }

            // Log activity
            $bookTitle = $copy->item ? $copy->item->title : 'Unknown Book';
            $shelfLocation = $copy->shelf ? ($copy->shelf->code ?? $copy->shelf->location) : 'N/A';
            $newAttributes = [
                'barcode' => $copy->barcode,
                'call_number' => $copy->call_number,
                'library_item_id' => $copy->library_item_id,
                'shelf_id' => $copy->shelf_id,
                'status' => $copy->status,
                'condition' => $copy->condition,
                'acquired_date' => $copy->acquired_date ? $copy->acquired_date->format('Y-m-d') : null,
            ];
            $this->logActivityUpdate(
                "Updated library copy: Barcode '{$copy->barcode}' - '{$bookTitle}' (ID: {$copy->id})",
                $copy,
                $oldAttributes,
                $newAttributes,
                'library_copies'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Copy updated successfully.',
                'data' => $copy
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating copy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update copy. Please try again.'
            ], 500);
        }
    }

    /**
     * Soft delete (set is_active = 0)
     */
    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $copy = LibraryCopy::findOrFail($id);
            $copy->load(['item', 'shelf']);
            
            $copy->is_active = false;
            $copy->updated_by = Auth::id();
            $copy->save();

            // Log activity
            $bookTitle = $copy->item ? $copy->item->title : 'Unknown Book';
            $shelfLocation = $copy->shelf ? ($copy->shelf->code ?? $copy->shelf->location) : 'N/A';
            $this->logActivity(
                "Deleted library copy: Barcode '{$copy->barcode}' - '{$bookTitle}' (ID: {$copy->id}, Shelf: {$shelfLocation})",
                $copy,
                [
                    'copy_id' => $copy->id,
                    'barcode' => $copy->barcode,
                    'call_number' => $copy->call_number,
                    'library_item_id' => $copy->library_item_id,
                    'book_title' => $bookTitle,
                    'shelf_id' => $copy->shelf_id,
                    'shelf_location' => $shelfLocation,
                    'status' => $copy->status,
                    'condition' => $copy->condition,
                ],
                'library_copies'
            );

            return response()->json([
                'success' => true,
                'message' => 'Copy deactivated successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deactivating copy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate copy.'
            ], 500);
        }
    }

    /**
     * Toggle is_active status
     */
    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $copy = LibraryCopy::findOrFail($id);
            $copy->load(['item', 'shelf']);
            $oldStatus = $copy->is_active;
            
            $copy->is_active = !$copy->is_active;
            $copy->updated_by = Auth::id();
            $copy->save();

            $status = $copy->is_active ? 'activated' : 'deactivated';

            // Log activity
            $bookTitle = $copy->item ? $copy->item->title : 'Unknown Book';
            $shelfLocation = $copy->shelf ? ($copy->shelf->code ?? $copy->shelf->location) : 'N/A';
            $this->logActivity(
                "Toggled library copy status: Barcode '{$copy->barcode}' - '{$bookTitle}' (ID: {$copy->id}) - " . ucfirst($status),
                $copy,
                [
                    'copy_id' => $copy->id,
                    'barcode' => $copy->barcode,
                    'book_title' => $bookTitle,
                    'shelf_location' => $shelfLocation,
                    'old_is_active' => $oldStatus,
                    'new_is_active' => $copy->is_active,
                    'status' => $copy->status,
                ],
                'library_copies'
            );

            return response()->json([
                'success' => true,
                'message' => "Copy {$status} successfully."
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling copy status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle copy status.'
            ], 500);
        }
    }

    /**
     * Permanent delete (admin only)
     */
    public function forceDelete($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Admin only.'], 403);
        }

        try {
            $copy = LibraryCopy::findOrFail($id);
            $copy->load(['item', 'shelf']);
            
            $copyId = $copy->id;
            $barcode = $copy->barcode;
            $bookTitle = $copy->item ? $copy->item->title : 'Unknown Book';
            $shelfLocation = $copy->shelf ? ($copy->shelf->code ?? $copy->shelf->location) : 'N/A';

            // Check if copy has loan history
            if (Schema::hasTable('library_loans')) {
                $hasLoans = DB::table('library_loans')
                    ->where('library_copy_id', $copy->id)
                    ->exists();
                
                if ($hasLoans) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete: copy has loan history.'
                    ], 422);
                }
            }

            // Check if copy has status history
            if (Schema::hasTable('library_copy_status_history')) {
                $hasHistory = DB::table('library_copy_status_history')
                    ->where('library_copy_id', $copy->id)
                    ->exists();
                
                if ($hasHistory) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete: copy has status history.'
                    ], 422);
                }
            }

            // Log activity before deletion
            $this->logActivity(
                "Permanently deleted library copy: Barcode '{$barcode}' - '{$bookTitle}' (ID: {$copyId}, Shelf: {$shelfLocation})",
                null,
                [
                    'copy_id' => $copyId,
                    'barcode' => $barcode,
                    'call_number' => $copy->call_number,
                    'library_item_id' => $copy->library_item_id,
                    'book_title' => $bookTitle,
                    'shelf_id' => $copy->shelf_id,
                    'shelf_location' => $shelfLocation,
                    'status' => $copy->status,
                    'condition' => $copy->condition,
                ],
                'library_copies'
            );

            $copy->delete();

            return response()->json([
                'success' => true,
                'message' => 'Copy permanently deleted.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error force deleting copy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete copy permanently.'
            ], 500);
        }
    }

    /**
     * Find copy by barcode (optional helper)
     */
    public function findByBarcode($barcode)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $copy = LibraryCopy::with(['item', 'shelf'])
                ->where('barcode', $barcode)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $copy
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Copy not found with barcode: ' . $barcode
            ], 404);
        }
    }

    /**
     * Get status/condition change history for a copy
     */
    public function history($id)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $copy = LibraryCopy::findOrFail($id);

            $history = LibraryCopyStatusHistory::where('copy_id', $id)
                ->where('is_active', true)
                ->with(['changer:id,name'])
                ->orderBy('changed_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'action' => $record->action_text,
                        'old_status' => $record->old_status,
                        'new_status' => $record->new_status,
                        'old_condition' => $record->old_condition,
                        'new_condition' => $record->new_condition,
                        'change_summary' => $record->change_summary,
                        'note' => $record->note,
                        'changed_by' => $record->changer->name ?? 'Unknown',
                        'changed_at' => $record->changed_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'copy' => [
                        'id' => $copy->id,
                        'barcode' => $copy->barcode,
                        'current_status' => $copy->status,
                        'current_condition' => $copy->condition,
                    ],
                    'history' => $history
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching copy history: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load history.'
            ], 500);
        }
    }
}

