@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">
    {{-- Header --}}
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-black text-[#2D8A80] uppercase tracking-tight">{{ $medicine->name }}</h1>
            <p class="text-gray-500 text-sm font-medium mt-1">Lot-based inventory • Sorted by expiration date (FEFO)</p>
        </div>
        <a href="{{ route('medicines.index') }}" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition shadow-sm">
            ✕ Close
        </a>
    </div>

    {{-- Lot Cards Container --}}
    <div class="space-y-6">
        @foreach($lots as $index => $lot)
        <div class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm flex flex-col md:flex-row items-center gap-8 relative overflow-hidden transition hover:shadow-md">
            
            {{-- Lot Number Circle --}}
            <div class="w-16 h-16 bg-[#2D8A80] text-white rounded-full flex items-center justify-center font-black text-2xl shrink-0 shadow-lg shadow-teal-100">
                {{ $index + 1 }}
            </div>
            
            <div class="flex-grow grid grid-cols-1 md:grid-cols-3 gap-8 w-full">
                {{-- Column 1: Lot Info --}}
                <div>
                    <h3 class="text-xl font-black text-gray-800">{{ $lot->batch_number ?? 'LOT-' . str_pad($lot->id, 3, '0', STR_PAD_LEFT) }}</h3>
                    @if($index === 0 && $lot->stock > 0)
                        <p class="text-[11px] text-[#2D8A80] font-black uppercase mt-1 flex items-center gap-1">
                            <span class="w-2 h-2 bg-[#2D8A80] rounded-full animate-pulse"></span>
                            Next to dispense (FEFO)
                        </p>
                    @endif
                    <p class="text-[10px] text-gray-400 font-bold mt-2 uppercase tracking-widest">
                        @php
                            $daysRemaining = now()->diffInDays($lot->expiration_date, false);
                        @endphp
                        {{ $daysRemaining > 0 ? $daysRemaining . ' days until expiry' : 'Expired' }}
                    </p>
                </div>

                {{-- Column 2: Dates --}}
                <div class="flex flex-col justify-center gap-4">
                    <div>
                        <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]">Date Received</p>
                        <p class="font-bold text-gray-700 text-lg">{{ $lot->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]">Expiration Date</p>
                        <p class="font-bold text-gray-700 text-lg">{{ $lot->expiration_date->format('M d, Y') }}</p>
                    </div>
                </div>

                {{-- Column 3: Quantity & Actions --}}
                <div class="flex flex-col justify-between items-end">
                    <div class="text-right">
                        <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]">Quantity</p>
                        <p class="text-4xl font-black text-gray-800">{{ $lot->stock }} <span class="text-sm font-bold text-gray-400">units</span></p>
                    </div>

                    <div class="flex gap-3 mt-6">
                        {{-- The Main Edit Function --}}
                        <a href="{{ route('medicines.edit', $lot) }}" 
                           class="flex items-center gap-2 px-5 py-2 bg-[#E9F3F1] text-[#2D8A80] rounded-xl font-black text-xs hover:bg-[#dcece9] transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Edit
                        </a>

                        <form action="{{ route('medicines.destroy', $lot) }}" method="POST" onsubmit="return confirm('Delete this specific lot?')">
                            @csrf @method('DELETE')
                            <button class="flex items-center gap-2 px-5 py-2 bg-[#FFF1F1] text-[#FF5C5C] rounded-xl font-black text-xs hover:bg-[#ffe4e4] transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Status Badge --}}
            <div class="absolute top-8 right-8">
                <span class="px-4 py-1.5 bg-[#F0FDF4] text-[#16A34A] border border-[#DCFCE7] rounded-full text-[10px] font-black uppercase tracking-widest">
                    {{ $lot->stock > 20 ? 'Good' : 'Low' }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection