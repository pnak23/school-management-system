<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Models\Commune;
use App\Models\District;
use App\Models\Province;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    public function index()
    {
        $villages = Village::with(['commune.district.province'])->orderBy('name_en')->paginate(15);
        return view('locations.villages.index', compact('villages'));
    }

    public function create()
    {
        $provinces = Province::orderBy('name_en')->get();
        $districts = District::orderBy('name_en')->get();
        $communes = Commune::orderBy('name_en')->get();
        return view('locations.villages.create', compact('provinces', 'districts', 'communes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'commune_id' => ['required', 'exists:communes,id'],
            'name_en' => ['required', 'string', 'max:120'],
            'name_km' => ['nullable', 'string', 'max:120'],
        ]);

        Village::create($validated);

        return redirect()->route('villages.index')
            ->with('success', 'Village created successfully.');
    }

    public function edit(Village $village)
    {
        $provinces = Province::orderBy('name_en')->get();
        $districts = District::orderBy('name_en')->get();
        $communes = Commune::orderBy('name_en')->get();
        return view('locations.villages.edit', compact('village', 'provinces', 'districts', 'communes'));
    }

    public function update(Request $request, Village $village)
    {
        $validated = $request->validate([
            'commune_id' => ['required', 'exists:communes,id'],
            'name_en' => ['required', 'string', 'max:120'],
            'name_km' => ['nullable', 'string', 'max:120'],
        ]);

        $village->update($validated);

        return redirect()->route('villages.index')
            ->with('success', 'Village updated successfully.');
    }

    public function destroy(Village $village)
    {
        try {
            $village->delete();
            return redirect()->route('villages.index')
                ->with('success', 'Village deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('villages.index')
                ->with('error', 'Cannot delete village: it may be in use.');
        }
    }

    // API endpoint for getting villages by commune
    public function byCommune($communeId)
    {
        $villages = Village::where('commune_id', $communeId)
            ->orderBy('name_en')
            ->get();
        return response()->json($villages);
    }
}
