<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryItem;
use App\Models\LibraryAuthor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class LibraryItemAuthorController extends Controller
{
    /**
     * Permission helper methods
     */
    private function canRead()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager', 'staff', 'principal']);
    }

    private function canWrite()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Get DataTable data for authors of a specific item
     */
    public function index($itemId)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $item = LibraryItem::findOrFail($itemId);

        $query = DB::table('library_author_item')
            ->join('library_authors', 'library_author_item.author_id', '=', 'library_authors.id')
            ->where('library_author_item.library_item_id', $itemId)
            ->select([
                'library_author_item.id as pivot_id',
                'library_author_item.author_id',
                'library_author_item.role',
                'library_author_item.is_active as pivot_active',
                'library_author_item.created_at',
                'library_authors.name as author_name',
                'library_authors.phone',
                'library_authors.email',
                'library_authors.is_active as author_active'
            ]);

        return DataTables::of($query)
            ->addColumn('role_badge', function ($row) {
                $colors = [
                    'author' => 'primary',
                    'editor' => 'info',
                    'translator' => 'success',
                    'illustrator' => 'warning',
                    'contributor' => 'secondary'
                ];
                $color = $colors[$row->role] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($row->role) . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                if (!$row->author_active) {
                    return '<span class="badge bg-danger">Author Inactive</span>';
                }
                if (!$row->pivot_active) {
                    return '<span class="badge bg-warning">Detached</span>';
                }
                return '<span class="badge bg-success">Active</span>';
            })
            ->addColumn('actions', function ($row) use ($itemId) {
                $actions = '<div class="btn-group btn-group-sm" role="group">';
                
                if ($this->canWrite()) {
                    // Toggle status
                    $toggleIcon = $row->pivot_active ? 'fa-toggle-on' : 'fa-toggle-off';
                    $toggleColor = $row->pivot_active ? 'success' : 'secondary';
                    $toggleTitle = $row->pivot_active ? 'Detach' : 'Re-attach';
                    
                    $actions .= '<button type="button" class="btn btn-' . $toggleColor . '" onclick="toggleAuthorStatus(' . $itemId . ', ' . $row->author_id . ', \'' . $row->role . '\')" title="' . $toggleTitle . '">
                                    <i class="fas ' . $toggleIcon . '"></i>
                                </button>';
                    
                    // Edit role
                    $actions .= '<button type="button" class="btn btn-warning" onclick="editAuthorRole(' . $itemId . ', ' . $row->author_id . ', \'' . $row->role . '\')" title="Edit Role">
                                    <i class="fas fa-edit"></i>
                                </button>';
                    
                    // Remove (soft delete)
                    $actions .= '<button type="button" class="btn btn-danger" onclick="removeAuthor(' . $itemId . ', ' . $row->author_id . ', \'' . $row->role . '\')" title="Remove">
                                    <i class="fas fa-trash"></i>
                                </button>';
                }
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['role_badge', 'status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Attach author to item (or reactivate if exists)
     */
    public function store(Request $request, $itemId)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'author_id' => 'required|exists:library_authors,id',
            'role' => 'required|in:author,editor,translator,illustrator,contributor',
        ]);

        try {
            $item = LibraryItem::findOrFail($itemId);
            $author = LibraryAuthor::findOrFail($validated['author_id']);

            DB::beginTransaction();

            // Check if pivot already exists
            $existing = DB::table('library_author_item')
                ->where('library_item_id', $itemId)
                ->where('author_id', $validated['author_id'])
                ->where('role', $validated['role'])
                ->first();

            if ($existing) {
                // Reactivate if inactive
                if (!$existing->is_active) {
                    DB::table('library_author_item')
                        ->where('library_item_id', $itemId)
                        ->where('author_id', $validated['author_id'])
                        ->where('role', $validated['role'])
                        ->update([
                            'is_active' => 1,
                            'updated_by' => Auth::id(),
                            'updated_at' => now()
                        ]);

                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'message' => 'Author re-attached successfully!'
                    ]);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'This author is already attached with this role.'
                    ], 400);
                }
            }

            // Create new pivot entry
            DB::table('library_author_item')->insert([
                'library_item_id' => $itemId,
                'author_id' => $validated['author_id'],
                'role' => $validated['role'],
                'is_active' => 1,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Author attached successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error attaching author: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to attach author: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update author role
     */
    public function update(Request $request, $itemId)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'author_id' => 'required|exists:library_authors,id',
            'old_role' => 'required|in:author,editor,translator,illustrator,contributor',
            'new_role' => 'required|in:author,editor,translator,illustrator,contributor',
        ]);

        try {
            DB::beginTransaction();

            // Check if target role already exists
            if ($validated['old_role'] !== $validated['new_role']) {
                $targetExists = DB::table('library_author_item')
                    ->where('library_item_id', $itemId)
                    ->where('author_id', $validated['author_id'])
                    ->where('role', $validated['new_role'])
                    ->exists();

                if ($targetExists) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'This author already has the role: ' . $validated['new_role']
                    ], 400);
                }
            }

            // Update the role
            $updated = DB::table('library_author_item')
                ->where('library_item_id', $itemId)
                ->where('author_id', $validated['author_id'])
                ->where('role', $validated['old_role'])
                ->update([
                    'role' => $validated['new_role'],
                    'updated_by' => Auth::id(),
                    'updated_at' => now()
                ]);

            if (!$updated) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Author role not found.'
                ], 404);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Author role updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating author role: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update author role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle author status (attach/detach)
     */
    public function toggleStatus(Request $request, $itemId)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'author_id' => 'required|exists:library_authors,id',
            'role' => 'required|in:author,editor,translator,illustrator,contributor',
        ]);

        try {
            $pivot = DB::table('library_author_item')
                ->where('library_item_id', $itemId)
                ->where('author_id', $validated['author_id'])
                ->where('role', $validated['role'])
                ->first();

            if (!$pivot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Author relationship not found.'
                ], 404);
            }

            $newStatus = !$pivot->is_active;

            DB::table('library_author_item')
                ->where('library_item_id', $itemId)
                ->where('author_id', $validated['author_id'])
                ->where('role', $validated['role'])
                ->update([
                    'is_active' => $newStatus ? 1 : 0,
                    'updated_by' => Auth::id(),
                    'updated_at' => now()
                ]);

            $message = $newStatus ? 'Author re-attached successfully!' : 'Author detached successfully!';

            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $newStatus
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling author status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle author status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove author (soft delete)
     */
    public function destroy(Request $request, $itemId)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'author_id' => 'required|exists:library_authors,id',
            'role' => 'required|in:author,editor,translator,illustrator,contributor',
        ]);

        try {
            $updated = DB::table('library_author_item')
                ->where('library_item_id', $itemId)
                ->where('author_id', $validated['author_id'])
                ->where('role', $validated['role'])
                ->update([
                    'is_active' => 0,
                    'updated_by' => Auth::id(),
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Author relationship not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Author removed successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing author: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove author: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search authors for AJAX select (used in add author modal)
     */
    public function searchAuthors(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $query = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        try {
            $authorsQuery = LibraryAuthor::where('is_active', 1);

            if (!empty($query)) {
                $authorsQuery->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('phone', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                });
            }

            $authors = $authorsQuery->orderBy('name')
                ->skip(($page - 1) * $perPage)
                ->take($perPage + 1)
                ->get();

            $hasMore = $authors->count() > $perPage;
            if ($hasMore) {
                $authors = $authors->take($perPage);
            }

            $results = $authors->map(function ($author) {
                $text = $author->name;
                if ($author->phone) {
                    $text .= ' (' . $author->phone . ')';
                }
                return [
                    'id' => $author->id,
                    'text' => $text,
                    'name' => $author->name,
                    'phone' => $author->phone,
                    'email' => $author->email
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
            Log::error('Error searching authors: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search authors.',
                'results' => []
            ], 500);
        }
    }
}
