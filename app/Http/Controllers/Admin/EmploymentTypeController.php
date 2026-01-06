<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmploymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmploymentTypeController extends Controller
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
            
            $query = EmploymentType::with(['creator', 'updater']);
            
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
            
            $types = $query->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $types
            ]);
        }

        // Return Blade view
        return view('admin.employment-types.index');
    }

    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50|unique:employment_types,code',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = true;

        $type = EmploymentType::create($validated);
        $type->load(['creator', 'updater']);

        return response()->json([
            'success' => true,
            'message' => 'Employment type created successfully',
            'data' => $type
        ], 201);
    }

    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $type = EmploymentType::with(['creator', 'updater'])->find($id);

        if (!$type) {
            return response()->json(['error' => 'Employment type not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $type
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $type = EmploymentType::find($id);

        if (!$type) {
            return response()->json(['error' => 'Employment type not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50|unique:employment_types,code,' . $id,
            'description' => 'nullable|string',
        ]);

        $validated['updated_by'] = Auth::id();

        $type->update($validated);
        $type->load(['creator', 'updater']);

        return response()->json([
            'success' => true,
            'message' => 'Employment type updated successfully',
            'data' => $type
        ]);
    }

    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $type = EmploymentType::find($id);

        if (!$type) {
            return response()->json(['error' => 'Employment type not found'], 404);
        }

        // Soft delete
        $type->is_active = false;
        $type->updated_by = Auth::id();
        $type->save();

        return response()->json([
            'success' => true,
            'message' => 'Employment type deactivated successfully'
        ]);
    }

    public function restore($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $type = EmploymentType::find($id);

        if (!$type) {
            return response()->json(['error' => 'Employment type not found'], 404);
        }

        $type->is_active = true;
        $type->updated_by = Auth::id();
        $type->save();

        return response()->json([
            'success' => true,
            'message' => 'Employment type restored successfully'
        ]);
    }

    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $type = EmploymentType::find($id);

        if (!$type) {
            return response()->json(['error' => 'Employment type not found'], 404);
        }

        $type->is_active = !$type->is_active;
        $type->updated_by = Auth::id();
        $type->save();

        return response()->json([
            'success' => true,
            'message' => 'Employment type status updated successfully',
            'data' => $type
        ]);
    }
}

