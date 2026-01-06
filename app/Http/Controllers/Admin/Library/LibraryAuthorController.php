<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryAuthor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LibraryAuthorController extends Controller
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
     * Display listing of authors (Ajax + View)
     */
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to view authors.');
        }

        if ($request->ajax()) {
            $query = LibraryAuthor::query();

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
                      ->orWhere('nationality', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $authors = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $authors->items(),
                'pagination' => [
                    'current_page' => $authors->currentPage(),
                    'last_page' => $authors->lastPage(),
                    'per_page' => $authors->perPage(),
                    'total' => $authors->total(),
                ],
            ]);
        }

        return view('admin.library.authors.index');
    }

    /**
     * Show single author
     */
    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $author = LibraryAuthor::find($id);

        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Author not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $author
        ]);
    }

    /**
     * Store new author
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to create authors.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150|unique:library_authors,name',
            'nationality' => 'nullable|string|max:80',
            'dob' => 'nullable|date',
            'biography' => 'nullable|string|max:3000',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form fields.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $author = LibraryAuthor::create([
                'name' => $request->name,
                'nationality' => $request->nationality,
                'dob' => $request->dob,
                'biography' => $request->biography,
                'phone' => $request->phone,
                'email' => $request->email,
                'website' => $request->website,
                'created_by' => auth()->id(),
                'is_active' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Author created successfully.',
                'data' => $author
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Author creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create author: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update author
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to update authors.'
            ], 403);
        }

        $author = LibraryAuthor::find($id);

        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Author not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150|unique:library_authors,name,' . $id,
            'nationality' => 'nullable|string|max:80',
            'dob' => 'nullable|date',
            'biography' => 'nullable|string|max:3000',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form fields.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $author->update([
                'name' => $request->name,
                'nationality' => $request->nationality,
                'dob' => $request->dob,
                'biography' => $request->biography,
                'phone' => $request->phone,
                'email' => $request->email,
                'website' => $request->website,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Author updated successfully.',
                'data' => $author
            ]);

        } catch (\Exception $e) {
            \Log::error('Author update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update author: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete author (deactivate)
     */
    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to delete authors.'
            ], 403);
        }

        $author = LibraryAuthor::find($id);

        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Author not found.'
            ], 404);
        }

        try {
            $author->update([
                'is_active' => 0,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Author deactivated successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Author deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate author: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle author status
     */
    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $author = LibraryAuthor::find($id);

        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Author not found.'
            ], 404);
        }

        try {
            $author->is_active = !$author->is_active;
            $author->updated_by = auth()->id();
            $author->save();

            return response()->json([
                'success' => true,
                'message' => 'Author status updated successfully.',
                'data' => $author
            ]);

        } catch (\Exception $e) {
            \Log::error('Author status toggle failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update author status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete author (permanent)
     * Admin only
     */
    public function forceDelete($id)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can permanently delete authors.'
            ], 403);
        }

        $author = LibraryAuthor::find($id);

        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Author not found.'
            ], 404);
        }

        try {
            $author->delete();

            return response()->json([
                'success' => true,
                'message' => 'Author permanently deleted successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Author force delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete author: ' . $e->getMessage()
            ], 500);
        }
    }
}


