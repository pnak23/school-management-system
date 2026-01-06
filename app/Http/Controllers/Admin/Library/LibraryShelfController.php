<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryShelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LibraryShelfController extends Controller
{
    // Permission helper methods
    private function canRead(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'manager', 'staff', 'principal']);
    }

    private function canWrite(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'manager', 'staff']);
    }

    private function canDelete(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'manager']);
    }

    private function isAdmin(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Display listing of shelves (Ajax + View)
     */
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to view shelves.');
        }

        if ($request->ajax()) {
            $query = LibraryShelf::query();

            // Status filter
            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', 1);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', 0);
                }
            }

            // Search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $shelves = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $shelves->items(),
                'pagination' => [
                    'current_page' => $shelves->currentPage(),
                    'last_page' => $shelves->lastPage(),
                    'per_page' => $shelves->perPage(),
                    'total' => $shelves->total(),
                ],
            ]);
        }

        return view('admin.library.shelves.index');
    }

    /**
     * Show single shelf
     */
    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $shelf = LibraryShelf::find($id);

        if (!$shelf) {
            return response()->json([
                'success' => false,
                'message' => 'Shelf not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $shelf
        ]);
    }

    /**
     * Store new shelf
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to create shelves.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:library_shelves,code',
            'location' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form fields.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $shelf = LibraryShelf::create([
                'code' => $request->code,
                'location' => $request->location,
                'description' => $request->description,
                'created_by' => auth()->id(),
                'is_active' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shelf created successfully.',
                'data' => $shelf
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Shelf creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create shelf: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update shelf
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to update shelves.'
            ], 403);
        }

        $shelf = LibraryShelf::find($id);

        if (!$shelf) {
            return response()->json([
                'success' => false,
                'message' => 'Shelf not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:library_shelves,code,' . $id,
            'location' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form fields.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $shelf->update([
                'code' => $request->code,
                'location' => $request->location,
                'description' => $request->description,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shelf updated successfully.',
                'data' => $shelf
            ]);

        } catch (\Exception $e) {
            \Log::error('Shelf update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update shelf: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete shelf (deactivate)
     */
    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to delete shelves.'
            ], 403);
        }

        $shelf = LibraryShelf::find($id);

        if (!$shelf) {
            return response()->json([
                'success' => false,
                'message' => 'Shelf not found.'
            ], 404);
        }

        try {
            $shelf->update([
                'is_active' => 0,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shelf deactivated successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Shelf deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate shelf: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle shelf status
     */
    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $shelf = LibraryShelf::find($id);

        if (!$shelf) {
            return response()->json([
                'success' => false,
                'message' => 'Shelf not found.'
            ], 404);
        }

        try {
            $shelf->is_active = !$shelf->is_active;
            $shelf->updated_by = auth()->id();
            $shelf->save();

            return response()->json([
                'success' => true,
                'message' => 'Shelf status updated successfully.',
                'data' => $shelf
            ]);

        } catch (\Exception $e) {
            \Log::error('Shelf status toggle failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update shelf status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete shelf (permanent)
     * Admin only
     * Check if shelf is referenced by library items/copies
     */
    public function forceDelete($id)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can permanently delete shelves.'
            ], 403);
        }

        $shelf = LibraryShelf::find($id);

        if (!$shelf) {
            return response()->json([
                'success' => false,
                'message' => 'Shelf not found.'
            ], 404);
        }

        try {
            // Check if shelf is referenced by library_items or library_copies
            $itemsCount = DB::table('library_items')
                ->where('shelf_id', $id)
                ->count();

            $copiesCount = DB::table('library_copies')
                ->where('shelf_id', $id)
                ->count();

            if ($itemsCount > 0 || $copiesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete: shelf is in use by ' . ($itemsCount + $copiesCount) . ' item(s).'
                ], 400);
            }

            $shelf->delete();

            return response()->json([
                'success' => true,
                'message' => 'Shelf permanently deleted successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Shelf force delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete shelf: ' . $e->getMessage()
            ], 500);
        }
    }
}


