<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryGuest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LibraryGuestController extends Controller
{
    /**
     * Display a listing of guests (DataTables JSON or view)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $status = $request->get('status', 'active');
            
            $query = LibraryGuest::with(['user', 'creator', 'updater']);
            
            // Apply status filter
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
            // 'all' - no filter
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('full_name', function ($guest) {
                    return e($guest->full_name);
                })
                ->addColumn('phone', function ($guest) {
                    return $guest->phone ? e($guest->phone) : '<span class="text-muted">N/A</span>';
                })
                ->addColumn('id_card_no', function ($guest) {
                    return $guest->id_card_no ? e($guest->id_card_no) : '<span class="text-muted">N/A</span>';
                })
                ->addColumn('is_active', function ($guest) {
                    $checked = $guest->is_active ? 'checked' : '';
                    $canToggle = $this->canWrite();
                    
                    if ($canToggle) {
                        return '<div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" ' . $checked . ' 
                                onchange="toggleGuestActive(' . $guest->id . ')" 
                                title="Toggle Active Status">
                        </div>';
                    } else {
                        return '<span class="badge bg-' . ($guest->is_active ? 'success' : 'secondary') . '">'
                            . ($guest->is_active ? 'Active' : 'Inactive') . '</span>';
                    }
                })
                ->addColumn('created_at', function ($guest) {
                    return $guest->created_at ? $guest->created_at->format('Y-m-d H:i') : '';
                })
                ->addColumn('actions', function ($guest) {
                    $actions = '<div class="btn-group btn-group-sm" role="group">';
                    
                    // View button (all roles)
                    $actions .= '<button type="button" class="btn btn-info" onclick="viewGuest(' . $guest->id . ')" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>';
                    
                    // Edit button (not for principal)
                    if ($this->canWrite()) {
                        $actions .= '<button type="button" class="btn btn-primary" onclick="openEditModal(' . $guest->id . ')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>';
                    }
                    
                    // Delete button (admin/manager only)
                    if ($this->canDelete()) {
                        $actions .= '<button type="button" class="btn btn-danger" onclick="deleteGuest(' . $guest->id . ')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->filter(function ($query) use ($request) {
                    if ($search = $request->get('search')['value']) {
                        $query->where(function ($q) use ($search) {
                            $q->where('full_name', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%")
                              ->orWhere('id_card_no', 'like', "%{$search}%");
                        });
                    }
                })
                ->rawColumns(['phone', 'id_card_no', 'is_active', 'actions'])
                ->make(true);
        }
        
        return view('admin.library.guests.index');
    }

    /**
     * Show guest details (JSON)
     */
    public function show($id)
    {
        try {
            $guest = LibraryGuest::with(['user', 'creator', 'updater'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $guest->id,
                    'full_name' => $guest->full_name,
                    'phone' => $guest->phone,
                    'id_card_no' => $guest->id_card_no,
                    'user_id' => $guest->user_id,
                    'user' => $guest->user ? [
                        'id' => $guest->user->id,
                        'name' => $guest->user->name,
                        'email' => $guest->user->email
                    ] : null,
                    'note' => $guest->note,
                    'is_active' => $guest->is_active,
                    'created_by' => $guest->creator ? $guest->creator->name : 'N/A',
                    'updated_by' => $guest->updater ? $guest->updater->name : 'N/A',
                    'created_at' => $guest->created_at ? $guest->created_at->format('Y-m-d H:i:s') : '',
                    'updated_at' => $guest->updated_at ? $guest->updated_at->format('Y-m-d H:i:s') : '',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Guest not found.'
            ], 404);
        }
    }

    /**
     * Store a new guest
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'id_card_no' => 'nullable|string|max:100|unique:library_guests,id_card_no',
            'user_id' => 'nullable|exists:users,id',
            'note' => 'nullable|string|max:1000',
        ], [
            'full_name.required' => 'Full name is required.',
            'full_name.max' => 'Full name must not exceed 255 characters.',
            'phone.max' => 'Phone must not exceed 50 characters.',
            'id_card_no.max' => 'ID card number must not exceed 100 characters.',
            'id_card_no.unique' => 'This ID card number is already registered.',
            'note.max' => 'Note must not exceed 1000 characters.',
        ]);

        try {
            DB::beginTransaction();

            $validated['created_by'] = Auth::id();
            $validated['updated_by'] = Auth::id();
            $validated['is_active'] = true;

            $guest = LibraryGuest::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guest created successfully.',
                'data' => $guest
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create guest: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing guest
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $guest = LibraryGuest::find($id);
        if (!$guest) {
            return response()->json(['success' => false, 'message' => 'Guest not found.'], 404);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'id_card_no' => 'nullable|string|max:100|unique:library_guests,id_card_no,' . $id,
            'user_id' => 'nullable|exists:users,id',
            'note' => 'nullable|string|max:1000',
        ], [
            'full_name.required' => 'Full name is required.',
            'full_name.max' => 'Full name must not exceed 255 characters.',
            'phone.max' => 'Phone must not exceed 50 characters.',
            'id_card_no.max' => 'ID card number must not exceed 100 characters.',
            'id_card_no.unique' => 'This ID card number is already registered.',
            'note.max' => 'Note must not exceed 1000 characters.',
        ]);

        try {
            DB::beginTransaction();

            $validated['updated_by'] = Auth::id();
            $guest->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guest updated successfully.',
                'data' => $guest
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update guest: ' . $e->getMessage()
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
            $guest = LibraryGuest::findOrFail($id);
            $guest->is_active = false;
            $guest->updated_by = Auth::id();
            $guest->save();

            return response()->json([
                'success' => true,
                'message' => 'Guest deactivated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate guest.'
            ], 500);
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $guest = LibraryGuest::findOrFail($id);
            $guest->is_active = !$guest->is_active;
            $guest->updated_by = Auth::id();
            $guest->save();

            return response()->json([
                'success' => true,
                'message' => 'Guest status updated successfully.',
                'is_active' => $guest->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.'
            ], 500);
        }
    }

    /**
     * Permanent delete (admin only)
     */
    public function forceDelete($id)
    {
        if (!$this->canForceDelete()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Admin only.'], 403);
        }

        try {
            $guest = LibraryGuest::findOrFail($id);
            $guest->delete(); // Permanent delete

            return response()->json([
                'success' => true,
                'message' => 'Guest permanently deleted.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete guest.'
            ], 500);
        }
    }

    // Permission helpers
    protected function canWrite()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager', 'staff']);
    }

    protected function canDelete()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager']);
    }

    protected function canForceDelete()
    {
        return Auth::user()->hasRole('admin');
    }

    /**
     * Search users for Select2 dropdown (AJAX)
     */
    public function searchUsers(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $query = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        try {
            $usersQuery = \App\Models\User::where('is_active', 1);

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
            \Illuminate\Support\Facades\Log::error('Error searching users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search users.',
                'results' => []
            ], 500);
        }
    }
}







