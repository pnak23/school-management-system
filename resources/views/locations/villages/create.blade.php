@extends('layouts.app')

@section('title', 'Create Village')

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create Village</h1>
            <p class="text-gray-600 mt-1">Add a new village to the system</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('villages.store') }}" method="POST">
                @csrf

                <!-- Province Select (for filtering) -->
                <div class="mb-5">
                    <label for="filter_province" class="block text-sm font-semibold text-gray-700 mb-2">
                        Province (for filtering)
                    </label>
                    <select id="filter_province" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Select Province First --</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province->id }}">{{ $province->name_en }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Select province to filter districts</p>
                </div>

                <!-- District Select (for filtering) -->
                <div class="mb-5">
                    <label for="filter_district" class="block text-sm font-semibold text-gray-700 mb-2">
                        District (for filtering)
                    </label>
                    <select id="filter_district" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Select District --</option>
                        @foreach($districts as $district)
                            <option value="{{ $district->id }}" data-province="{{ $district->province_id }}">
                                {{ $district->name_en }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Select district to filter communes</p>
                </div>

                <!-- Commune Select -->
                <div class="mb-5">
                    <label for="commune_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Commune <span class="text-red-500">*</span>
                    </label>
                    <select name="commune_id" 
                            id="commune_id" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('commune_id') border-red-500 @enderror">
                        <option value="">-- Select Commune --</option>
                        @foreach($communes as $commune)
                            <option value="{{ $commune->id }}" data-district="{{ $commune->district_id }}" {{ old('commune_id') == $commune->id ? 'selected' : '' }}>
                                {{ $commune->name_en }}
                            </option>
                        @endforeach
                    </select>
                    @error('commune_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name (English) -->
                <div class="mb-5">
                    <label for="name_en" class="block text-sm font-semibold text-gray-700 mb-2">
                        Name (English) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name_en" 
                           id="name_en" 
                           value="{{ old('name_en') }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name_en') border-red-500 @enderror">
                    @error('name_en')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name (Khmer) -->
                <div class="mb-6">
                    <label for="name_km" class="block text-sm font-semibold text-gray-700 mb-2">
                        Name (Khmer)
                    </label>
                    <input type="text" 
                           name="name_km" 
                           id="name_km" 
                           value="{{ old('name_km') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name_km') border-red-500 @enderror">
                    @error('name_km')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow">
                        Create Village
                    </button>
                    <a href="{{ route('villages.index') }}" 
                       class="px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const provinceSelect = document.getElementById('filter_province');
    const districtSelect = document.getElementById('filter_district');
    const communeSelect = document.getElementById('commune_id');
    
    const allDistricts = Array.from(districtSelect.options).filter(opt => opt.value);
    const allCommunes = Array.from(communeSelect.options).filter(opt => opt.value);

    // Filter districts when province changes
    provinceSelect.addEventListener('change', function() {
        const selectedProvince = this.value;
        
        // Clear and reset districts
        districtSelect.innerHTML = '<option value="">-- Select District --</option>';
        
        allDistricts.forEach(option => {
            if (!selectedProvince || option.dataset.province == selectedProvince) {
                districtSelect.appendChild(option.cloneNode(true));
            }
        });
        
        // Reset communes
        communeSelect.innerHTML = '<option value="">-- Select Commune --</option>';
    });

    // Filter communes when district changes
    districtSelect.addEventListener('change', function() {
        const selectedDistrict = this.value;
        
        // Clear and reset communes
        communeSelect.innerHTML = '<option value="">-- Select Commune --</option>';
        
        allCommunes.forEach(option => {
            if (!selectedDistrict || option.dataset.district == selectedDistrict) {
                communeSelect.appendChild(option.cloneNode(true));
            }
        });
    });
});
</script>
@endpush
@endsection











