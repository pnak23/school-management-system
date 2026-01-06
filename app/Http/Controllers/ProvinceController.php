<?php

namespace App\Http\Controllers;

use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ProvinceController extends Controller
{
    private function ensureCanWrite(): void
    {
        $role = auth()->user()->role ?? 'member';
        if (!in_array($role, ['admin','manager'], true)) {
            abort(403);
        }
    }

    public function index()
    {
        $provinces = Province::orderBy('name_en')->paginate(15);
        return view('locations.provinces.index', compact('provinces'));
    }

    public function create()
    {
        return view('locations.provinces.create');
    }

    public function data(Request $request)
    {
        try {
            $query = Province::query();

            if (class_exists(\Yajra\DataTables\DataTables::class)) {
                return DataTables::of($query)
                    ->filter(function ($q) use ($request) {
                        $search = $request->input('search.value');
                        if (!empty($search)) {
                            $q->where(function($inner) use ($search){
                                $inner->where('name_en', 'like', "%{$search}%")
                                      ->orWhere('name_km', 'like', "%{$search}%")
                                      ->orWhere('code', 'like', "%{$search}%");
                            });
                        }
                    })
                    ->addColumn('actions', function(Province $p){
                        $canWrite = in_array(auth()->user()->role ?? 'member', ['admin','manager'], true);
                        $view = '<button class="text-indigo-600" data-action="view" data-id="'.$p->id.'">👁️</button>';
                        $edit = $canWrite ? '<button class="text-yellow-600" data-action="edit" data-id="'.$p->id.'">✏️</button>' : '';
                        $del  = $canWrite ? '<button class="text-red-600" data-action="delete" data-id="'.$p->id.'">🗑️</button>' : '';
                        return '<div class="flex gap-2">'.$view.$edit.$del.'</div>';
                    })
                    ->rawColumns(['actions'])
                    ->make(true);
            }

            // Manual fallback if Yajra isn't available
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');

            if (!empty($search)) {
                $query->where(function($q) use ($search){
                    $q->where('name_en','like',"%{$search}%")
                      ->orWhere('name_km','like',"%{$search}%")
                      ->orWhere('code','like',"%{$search}%");
                });
            }

            $orderColumnIndex = (int) ($request->input('order.0.column', 0));
            $orderDir = $request->input('order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';
            $columns = ['id','name_en','name_km','code'];
            $orderBy = $columns[$orderColumnIndex] ?? 'id';

            $total = Province::count();
            $filtered = (clone $query)->count();

            $rows = $query->orderBy($orderBy, $orderDir)
                ->skip($start)->take($length)->get()
                ->map(function(Province $p){
                    $canWrite = in_array(auth()->user()->role ?? 'member', ['admin','manager'], true);
                    $actions = '<div class="flex gap-2">'
                        .'<button class="text-indigo-600" data-action="view" data-id="'.$p->id.'">👁️</button>'
                        .($canWrite?'<button class="text-yellow-600" data-action="edit" data-id="'.$p->id.'">✏️</button>':'')
                        .($canWrite?'<button class="text-red-600" data-action="delete" data-id="'.$p->id.'">🗑️</button>':'')
                        .'</div>';
                    return [
                        'id'=>$p->id,
                        'name_en'=>$p->name_en,
                        'name_km'=>$p->name_km,
                        'code'=>$p->code,
                        'actions'=>$actions,
                    ];
                });

            return response()->json([
                'draw'=>$draw,
                'recordsTotal'=>$total,
                'recordsFiltered'=>$filtered,
                'data'=>$rows,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Provinces data error', ['error'=>$e->getMessage()]);
            return response()->json(['error'=>'Server error: '.$e->getMessage()], 500);
        }
    }

    public function show(Province $province)
    {
        return response()->json(['ok' => true, 'data' => $province]);
    }

    public function edit(Province $province)
    {
        return view('locations.provinces.edit', compact('province'));
    }

    public function store(Request $request)
    {
        // Support both form and API requests
        $validated = $request->validate([
            'name_en' => ['required','string','max:100','unique:provinces,name_en'],
            'name_km' => ['nullable','string','max:100'],
            'code'    => ['nullable','string','max:10','unique:provinces,code'],
        ]);
        
        $p = Province::create($validated);
        
        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Created', 'data' => $p]);
        }
        
        // Redirect for form submissions
        return redirect()->route('provinces.index')
            ->with('success', 'Province created successfully.');
    }

    public function update(Request $request, Province $province)
    {
        $validated = $request->validate([
            'name_en' => ['required','string','max:100', Rule::unique('provinces','name_en')->ignore($province->id)],
            'name_km' => ['nullable','string','max:100'],
            'code'    => ['nullable','string','max:10', Rule::unique('provinces','code')->ignore($province->id)],
        ]);
        
        $province->update($validated);
        
        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Updated', 'data' => $province]);
        }
        
        // Redirect for form submissions
        return redirect()->route('provinces.index')
            ->with('success', 'Province updated successfully.');
    }

    public function destroy(Province $province)
    {
        try {
            $province->delete();
            
            // Return JSON for API requests
            if (request()->expectsJson()) {
                return response()->json(['ok' => true, 'message' => 'Deleted']);
            }
            
            // Redirect for form submissions
            return redirect()->route('provinces.index')
                ->with('success', 'Province deleted successfully.');
                
        } catch (\Throwable $e) {
            // Return JSON for API requests
            if (request()->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Cannot delete: in use'], 422);
            }
            
            // Redirect for form submissions
            return redirect()->route('provinces.index')
                ->with('error', 'Cannot delete province: it may be in use.');
        }
    }
}


