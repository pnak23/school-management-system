<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\District;
use App\Models\Province;
use Illuminate\Http\Request;

class CommuneController extends Controller
{
    public function index()
    {
        $communes = Commune::with(['district.province'])->orderBy('name_en')->paginate(15);
        return view('locations.communes.index', compact('communes'));
    }

    public function create()
    {
        $provinces = Province::orderBy('name_en')->get();
        $districts = District::orderBy('name_en')->get();
        return view('locations.communes.create', compact('provinces', 'districts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'district_id' => ['required', 'exists:districts,id'],
            'name_en' => ['required', 'string', 'max:120'],
            'name_km' => ['nullable', 'string', 'max:120'],
        ]);

        Commune::create($validated);

        return redirect()->route('communes.index')
            ->with('success', 'Commune created successfully.');
    }

    public function edit(Commune $commune)
    {
        $provinces = Province::orderBy('name_en')->get();
        $districts = District::orderBy('name_en')->get();
        return view('locations.communes.edit', compact('commune', 'provinces', 'districts'));
    }

    public function update(Request $request, Commune $commune)
    {
        $validated = $request->validate([
            'district_id' => ['required', 'exists:districts,id'],
            'name_en' => ['required', 'string', 'max:120'],
            'name_km' => ['nullable', 'string', 'max:120'],
        ]);

        $commune->update($validated);

        return redirect()->route('communes.index')
            ->with('success', 'Commune updated successfully.');
    }

    public function destroy(Commune $commune)
    {
        try {
            $commune->delete();
            return redirect()->route('communes.index')
                ->with('success', 'Commune deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('communes.index')
                ->with('error', 'Cannot delete commune: it may be in use.');
        }
    }

    // API endpoint for getting communes by district
    public function byDistrict($districtId)
    {
        $communes = Commune::where('district_id', $districtId)
            ->orderBy('name_en')
            ->get();
        return response()->json($communes);
    }
}
