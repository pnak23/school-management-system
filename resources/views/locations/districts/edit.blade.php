@extends('layouts.app')

@section('title', 'Edit District')

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit District</h1>
            <p class="text-gray-600 mt-1">Update district information</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('districts.update', $district) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Province Select -->
                <div class="mb-5">
                    <label for="province_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Province <span class="text-red-500">*</span>
                    </label>
                    <select name="province_id" 
                            id="province_id" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('province_id') border-red-500 @enderror">
                        <option value="">-- Select Province --</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province->id }}" {{ old('province_id', $district->province_id) == $province->id ? 'selected' : '' }}>
                                {{ $province->name_en }}
                            </option>
                        @endforeach
                    </select>
                    @error('province_id')
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
                           value="{{ old('name_en', $district->name_en) }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name_en') border-red-500 @enderror">
                    @error('name_en')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name (Khmer) -->
                <div class="mb-5">
                    <label for="name_km" class="block text-sm font-semibold text-gray-700 mb-2">
                        Name (Khmer)
                    </label>
                    <input type="text" 
                           name="name_km" 
                           id="name_km" 
                           value="{{ old('name_km', $district->name_km) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name_km') border-red-500 @enderror">
                    @error('name_km')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type Select -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                        Type <span class="text-red-500">*</span>
                    </label>
                    <select name="type" 
                            id="type" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('type') border-red-500 @enderror">
                        <option value="district" {{ old('type', $district->type) == 'district' ? 'selected' : '' }}>District</option>
                        <option value="city" {{ old('type', $district->type) == 'city' ? 'selected' : '' }}>City</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow">
                        Update District
                    </button>
                    <a href="{{ route('districts.index') }}" 
                       class="px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection











