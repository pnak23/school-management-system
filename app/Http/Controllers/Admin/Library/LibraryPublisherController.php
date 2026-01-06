<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LibraryPublisherController extends Controller
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
     * Display listing of publishers (Ajax + View)
     */
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to view publishers.');
        }

        if ($request->ajax()) {
            $query = LibraryPublisher::query();

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
                      ->orWhere('address', 'like', "%{$search}%")
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
            $publishers = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $publishers->items(),
                'pagination' => [
                    'current_page' => $publishers->currentPage(),
                    'last_page' => $publishers->lastPage(),
                    'per_page' => $publishers->perPage(),
                    'total' => $publishers->total(),
                ],
            ]);
        }

        return view('admin.library.publishers.index');
    }

    /**
     * Show single publisher
     */
    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $publisher = LibraryPublisher::find($id);

        if (!$publisher) {
            return response()->json([
                'success' => false,
                'message' => 'Publisher not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $publisher
        ]);
    }

    /**
     * Store new publisher
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to create publishers.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150|unique:library_publishers,name',
            'address' => 'nullable|string|max:255',
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
            $publisher = LibraryPublisher::create([
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'website' => $request->website,
                'created_by' => auth()->id(),
                'is_active' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Publisher created successfully.',
                'data' => $publisher
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Publisher creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create publisher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update publisher
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to update publishers.'
            ], 403);
        }

        $publisher = LibraryPublisher::find($id);

        if (!$publisher) {
            return response()->json([
                'success' => false,
                'message' => 'Publisher not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150|unique:library_publishers,name,' . $id,
            'address' => 'nullable|string|max:255',
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
            $publisher->update([
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'website' => $request->website,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Publisher updated successfully.',
                'data' => $publisher
            ]);

        } catch (\Exception $e) {
            \Log::error('Publisher update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update publisher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete publisher (deactivate)
     */
    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to delete publishers.'
            ], 403);
        }

        $publisher = LibraryPublisher::find($id);

        if (!$publisher) {
            return response()->json([
                'success' => false,
                'message' => 'Publisher not found.'
            ], 404);
        }

        try {
            $publisher->update([
                'is_active' => 0,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Publisher deactivated successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Publisher deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate publisher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle publisher status
     */
    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $publisher = LibraryPublisher::find($id);

        if (!$publisher) {
            return response()->json([
                'success' => false,
                'message' => 'Publisher not found.'
            ], 404);
        }

        try {
            $publisher->is_active = !$publisher->is_active;
            $publisher->updated_by = auth()->id();
            $publisher->save();

            return response()->json([
                'success' => true,
                'message' => 'Publisher status updated successfully.',
                'data' => $publisher
            ]);

        } catch (\Exception $e) {
            \Log::error('Publisher status toggle failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update publisher status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete publisher (permanent)
     * Admin only
     */
    public function forceDelete($id)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can permanently delete publishers.'
            ], 403);
        }

        $publisher = LibraryPublisher::find($id);

        if (!$publisher) {
            return response()->json([
                'success' => false,
                'message' => 'Publisher not found.'
            ], 404);
        }

        try {
            $publisher->delete();

            return response()->json([
                'success' => true,
                'message' => 'Publisher permanently deleted successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Publisher force delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete publisher: ' . $e->getMessage()
            ], 500);
        }
    }
}


