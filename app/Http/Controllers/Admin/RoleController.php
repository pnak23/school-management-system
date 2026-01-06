<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     * Returns JSON if Ajax request, otherwise returns view.
     *
     * @param Request $request
     * @return JsonResponse|View
     */
    public function index(Request $request)
    {
        // Check if request is Ajax or expects JSON
        if ($request->wantsJson() || $request->ajax()) {
            $roles = Role::withCount('users')
                ->select('id', 'name', 'description', 'is_active', 'created_at', 'updated_at')
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'description' => $role->description,
                        'is_active' => $role->is_active,
                        'users_count' => $role->users_count,
                        'created_at' => $role->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $role->updated_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $roles,
                'message' => 'Roles retrieved successfully'
            ]);
        }

        // Return Blade view for non-Ajax requests
        return view('admin.roles.index');
    }

    /**
     * Show the form for creating a new role.
     *
     * @return View
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created role in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role = Role::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? 1,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description,
                    'is_active' => $role->is_active,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $role = Role::with(['users', 'creator', 'updater'])
                ->withCount('users')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description,
                    'is_active' => $role->is_active,
                    'users_count' => $role->users_count,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                    'users' => $role->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                        ];
                    }),
                    'creator' => $role->creator ? $role->creator->name : null,
                    'updater' => $role->updater ? $role->updater->name : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Update the specified role in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            // Validation rules
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:roles,name,' . $id,
                'description' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? $role->is_active,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description,
                    'is_active' => $role->is_active,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft deactivate the specified role (set is_active = 0).
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            // Check if role has users
            $usersCount = $role->users()->count();
            if ($usersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot deactivate role. It is assigned to {$usersCount} user(s)."
                ], 400);
            }

            // Soft deactivate by setting is_active = 0
            $role->update([
                'is_active' => 0,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a deactivated role (set is_active = 1).
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            $role->update([
                'is_active' => 1,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role activated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign role to multiple users.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function assignUsers(Request $request, $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            foreach ($request->user_ids as $userId) {
                if (!$role->users()->where('user_id', $userId)->exists()) {
                    $role->users()->attach($userId, [
                        'created_by' => auth()->id(),
                        'is_active' => 1,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Users assigned to role successfully',
                'assigned_count' => count($request->user_ids)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign users',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
