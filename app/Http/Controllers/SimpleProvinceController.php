<?php

namespace App\Http\Controllers;

use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProvinceController extends Controller
{
    public function index()
    {
        $provinces = Province::orderBy('name_en')->paginate(15);
        return view('locations.provinces.index', compact('provinces'));
    }

    public function create()
    {
        return view('locations.provinces.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:100', 'unique:provinces,name_en'],
            'name_km' => ['nullable', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:10', 'unique:provinces,code'],
        ]);

        Province::create($validated);

        return redirect()->route('provinces.index')
            ->with('success', 'Province created successfully.');
    }

    public function edit(Province $province)
    {
        return view('locations.provinces.edit', compact('province'));
    }

    public function update(Request $request, Province $province)
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:100', Rule::unique('provinces', 'name_en')->ignore($province->id)],
            'name_km' => ['nullable', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:10', Rule::unique('provinces', 'code')->ignore($province->id)],
        ]);

        $province->update($validated);

        return redirect()->route('provinces.index')
            ->with('success', 'Province updated successfully.');
    }

    public function destroy(Province $province)
    {
        try {
            $province->delete();
            return redirect()->route('provinces.index')
                ->with('success', 'Province deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('provinces.index')
                ->with('error', 'Cannot delete province: it may be in use.');
        }
    }
}











