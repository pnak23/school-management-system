<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    use LogsActivity;
    /**
     * Display a listing of the users.
     * Returns DataTables JSON if Ajax request, otherwise returns view.
     *
     * @param Request $request
     * @return JsonResponse|View
     */
    public function index(Request $request)
    {
        // DataTables server-side processing
        if ($request->ajax()) {
            $query = User::with('roles')
                ->select('users.*');

            // Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by is_active
            if ($request->filled('is_active') && $request->is_active !== 'all') {
                if ($request->is_active === 'active') {
                    $query->where('is_active', 1);
                } elseif ($request->is_active === 'inactive') {
                    $query->where('is_active', 0);
                }
            }

            // Filter by role
            if ($request->filled('role') && $request->role !== 'all') {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('roles.id', $request->role);
                });
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
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                        });
                    }
                })
                ->editColumn('created_at', function ($user) {
                    return $user->created_at ? $user->created_at->format('Y-m-d H:i') : '-';
                })
                ->addColumn('user_info', function ($user) {
                    $profilePic = $user->profile_picture 
                        ? '<img src="' . asset('storage/' . $user->profile_picture) . '" alt="' . e($user->name) . '" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">
                           <div class="rounded-circle bg-primary text-white d-none align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                               <span class="fw-bold">' . strtoupper(substr($user->name, 0, 1)) . '</span>
                           </div>'
                        : '<div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; min-width: 40px;">
                               <span class="fw-bold">' . strtoupper(substr($user->name, 0, 1)) . '</span>
                           </div>';
                    
                    return $profilePic . '<div class="d-inline-block">
                        <div class="fw-bold">' . e($user->name) . '</div>
                        <small class="text-muted">' . e($user->email) . '</small>
                    </div>';
                })
                ->addColumn('status_badge', function ($user) {
                    $badgeClass = $user->status === 'active' ? 'bg-success' : 
                                 ($user->status === 'inactive' ? 'bg-warning' : 'bg-danger');
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($user->status) . '</span>';
                })
                ->addColumn('roles_badge', function ($user) {
                    if ($user->roles->isEmpty()) {
                        return '<span class="text-muted small">No roles</span>';
                    }
                    return $user->roles->map(function ($role) {
                        return '<span class="badge bg-info me-1">' . e($role->name) . '</span>';
                    })->join('');
                })
                ->addColumn('active_toggle', function ($user) {
                    $checked = $user->is_active ? 'checked' : '';
                    return '<div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" 
                               onchange="toggleActive(' . $user->id . ', ' . ($user->is_active ? 1 : 0) . ')" 
                               ' . $checked . '>
                    </div>';
                })
                ->addColumn('actions', function ($user) {
                    $actions = '<div class="btn-group" role="group">';
                    $actions .= '<button type="button" class="btn btn-sm btn-info" onclick="viewUser(' . $user->id . ')" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>';
                    $actions .= '<button type="button" class="btn btn-sm btn-primary" onclick="editUser(' . $user->id . ')" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>';
                    $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(' . $user->id . ')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>';
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['user_info', 'status_badge', 'roles_badge', 'active_toggle', 'actions'])
                ->make(true);
        }

        // Return Blade view for non-Ajax requests
        $roles = Role::active()->get();
        return view('admin.users.index', compact('roles'));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return View
     */
    public function create()
    {
        $roles = Role::active()->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:3|confirmed',
            'status' => 'nullable|in:active,inactive,banned',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Handle file uploads
            $profilePicturePath = null;
            $coverPhotoPath = null;

            if ($request->hasFile('profile_picture')) {
                $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            }

            if ($request->hasFile('cover_photo')) {
                $coverPhotoPath = $request->file('cover_photo')->store('cover_photos', 'public');
            }

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => $request->status ?? 'active',
                'profile_picture' => $profilePicturePath,
                'cover_photo' => $coverPhotoPath,
                'is_active' => 1,
                'created_by' => auth()->id(),
            ]);

            // Assign roles if provided
            $roleIds = [];
            if ($request->has('roles') && is_array($request->roles)) {
                foreach ($request->roles as $roleId) {
                    $user->roles()->attach($roleId, [
                        'created_by' => auth()->id(),
                        'is_active' => 1,
                    ]);
                    $roleIds[] = $roleId;
                }
            }

            // Get role names for logging
            $user->load('roles');
            $roleNames = $user->roles->pluck('name')->toArray();

            // Log activity
            $this->logActivity(
                "User created: {$user->name} ({$user->email})",
                $user,
                [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'is_active' => $user->is_active,
                    'role_ids' => $roleIds,
                    'role_names' => $roleNames,
                    'profile_picture' => $user->profile_picture ? 'Uploaded' : null,
                    'cover_photo' => $user->cover_photo ? 'Uploaded' : null,
                    'created_by' => auth()->id()
                ],
                'users'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'profile_picture' => $user->profile_picture,
                    'cover_photo' => $user->cover_photo,
                    'is_active' => $user->is_active,
                    'roles' => $user->roles->pluck('name'),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::with(['roles', 'creator', 'updater'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'profile_picture' => $user->profile_picture,
                    'cover_photo' => $user->cover_photo,
                    'is_active' => $user->is_active,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'description' => $role->description,
                        ];
                    }),
                    'creator' => $user->creator ? $user->creator->name : null,
                    'updater' => $user->updater ? $user->updater->name : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::active()->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Validation rules
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $id,
                'password' => 'nullable|string|min:3|confirmed',
                'status' => 'nullable|in:active,inactive,banned',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_active' => 'nullable|boolean',
                'roles' => 'nullable|array',
                'roles.*' => 'exists:roles,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Store old values for logging
            $oldAttributes = [
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'is_active' => $user->is_active,
                'profile_picture' => $user->profile_picture,
                'cover_photo' => $user->cover_photo
            ];

            // Get old roles
            $oldRoleIds = $user->roles->pluck('id')->toArray();
            $oldRoleNames = $user->roles->pluck('name')->toArray();

            // Handle file uploads
            $profilePicturePath = $user->profile_picture;
            $coverPhotoPath = $user->cover_photo;

            if ($request->hasFile('profile_picture')) {
                // Delete old file if exists
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            }

            if ($request->hasFile('cover_photo')) {
                // Delete old file if exists
                if ($user->cover_photo && Storage::disk('public')->exists($user->cover_photo)) {
                    Storage::disk('public')->delete($user->cover_photo);
                }
                $coverPhotoPath = $request->file('cover_photo')->store('cover_photos', 'public');
            }

            // Update user data
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status ?? $user->status,
                'profile_picture' => $profilePicturePath,
                'cover_photo' => $coverPhotoPath,
                'updated_by' => auth()->id(),
            ];

            // Update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Update is_active if provided
            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->is_active;
            }

            $user->update($updateData);

            // Sync roles if provided
            $newRoleIds = [];
            if ($request->has('roles')) {
                $user->roles()->detach();
                
                if (is_array($request->roles) && count($request->roles) > 0) {
                    foreach ($request->roles as $roleId) {
                        $user->roles()->attach($roleId, [
                            'created_by' => auth()->id(),
                            'is_active' => 1,
                        ]);
                        $newRoleIds[] = $roleId;
                    }
                }
            }

            // Reload relationships
            $user->load('roles');
            $newRoleNames = $user->roles->pluck('name')->toArray();

            // Prepare new attributes for logging
            $newAttributes = [
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'is_active' => $user->is_active,
                'profile_picture' => $user->profile_picture,
                'cover_photo' => $user->cover_photo,
                'role_ids' => $newRoleIds,
                'role_names' => $newRoleNames,
                'password_changed' => $request->filled('password')
            ];

            // Add role changes to old attributes if changed
            if ($oldRoleIds !== $newRoleIds) {
                $oldAttributes['role_ids'] = $oldRoleIds;
                $oldAttributes['role_names'] = $oldRoleNames;
            }

            // Log activity with old and new values
            $this->logActivityUpdate(
                "User updated: {$user->name} ({$user->email})",
                $user,
                $oldAttributes,
                $newAttributes,
                'users'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'is_active' => $user->is_active,
                    'roles' => $user->roles->pluck('name'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft deactivate the specified user (set is_active = 0).
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Prevent self-deletion
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot deactivate your own account'
                ], 403);
            }

            // Store old status
            $oldIsActive = $user->is_active;

            // Soft deactivate by setting is_active = 0
            $user->update([
                'is_active' => 0,
                'updated_by' => auth()->id(),
            ]);

            // Log activity
            $this->logActivity(
                "User deactivated: {$user->name} ({$user->email})",
                $user,
                [
                    'old_status' => $oldIsActive ? 'Active' : 'Inactive',
                    'new_status' => 'Inactive',
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'deactivated_by' => auth()->id()
                ],
                'users'
            );

            return response()->json([
                'success' => true,
                'message' => 'User deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a deactivated user (set is_active = 1).
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Store old status
            $oldIsActive = $user->is_active;

            $user->update([
                'is_active' => 1,
                'updated_by' => auth()->id(),
            ]);

            // Log activity
            $this->logActivity(
                "User activated: {$user->name} ({$user->email})",
                $user,
                [
                    'old_status' => $oldIsActive ? 'Active' : 'Inactive',
                    'new_status' => 'Active',
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'activated_by' => auth()->id()
                ],
                'users'
            );

            return response()->json([
                'success' => true,
                'message' => 'User activated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics (for dashboard cards)
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $total = User::count();
            $active = User::where('is_active', 1)->count();
            $inactive = User::where('is_active', 0)->count();
            $withRoles = User::whereHas('roles')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'active' => $active,
                    'inactive' => $inactive,
                    'with_roles' => $withRoles
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
