<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryItem;
use App\Models\LibraryCategory;
use App\Models\LibraryPublisher;
use App\Models\LibraryAuthor;
use App\Models\LibraryShelf;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class LibraryItemController extends Controller
{
    use LogsActivity;
    // Permission helper methods
    private function canRead(): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'manager', 'staff', 'principal']);
    }

    private function canWrite(): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'manager', 'staff']);
    }

    private function canDelete(): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'manager']);
    }

    private function isAdmin(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    /**
     * Display listing of items (Ajax + View)
     */
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to view library items.');
        }

        // Handle DataTables Ajax request
        if ($request->ajax()) {
            $query = LibraryItem::with(['category', 'publisher', 'authors'])->select('library_items.*');

            // Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                if ($request->status === 'active') {
                    $query->where('is_active', 1);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', 0);
                }
            }

            // Filter by category
            if ($request->filled('category') && $request->category !== 'all') {
                $query->where('category_id', $request->category);
            }

            // Filter by publisher
            if ($request->filled('publisher') && $request->publisher !== 'all') {
                $query->where('publisher_id', $request->publisher);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%")
                      ->orWhere('edition', 'like', "%{$search}%")
                      ->orWhere('language', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
                })
                ->addColumn('cover_display', function ($item) {
                    if ($item->cover_image) {
                        $coverUrl = asset('storage/' . $item->cover_image);
                        return '<img src="' . $coverUrl . '" alt="Cover" class="rounded" style="width: 50px; height: 70px; object-fit: cover;">';
                    }
                    return '<div class="bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 70px;"><i class="fas fa-book"></i></div>';
                })
                ->addColumn('category_name', function ($item) {
                    return $item->category ? e($item->category->name) : '-';
                })
                ->addColumn('publisher_name', function ($item) {
                    return $item->publisher ? e($item->publisher->name) : '-';
                })
                ->addColumn('authors_display', function ($item) {
                    $authors = $item->authors->pluck('name')->toArray();
                    return !empty($authors) ? implode(', ', array_map('e', $authors)) : '-';
                })
                ->addColumn('status_badge', function ($item) {
                    if ($item->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    }
                    return '<span class="badge bg-danger">Inactive</span>';
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at ? $item->created_at->format('Y-m-d') : '-';
                })
                ->addColumn('actions', function ($item) {
                    $buttons = '';
                    
                    // View button
                    $buttons .= '<button class="btn btn-sm btn-info btn-view-item me-1" data-id="'.$item->id.'" title="View">
                        <i class="fas fa-eye"></i>
                    </button>';
                    
                    if (auth()->user()->hasAnyRole(['admin', 'manager', 'staff'])) {
                        // Edit button
                        $buttons .= '<button class="btn btn-sm btn-primary btn-edit-item me-1" data-id="'.$item->id.'" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>';
                        
                        // Toggle status button
                        if ($item->is_active) {
                            $buttons .= '<button class="btn btn-sm btn-warning btn-toggle-status me-1" data-id="'.$item->id.'" title="Deactivate">
                                <i class="fas fa-ban"></i>
                            </button>';
                        } else {
                            $buttons .= '<button class="btn btn-sm btn-success btn-toggle-status me-1" data-id="'.$item->id.'" title="Activate">
                                <i class="fas fa-check"></i>
                            </button>';
                        }
                    }
                    
                    if (auth()->user()->hasAnyRole(['admin', 'manager'])) {
                        // Delete button
                        $buttons .= '<button class="btn btn-sm btn-danger btn-delete-item" data-id="'.$item->id.'" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $buttons;
                })
                ->rawColumns(['cover_display', 'status_badge', 'actions'])
                ->make(true);
        }

        // Load data for filters
        $categories = LibraryCategory::where('is_active', 1)->orderBy('name')->get();
        $publishers = LibraryPublisher::where('is_active', 1)->orderBy('name')->get();
        $authors = LibraryAuthor::where('is_active', 1)->orderBy('name')->get();

        return view('admin.library.items.index', compact('categories', 'publishers', 'authors'));
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
            $total = LibraryItem::count();
            $active = LibraryItem::where('is_active', 1)->count();
            $inactive = LibraryItem::where('is_active', 0)->count();
            $withCopies = LibraryItem::whereHas('copies')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'active' => $active,
                    'inactive' => $inactive,
                    'with_copies' => $withCopies,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching library items stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.'
            ], 500);
        }
    }

    /**
     * Show single item
     */
    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $item = LibraryItem::with(['category', 'publisher', 'authors'])->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Library item not found.'
            ], 404);
        }

        // Get authors with pivot data
        $authors = $item->authors->map(function ($author) {
            return [
                'id' => $author->id,
                'name' => $author->name,
                'role' => $author->pivot->role ?? 'author',
                'is_active' => $author->pivot->is_active ?? 1,
            ];
        });

        // Get copies count
        $copiesCount = $item->copies()->count();
        $availableCopies = $item->copies()->where('status', 'available')->where('is_active', 1)->count();

        // Get shelf information for copies
        $shelves = $item->copies()
            ->where('is_active', true)
            ->with('shelf:id,code,location')
            ->get()
            ->pluck('shelf')
            ->filter()
            ->unique('id')
            ->map(function($shelf) {
                return [
                    'id' => $shelf->id,
                    'code' => $shelf->code,
                    'location' => $shelf->location,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'title' => $item->title,
                'isbn' => $item->isbn,
                'edition' => $item->edition,
                'published_year' => $item->published_year,
                'language' => $item->language,
                'description' => $item->description,
                'cover_image' => $item->cover_image,
                'cover_image_url' => $item->cover_image_url,
                'category_id' => $item->category_id,
                'category_name' => $item->category ? $item->category->name : 'N/A',
                'publisher_id' => $item->publisher_id,
                'publisher_name' => $item->publisher ? $item->publisher->name : 'N/A',
                'authors' => $authors,
                'author_ids' => $authors->pluck('id')->toArray(), // For edit form
                'authors_count' => $authors->count(),
                'copies_count' => $copiesCount,
                'available_copies' => $availableCopies,
                'shelves' => $shelves,
                'is_active' => $item->is_active,
                'status' => $item->is_active ? 'Active' : 'Inactive',
                'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : null,
                'created_by' => $item->creator ? $item->creator->name : 'System',
                'updated_by' => $item->updater ? $item->updater->name : 'System',
            ]
        ]);
    }

    /**
     * Store new item
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to create library items.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'edition' => 'nullable|string|max:50',
            'published_year' => 'nullable|digits:4|integer|min:1000|max:2155',
            'language' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'required|exists:library_categories,id',
            'publisher_id' => 'nullable|exists:library_publishers,id',
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'exists:library_authors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form fields.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Handle cover image upload
            $coverImagePath = null;
            if ($request->hasFile('cover_image')) {
                $coverImagePath = $request->file('cover_image')->store('library_items', 'public');
            }

            $item = LibraryItem::create([
                'title' => $request->title,
                'isbn' => $request->isbn,
                'edition' => $request->edition,
                'published_year' => $request->published_year,
                'language' => $request->language,
                'description' => $request->description,
                'cover_image' => $coverImagePath,
                'category_id' => $request->category_id,
                'publisher_id' => $request->publisher_id,
                'created_by' => Auth::id(),
                'is_active' => 1,
            ]);

            // Sync authors (many-to-many)
            if ($request->has('author_ids') && is_array($request->author_ids)) {
                $item->authors()->sync($request->author_ids);
            }

            // Get author names for logging
            $authorNames = $item->authors->pluck('name')->toArray();

            // Log activity
            $this->logActivity(
                "Library item created: {$item->title}" . ($item->isbn ? " (ISBN: {$item->isbn})" : ""),
                $item,
                [
                    'title' => $item->title,
                    'isbn' => $item->isbn,
                    'edition' => $item->edition,
                    'published_year' => $item->published_year,
                    'language' => $item->language,
                    'category_id' => $item->category_id,
                    'publisher_id' => $item->publisher_id,
                    'author_ids' => $request->author_ids ?? [],
                    'author_names' => $authorNames,
                    'cover_image' => $item->cover_image ? 'Uploaded' : null,
                    'created_by' => Auth::id()
                ],
                'library_items'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Library item created successfully.',
                'data' => $item
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded cover image if creation failed
            if (isset($coverImagePath)) {
                Storage::disk('public')->delete($coverImagePath);
            }

            Log::error('Library item creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create library item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update item
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to update library items.'
            ], 403);
        }

        $item = LibraryItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Library item not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'edition' => 'nullable|string|max:50',
            'published_year' => 'nullable|digits:4|integer|min:1000|max:2155',
            'language' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'required|exists:library_categories,id',
            'publisher_id' => 'nullable|exists:library_publishers,id',
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'exists:library_authors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form fields.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Store old values for logging
            $oldAttributes = [
                'title' => $item->title,
                'isbn' => $item->isbn,
                'edition' => $item->edition,
                'published_year' => $item->published_year,
                'language' => $item->language,
                'description' => $item->description,
                'cover_image' => $item->cover_image,
                'category_id' => $item->category_id,
                'publisher_id' => $item->publisher_id,
                'is_active' => $item->is_active
            ];

            // Get old author IDs
            $oldAuthorIds = $item->authors->pluck('id')->toArray();
            $oldAuthorNames = $item->authors->pluck('name')->toArray();

            // Handle cover image upload
            $coverImagePath = $item->cover_image;
            if ($request->hasFile('cover_image')) {
                // Delete old image
                if ($item->cover_image) {
                    Storage::disk('public')->delete($item->cover_image);
                }
                
                $coverImagePath = $request->file('cover_image')->store('library_items', 'public');
            }

            $item->update([
                'title' => $request->title,
                'isbn' => $request->isbn,
                'edition' => $request->edition,
                'published_year' => $request->published_year,
                'language' => $request->language,
                'description' => $request->description,
                'cover_image' => $coverImagePath,
                'category_id' => $request->category_id,
                'publisher_id' => $request->publisher_id,
                'updated_by' => Auth::id(),
            ]);

            // Sync authors (many-to-many)
            if ($request->has('author_ids')) {
                $authorIds = is_array($request->author_ids) ? $request->author_ids : [];
                $item->authors()->sync($authorIds);
            }

            // Refresh to get updated authors
            $item->refresh();
            $newAuthorIds = $item->authors->pluck('id')->toArray();
            $newAuthorNames = $item->authors->pluck('name')->toArray();

            // Prepare new attributes for logging
            $newAttributes = [
                'title' => $item->title,
                'isbn' => $item->isbn,
                'edition' => $item->edition,
                'published_year' => $item->published_year,
                'language' => $item->language,
                'description' => $item->description,
                'cover_image' => $item->cover_image,
                'category_id' => $item->category_id,
                'publisher_id' => $item->publisher_id,
                'is_active' => $item->is_active,
                'author_ids' => $newAuthorIds,
                'author_names' => $newAuthorNames
            ];

            // Add author changes to old attributes
            if ($oldAuthorIds !== $newAuthorIds) {
                $oldAttributes['author_ids'] = $oldAuthorIds;
                $oldAttributes['author_names'] = $oldAuthorNames;
            }

            // Log activity with old and new values
            $this->logActivityUpdate(
                "Library item updated: {$item->title}" . ($item->isbn ? " (ISBN: {$item->isbn})" : ""),
                $item,
                $oldAttributes,
                $newAttributes,
                'library_items'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Library item updated successfully.',
                'data' => $item
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Library item update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update library item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete item (deactivate)
     */
    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to delete library items.'
            ], 403);
        }

        $item = LibraryItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Library item not found.'
            ], 404);
        }

        try {
            // Store old status
            $oldIsActive = $item->is_active;

            $item->update([
                'is_active' => 0,
                'updated_by' => Auth::id()
            ]);

            // Log activity
            $this->logActivity(
                "Library item deactivated: {$item->title}" . ($item->isbn ? " (ISBN: {$item->isbn})" : ""),
                $item,
                [
                    'old_status' => $oldIsActive ? 'Active' : 'Inactive',
                    'new_status' => 'Inactive',
                    'item_id' => $item->id,
                    'title' => $item->title,
                    'isbn' => $item->isbn
                ],
                'library_items'
            );

            return response()->json([
                'success' => true,
                'message' => 'Library item deactivated successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Library item deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate library item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle item status
     */
    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $item = LibraryItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Library item not found.'
            ], 404);
        }

        try {
            $item->is_active = !$item->is_active;
            $item->updated_by = Auth::id();
            $item->save();

            return response()->json([
                'success' => true,
                'message' => 'Library item status updated successfully.',
                'data' => $item
            ]);

        } catch (\Exception $e) {
            Log::error('Library item status toggle failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update library item status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete item (permanent)
     * Admin only
     * Check if item has copies or active loans
     */
    public function forceDelete($id)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can permanently delete library items.'
            ], 403);
        }

        $item = LibraryItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Library item not found.'
            ], 404);
        }

        try {
            // Check if item has copies
            $copiesCount = DB::table('library_copies')
                ->where('item_id', $id)
                ->count();

            if ($copiesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete: item has ' . $copiesCount . ' physical cop' . ($copiesCount > 1 ? 'ies' : 'y') . '.'
                ], 400);
            }

            // Check if item has active loans (if loans table exists)
            $loansCount = 0;
            if (Schema::hasTable('library_loans')) {
                $loansCount = DB::table('library_loans')
                    ->join('library_copies', 'library_loans.copy_id', '=', 'library_copies.id')
                    ->where('library_copies.item_id', $id)
                    ->whereNull('library_loans.returned_at')
                    ->count();

                if ($loansCount > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete: item has ' . $loansCount . ' active loan(s).'
                    ], 400);
                }
            }

            // Delete cover image if exists
            if ($item->cover_image) {
                Storage::disk('public')->delete($item->cover_image);
            }

            // Store item info for logging before deletion
            $itemInfo = [
                'id' => $item->id,
                'title' => $item->title,
                'isbn' => $item->isbn,
                'edition' => $item->edition,
                'published_year' => $item->published_year,
                'language' => $item->language,
                'category_id' => $item->category_id,
                'publisher_id' => $item->publisher_id,
                'cover_image' => $item->cover_image ? 'Had cover image' : null,
                'author_count' => $item->authors()->count()
            ];

            // Delete pivot relationships
            $item->authors()->detach();

            // Delete item
            $item->delete();

            // Log activity (after deletion, so we can't use $item as subject)
            $this->logActivity(
                "Library item permanently deleted: {$itemInfo['title']}" . ($itemInfo['isbn'] ? " (ISBN: {$itemInfo['isbn']})" : ""),
                null, // No subject since it's deleted
                [
                    'deleted_item' => $itemInfo,
                    'deleted_by' => Auth::id(),
                    'deleted_at' => now()->toDateTimeString()
                ],
                'library_items'
            );

            return response()->json([
                'success' => true,
                'message' => 'Library item permanently deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Library item force delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete library item: ' . $e->getMessage()
            ], 500);
        }
    }
}

