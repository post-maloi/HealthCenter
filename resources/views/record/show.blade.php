@extends('layouts.app')

@section('content')
@php
    $hasValue = fn ($value) => !is_null($value) && trim((string) $value) !== '' && strtoupper(trim((string) $value)) !== 'N/A';
@endphp
<style>
    @media print {
        .no-print { display: none !important; }
        .print-full-width { 
            grid-column: span 3 / span 3 !important; 
            width: 100% !important; 
            display: block !important; 
        }
        body { background-color: white !important; padding: 0 !important; }
        .max-w-5xl { max-width: 100% !important; width: 100% !important; }
        .shadow-sm { box-shadow: none !important; border: 1px solid #eee !important; }
        .rounded-\[2rem\] { border-radius: 0.5rem !important; } /* Standardize corners for print */
    }
</style>

<div class="max-w-5xl mx-auto py-8 px-4">
    {{-- Header --}}
    <div class="mb-8 flex justify-between items-center no-print">
        <div>
            <h1 class="text-2xl font-black text-gray-800 uppercase tracking-tight">Consultation Record</h1>
            <p class="text-gray-500 text-sm">Date: {{ \Carbon\Carbon::parse($record->consultation_date)->format('M d, Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('record.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition">
                ← Back
            </a>
            <a href="{{ route('record.print', $record->id) }}" target="_blank" class="px-4 py-2 bg-[#2D8A80] text-white rounded-xl font-bold hover:bg-[#246e66] transition shadow-lg shadow-teal-100">
                Print Record
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        {{-- Left Column: Patient Info --}}
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white border border-gray-100 rounded-[2rem] p-6 shadow-sm">
                <h2 class="text-xs font-black text-gray-300 uppercase tracking-widest mb-4">Patient Information</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Full Name</p>
                        <p class="font-black text-gray-800 uppercase">{{ $record->last_name }}, {{ $record->first_name }} {{ $record->middle_name }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Age / Sex</p>
                            <p class="font-bold text-gray-700">
                                {{-- Robust Age Logic: Handles both strings like "22 yrs" and numeric decimals --}}
                                {{ is_numeric($record->age) ? round($record->age) . ' yrs' : ($record->age ?: '--') }} / {{ $record->gender }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Status</p>
                            <p class="font-bold text-gray-700">{{ $record->civil_status }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Address</p>
                        <p class="font-bold text-gray-700 uppercase">{{ $record->address_purok }}</p>
                    </div>
                </div>
            </div>

            {{-- Visit History Sidebar (Hidden during print) --}}
            @if(isset($history) && $history->count() > 1)
            <div class="bg-gray-50 rounded-[2rem] p-6 no-print">
                <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Visit History</h2>
                <div class="space-y-3">
                    @foreach($history as $visit)
                        <a href="{{ route('record.show', $visit->id) }}" class="block p-3 rounded-xl border {{ $visit->id == $record->id ? 'bg-white border-[#2D8A80] shadow-sm' : 'border-transparent hover:bg-white' }} transition">
                            <p class="text-xs font-black {{ $visit->id == $record->id ? 'text-[#2D8A80]' : 'text-gray-600' }}">
                                {{ \Carbon\Carbon::parse($visit->consultation_date)->format('M d, Y') }}
                            </p>
                            <p class="text-[10px] text-gray-400 truncate">{{ $visit->diagnosis }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column: SOAP & Vitals --}}
        <div class="md:col-span-2 space-y-6 print-full-width">
            
            {{-- S - Subjective --}}
            <div class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                    <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest">Subjective Findings</h2>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">{{ $record->subjective ?: 'No complaints recorded.' }}</p>
            </div>

            {{-- O - Objective / Vital Signs --}}
            <div class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm">
                <div class="flex items-center gap-2 mb-6">
                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                    <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest">Objective / Vital Signs</h2>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-gray-50 rounded-2xl text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">Temp</p>
                        <p class="text-xl font-black text-gray-800">
                            {{ $hasValue($record->temp) ? $record->temp : '--' }}@if($hasValue($record->temp))<span class="text-xs">°C</span>@endif
                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">BP</p>
                        <p class="text-xl font-black text-gray-800">{{ $record->bp ?: '--' }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">Pulse</p>
                        <p class="text-xl font-black text-gray-800">
                            {{ $hasValue($record->pr) ? $record->pr : '--' }}@if($hasValue($record->pr))<span class="text-xs ml-1">bpm</span>@endif
                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">Resp</p>
                        <p class="text-xl font-black text-gray-800">
                            {{ $hasValue($record->rr) ? $record->rr : '--' }}@if($hasValue($record->rr))<span class="text-xs ml-1">cpm</span>@endif
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mt-4">
                    <div class="p-4 bg-gray-50 rounded-2xl text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">Weight</p>
                        <p class="text-lg font-black text-gray-800">
                            {{ $hasValue($record->weight) ? $record->weight : '--' }}@if($hasValue($record->weight))<span class="text-xs text-gray-400 ml-1">kg</span>@endif
                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">Height</p>
                        <p class="text-lg font-black text-gray-800">
                            {{ $hasValue($record->height) ? $record->height : '--' }}@if($hasValue($record->height))<span class="text-xs text-gray-400 ml-1">cm</span>@endif
                        </p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-2xl text-center border border-blue-100">
                        <p class="text-[10px] font-black text-blue-400 uppercase">BMI</p>
                        <p class="text-lg font-black text-blue-600">{{ $record->bmi ?: '--' }}</p>
                    </div>
                </div>

                <div class="mt-6 p-4 border border-dashed border-gray-200 rounded-2xl">
                    <p class="text-[10px] font-black text-gray-300 uppercase mb-2">Physical Examination Details</p>
                    <p class="text-gray-600 italic text-sm whitespace-pre-line">{{ $record->objective ?: 'No specific physical examination details provided.' }}</p>
                </div>
            </div>

            {{-- Assessment & Plan --}}
            <div class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                    <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest">Assessment / Diagnosis</h2>
                </div>
                <div class="p-6 bg-red-50 rounded-3xl border border-red-100">
                    <p class="text-lg font-bold text-gray-800">{{ $record->diagnosis }}</p>
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                    <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest">Plan / Prescribed Medicines</h2>
                </div>
                
                @if($record->medicines && $record->medicines->count() > 0)
                    <div class="space-y-3">
                        @foreach($record->medicines as $medicine)
                            <div class="flex justify-between items-center p-4 bg-green-50 rounded-2xl border border-green-100">
                                <span class="font-black text-gray-700 uppercase">{{ $medicine->name }}</span>
                                <span class="px-4 py-1 bg-white text-green-600 rounded-lg font-black text-sm shadow-sm">
                                    {{ $medicine->pivot->quantity }} Qty
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-10 text-center border border-dashed border-gray-200 rounded-3xl">
                        <p class="text-gray-400 font-bold uppercase text-xs tracking-widest">No medications prescribed.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection