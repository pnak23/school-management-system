<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PositionController extends Controller
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
            
            $query = Position::with(['creator', 'updater']);
            
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
            
            $positions = $query->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $positions
            ]);
        }

        // Return Blade view
        return view('admin.positions.index');
    }

    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50|unique:positions,code',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = true;

        $position = Position::create($validated);
        $position->load(['creator', 'updater']);

        return response()->json([
            'success' => true,
            'message' => 'Position created successfully',
            'data' => $position
        ], 201);
    }

    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $position = Position::with(['creator', 'updater'])->find($id);

        if (!$position) {
            return response()->json(['error' => 'Position not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $position
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $position = Position::find($id);

        if (!$position) {
            return response()->json(['error' => 'Position not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50|unique:positions,code,' . $id,
            'description' => 'nullable|string',
        ]);

        $validated['updated_by'] = Auth::id();

        $position->update($validated);
        $position->load(['creator', 'updater']);

        return response()->json([
            'success' => true,
            'message' => 'Position updated successfully',
            'data' => $position
        ]);
    }

    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $position = Position::find($id);

        if (!$position) {
            return response()->json(['error' => 'Position not found'], 404);
        }

        // Soft delete
        $position->is_active = false;
        $position->updated_by = Auth::id();
        $position->save();

        return response()->json([
            'success' => true,
            'message' => 'Position deactivated successfully'
        ]);
    }

    public function restore($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $position = Position::find($id);

        if (!$position) {
            return response()->json(['error' => 'Position not found'], 404);
        }

        $position->is_active = true;
        $position->updated_by = Auth::id();
        $position->save();

        return response()->json([
            'success' => true,
            'message' => 'Position restored successfully'
        ]);
    }

    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $position = Position::find($id);

        if (!$position) {
            return response()->json(['error' => 'Position not found'], 404);
        }

        $position->is_active = !$position->is_active;
        $position->updated_by = Auth::id();
        $position->save();

        return response()->json([
            'success' => true,
            'message' => 'Position status updated successfully',
            'data' => $position
        ]);
    }
}
