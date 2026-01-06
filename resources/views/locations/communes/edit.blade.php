@extends('layouts.app')

@section('title', 'Edit Commune')

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Commune</h1>
            <p class="text-gray-600 mt-1">Update commune information</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('communes.update', $commune) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Province Select (for filtering) -->
                <div class="mb-5">
                    <label for="filter_province" class="block text-sm font-semibold text-gray-700 mb-2">
                        Province (for filtering)
                    </label>
                    <select id="filter_province" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Select Province First --</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province->id }}" {{ $commune->district->province_id == $province->id ? 'selected' : '' }}>
                                {{ $province->name_en }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Select province to filter districts</p>
                </div>

                <!-- District Select -->
                <div class="mb-5">
                    <label for="district_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        District <span class="text-red-500">*</span>
                    </label>
                    <select name="district_id" 
                            id="district_id" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('district_id') border-red-500 @enderror">
                        <option value="">-- Select District --</option>
                        @foreach($districts as $district)
                            <option value="{{ $district->id }}" data-province="{{ $district->province_id }}" {{ old('district_id', $commune->district_id) == $district->id ? 'selected' : '' }}>
                                {{ $district->name_en }}
                            </option>
                        @endforeach
                    </select>
                    @error('district_id')
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
                           value="{{ old('name_en', $commune->name_en) }}"
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
                           value="{{ old('name_km', $commune->name_km) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name_km') border-red-500 @enderror">
                    @error('name_km')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow">
                        Update Commune
                    </button>
                    <a href="{{ route('communes.index') }}" 
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
    const districtSelect = document.getElementById('district_id');
    const allDistricts = Array.from(districtSelect.options).filter(opt => opt.value);

    provinceSelect.addEventListener('change', function() {
        const selectedProvince = this.value;
        const currentDistrict = '{{ old("district_id", $commune->district_id) }}';
        
        // Clear current options except first
        districtSelect.innerHTML = '<option value="">-- Select District --</option>';
        
        // Filter and add options
        allDistricts.forEach(option => {
            if (!selectedProvince || option.dataset.province == selectedProvince) {
                const newOption = option.cloneNode(true);
                if (newOption.value == currentDistrict) {
                    newOption.selected = true;
                }
                districtSelect.appendChild(newOption);
            }
        });
    });
    
    // Trigger on load to filter correctly
    if (provinceSelect.value) {
        provinceSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection











