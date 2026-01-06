<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DistrictController extends Controller
{
    public function index()
    {
        $districts = District::with('province')->orderBy('name_en')->paginate(15);
        return view('locations.districts.index', compact('districts'));
    }

    public function create()
    {
        $provinces = Province::orderBy('name_en')->get();
        return view('locations.districts.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'province_id' => ['required', 'exists:provinces,id'],
            'name_en' => ['required', 'string', 'max:120'],
            'name_km' => ['nullable', 'string', 'max:120'],
            'type' => ['required', 'in:district,city'],
        ]);

        District::create($validated);

        return redirect()->route('districts.index')
            ->with('success', 'District created successfully.');
    }

    public function edit(District $district)
    {
        $provinces = Province::orderBy('name_en')->get();
        return view('locations.districts.edit', compact('district', 'provinces'));
    }

    public function update(Request $request, District $district)
    {
        $validated = $request->validate([
            'province_id' => ['required', 'exists:provinces,id'],
            'name_en' => ['required', 'string', 'max:120'],
            'name_km' => ['nullable', 'string', 'max:120'],
            'type' => ['required', 'in:district,city'],
        ]);

        $district->update($validated);

        return redirect()->route('districts.index')
            ->with('success', 'District updated successfully.');
    }

    public function destroy(District $district)
    {
        try {
            $district->delete();
            return redirect()->route('districts.index')
                ->with('success', 'District deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('districts.index')
                ->with('error', 'Cannot delete district: it may be in use.');
        }
    }

    // API endpoint for getting districts by province
    public function byProvince($provinceId)
    {
        $districts = District::where('province_id', $provinceId)
            ->orderBy('name_en')
            ->get();
        return response()->json($districts);
    }
}
