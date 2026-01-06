<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SampleUserTableController extends Controller
{
    /**
     * Display the users page with DataTable
     */
    public function index()
    {
        return view('admin.sample_users.index');
    }

    /**
     * Return JSON data for DataTables Ajax
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with('roles')->select('users.*');
            
            return DataTables::of($users)
                ->addIndexColumn() // Adds DT_RowIndex
                ->addColumn('roles', function ($user) {
                    return $user->roles->pluck('name')->implode(', ');
                })
                ->addColumn('status', function ($user) {
                    return $user->is_active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($user) {
                    $editBtn = '<button class="btn btn-sm btn-primary edit-btn" data-id="'.$user->id.'">Edit</button>';
                    $deleteBtn = '<button class="btn btn-sm btn-danger delete-btn" data-id="'.$user->id.'">Delete</button>';
                    return $editBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['status', 'action']) // Allow HTML in these columns
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Delete user (example endpoint)
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }
}

