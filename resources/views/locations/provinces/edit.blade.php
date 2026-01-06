@extends('layouts.app')

@section('title', 'Edit Province')

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Province</h1>
            <p class="text-gray-600 mt-1">Update province information</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('provinces.update', $province) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Name (English) -->
                <div class="mb-5">
                    <label for="name_en" class="block text-sm font-semibold text-gray-700 mb-2">
                        Name (English) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name_en" 
                           id="name_en" 
                           value="{{ old('name_en', $province->name_en) }}"
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
                           value="{{ old('name_km', $province->name_km) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name_km') border-red-500 @enderror">
                    @error('name_km')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Code -->
                <div class="mb-6">
                    <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">
                        Code
                    </label>
                    <input type="text" 
                           name="code" 
                           id="code" 
                           value="{{ old('code', $province->code) }}"
                           maxlength="10"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('code') border-red-500 @enderror">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Optional unique code for the province</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow">
                        Update Province
                    </button>
                    <a href="{{ route('provinces.index') }}" 
                       class="px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection











