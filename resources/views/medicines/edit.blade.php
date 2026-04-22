@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('medicines.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2 font-medium transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Inventory
        </a>
        <a href="{{ route('medicines.index') }}" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-bold hover:bg-blue-100 transition">
            View
        </a>
        </div>
        <h1 class="text-3xl font-bold text-gray-800">Edit Medicine</h1>
        <p class="text-gray-500 mt-1">Update details for <span class="font-semibold text-slate-700">{{ $medicine->name }}</span>.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <form action="{{ route('medicines.update', $medicine) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Medicine Name --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Medicine Name</label>
                    <input type="text" name="name" value="{{ old('name', $medicine->name) }}" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition">
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Type</label>
                    <select name="type"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition bg-white">
                        @php
                            $types = [
                                'Tablet', 'Capsule', 'Syrup', 'Suspension', 'Drops', 'Inhaler',
                                'Cream', 'Ointment', 'Eye Drops', 'Nebule', 'Injection',
                            ];
                            $selectedType = old('type', $medicine->type);
                        @endphp
                        <option value="" {{ empty($selectedType) ? 'selected' : '' }}>—</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ $selectedType === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Dosage --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Dosage</label>
                    <div class="grid grid-cols-[1fr_110px] gap-3">
                        <input type="number" name="dosage_value" value="{{ old('dosage_value', $medicine->dosage_value) }}"
                            min="0.01" step="0.01"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition">
                        <select name="dosage_unit"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition bg-white">
                            @php $unit = old('dosage_unit', $medicine->dosage_unit); @endphp
                            <option value="" {{ empty($unit) ? 'selected' : '' }}>—</option>
                            <option value="mg" {{ $unit === 'mg' ? 'selected' : '' }}>mg</option>
                            <option value="ml" {{ $unit === 'ml' ? 'selected' : '' }}>ml</option>
                        </select>
                    </div>
                </div>

                {{-- Stock Quantity --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Stock Quantity</label>
                    <input type="number" name="stock" value="{{ old('stock', $medicine->stock) }}" min="0" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition">
                </div>

                {{-- Expiration Date --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Expiration Date</label>
                    <input type="date" name="expiration_date" 
                        value="{{ old('expiration_date', $medicine->expiration_date->format('Y-m-d')) }}" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition">
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex items-center gap-4">
                <button type="submit" class="px-8 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-md transition active:transform active:scale-95">
                    Update Medicine
                </button>
                <a href="{{ route('medicines.index') }}" class="px-8 py-3 bg-gray-100 text-gray-600 font-bold rounded-lg hover:bg-gray-200 transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection