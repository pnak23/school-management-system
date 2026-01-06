<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LibraryCategoryController extends Controller
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
     * Display listing of categories (Ajax + View)
     */
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to view categories.');
        }

        if ($request->ajax()) {
            $query = LibraryCategory::query();

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
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $categories = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $categories->items(),
                'pagination' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                ],
            ]);
        }

        return view('admin.library.categories.index');
    }

    /**
     * Show single category
     */
    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $category = LibraryCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to create categories.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150|unique:library_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form fields.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category = LibraryCategory::create([
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => auth()->id(),
                'is_active' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data' => $category
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Category creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to update categories.'
            ], 403);
        }

        $category = LibraryCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150|unique:library_categories,name,' . $id,
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form fields.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            \Log::error('Category update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete category (deactivate)
     */
    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to delete categories.'
            ], 403);
        }

        $category = LibraryCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        try {
            $category->update([
                'is_active' => 0,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category deactivated successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Category deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle category status
     */
    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $category = LibraryCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        try {
            $category->is_active = !$category->is_active;
            $category->updated_by = auth()->id();
            $category->save();

            return response()->json([
                'success' => true,
                'message' => 'Category status updated successfully.',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            \Log::error('Category status toggle failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update category status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete category (permanent)
     * Admin only
     */
    public function forceDelete($id)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can permanently delete categories.'
            ], 403);
        }

        $category = LibraryCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        try {
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category permanently deleted successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Category force delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete category: ' . $e->getMessage()
            ], 500);
        }
    }
}


