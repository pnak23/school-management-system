@extends('layouts.app')

@section('title', 'Villages')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Villages</h1>
                <p class="text-gray-600 mt-1">Manage villages</p>
            </div>
            <a href="{{ route('villages.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Village
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Villages Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Province</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">District</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commune</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name (EN)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name (KM)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($villages as $village)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $village->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600">{{ $village->commune->district->province->name_en }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600">{{ $village->commune->district->name_en }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">{{ $village->commune->name_en }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $village->name_en }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $village->name_km ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex gap-2">
                                    <a href="{{ route('villages.edit', $village) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">
                                        Edit
                                    </a>
                                    <form action="{{ route('villages.destroy', $village) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this village?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No villages found. <a href="{{ route('villages.create') }}" class="text-indigo-600 hover:text-indigo-900">Add one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($villages->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $villages->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection











