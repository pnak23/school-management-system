<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    private function canRead()
    {
        $user = Auth::user();
        return $user->hasAnyRole(['admin', 'manager', 'principal', 'staff']);
    }

    private function canWrite()
    {
        $user = Auth::user();
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    private function canDelete()
    {
        $user = Auth::user();
        return $user->hasAnyRole(['admin', 'manager']);
    }

    public function index(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // If Ajax request, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            $status = $request->get('status', 'all');
            
            $query = Department::with(['headTeacher', 'creator', 'updater']);
            
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
            
            $departments = $query->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $departments
            ]);
        }

        // Return Blade view
        $teachers = Teacher::active()->orderBy('english_name')->get(['id', 'english_name', 'khmer_name']);
        return view('admin.departments.index', compact('teachers'));
    }

    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'head_teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = true;

        $department = Department::create($validated);
        $department->load(['headTeacher', 'creator', 'updater']);

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully',
            'data' => $department
        ], 201);
    }

    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $department = Department::with(['headTeacher', 'creator', 'updater'])->find($id);

        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $department
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $department = Department::find($id);

        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50|unique:departments,code,' . $id,
            'description' => 'nullable|string',
            'head_teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $validated['updated_by'] = Auth::id();

        $department->update($validated);
        $department->load(['headTeacher', 'creator', 'updater']);

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully',
            'data' => $department
        ]);
    }

    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $department = Department::find($id);

        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        // Soft delete
        $department->is_active = false;
        $department->updated_by = Auth::id();
        $department->save();

        return response()->json([
            'success' => true,
            'message' => 'Department deactivated successfully'
        ]);
    }

    public function restore($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $department = Department::find($id);

        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $department->is_active = true;
        $department->updated_by = Auth::id();
        $department->save();

        return response()->json([
            'success' => true,
            'message' => 'Department restored successfully'
        ]);
    }

    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $department = Department::find($id);

        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $department->is_active = !$department->is_active;
        $department->updated_by = Auth::id();
        $department->save();

        return response()->json([
            'success' => true,
            'message' => 'Department status updated successfully',
            'data' => $department
        ]);
    }
}

