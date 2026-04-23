@extends('layouts.app')

@section('content')
@php
    $hasValue = fn ($value) => !is_null($value) && trim((string) $value) !== '' && strtoupper(trim((string) $value)) !== 'N/A';
    $conditionStatus = $record->condition_update ?: 'monitoring';
    $conditionLabel = match($conditionStatus) {
        'recovered' => 'Recovered',
        'improving' => 'Improving',
        'no_improvement' => 'No Improvement',
        'worsened' => 'Worsened',
        default => 'Monitoring',
    };
    $conditionClass = match($conditionStatus) {
        'recovered' => 'bg-emerald-100 text-emerald-700',
        'improving' => 'bg-yellow-100 text-yellow-700',
        'no_improvement' => 'bg-orange-100 text-orange-700',
        'worsened' => 'bg-red-100 text-red-700',
        default => 'bg-slate-100 text-slate-700',
    };
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
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Contact Number</p>
                        <p class="font-bold text-gray-700">{{ $record->contact_number ?: 'N/A' }}</p>
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
                            <div class="flex items-start gap-3">
                                @php
                                    $thumb = $visit->laboratoryFiles->first();
                                    $count = $visit->laboratoryFiles->count();
                                @endphp
                                @if($thumb)
                                    <div class="relative shrink-0">
                                        <img
                                            src="{{ asset('storage/'.$thumb->path) }}"
                                            alt="Lab"
                                            class="w-10 h-10 rounded-lg object-cover border border-gray-100"
                                            loading="lazy"
                                        >
                                        @if($count > 1)
                                            <span class="absolute -top-1 -right-1 px-1.5 py-0.5 rounded-full bg-blue-600 text-white text-[9px] font-black">
                                                +{{ $count - 1 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-white border border-gray-100 shrink-0 flex items-center justify-center text-[10px] font-black text-gray-300">
                                        —
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <p class="text-xs font-black {{ $visit->id == $record->id ? 'text-[#2D8A80]' : 'text-gray-600' }}">
                                        {{ \Carbon\Carbon::parse($visit->consultation_date)->format('M d, Y') }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 truncate">{{ $visit->diagnosis }}</p>
                                    <p class="text-[10px] text-gray-500 mt-1">
                                        T: {{ $visit->display_temp ?: '--' }} | BP: {{ $visit->display_bp ?: '--' }} | WT: {{ $visit->display_weight ?: '--' }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($history) && $history->count() > 0)
            <div class="bg-white border border-gray-100 rounded-[2rem] p-6 no-print">
                <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Recovery Timeline</h2>
                <div class="space-y-3">
                    @foreach($history->sortBy('consultation_date')->values() as $visit)
                        @php
                            $status = $visit->condition_update ?: 'monitoring';
                            $statusText = match($status) {
                                'recovered' => 'Recovered',
                                'improving' => 'Improving',
                                'no_improvement' => 'No Improvement',
                                'worsened' => 'Worsened',
                                default => 'Monitoring',
                            };
                            $statusClass = match($status) {
                                'recovered' => 'bg-emerald-100 text-emerald-700',
                                'improving' => 'bg-yellow-100 text-yellow-700',
                                'no_improvement' => 'bg-orange-100 text-orange-700',
                                'worsened' => 'bg-red-100 text-red-700',
                                default => 'bg-slate-100 text-slate-700',
                            };
                        @endphp
                        <div class="flex items-start justify-between gap-2 p-3 rounded-xl border border-gray-100">
                            <div>
                                <p class="text-[11px] font-black text-gray-500 uppercase">{{ \Carbon\Carbon::parse($visit->consultation_date)->format('M d, Y') }}</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $visit->diagnosis }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $statusClass }}">{{ $statusText }}</span>
                        </div>
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
                            {{ $hasValue($record->display_temp ?? null) ? $record->display_temp : '--' }}@if($hasValue($record->display_temp ?? null))<span class="text-xs">°C</span>@endif
                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">BP</p>
                        <p class="text-xl font-black text-gray-800">{{ $record->display_bp ?: '--' }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">Pulse</p>
                        <p class="text-xl font-black text-gray-800">
                            {{ $hasValue($record->display_pr ?? null) ? $record->display_pr : '--' }}@if($hasValue($record->display_pr ?? null))<span class="text-xs ml-1">bpm</span>@endif
                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">Resp</p>
                        <p class="text-xl font-black text-gray-800">
                            {{ $hasValue($record->display_rr ?? null) ? $record->display_rr : '--' }}@if($hasValue($record->display_rr ?? null))<span class="text-xs ml-1">cpm</span>@endif
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mt-4">
                    <div class="p-4 bg-gray-50 rounded-2xl text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">Weight</p>
                        <p class="text-lg font-black text-gray-800">
                            {{ $hasValue($record->display_weight ?? null) ? $record->display_weight : '--' }}@if($hasValue($record->display_weight ?? null))<span class="text-xs text-gray-400 ml-1">kg</span>@endif
                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">Height</p>
                        <p class="text-lg font-black text-gray-800">
                            {{ $hasValue($record->display_height ?? null) ? $record->display_height : '--' }}@if($hasValue($record->display_height ?? null))<span class="text-xs text-gray-400 ml-1">cm</span>@endif
                        </p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-2xl text-center border border-blue-100">
                        <p class="text-[10px] font-black text-blue-400 uppercase">BMI</p>
                        <p class="text-lg font-black text-blue-600">{{ $record->display_bmi ?: '--' }}</p>
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
                <div class="mb-3">
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $conditionClass }}">{{ $conditionLabel }}</span>
                </div>
                <div class="p-6 bg-red-50 rounded-3xl border border-red-100">
                    <p class="text-lg font-bold text-gray-800">{{ $record->diagnosis }}</p>
                </div>
                @if(!empty($record->follow_up_recommendation))
                    <div class="mt-3 p-3 rounded-xl border border-slate-200 bg-slate-50">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Follow-up Recommendation</p>
                        <p class="text-sm text-slate-700 mt-1">{{ $record->follow_up_recommendation }}</p>
                    </div>
                @endif
            </div>

            {{-- Laboratory Files --}}
            <div class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-indigo-500 rounded-full"></span>
                        <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest">Laboratory</h2>
                    </div>
                    @if($record->laboratoryFiles && $record->laboratoryFiles->count() > 0)
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            {{ $record->laboratoryFiles->count() }} file{{ $record->laboratoryFiles->count() > 1 ? 's' : '' }}
                        </span>
                    @endif
                </div>

                @if($record->laboratoryFiles && $record->laboratoryFiles->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($record->laboratoryFiles as $file)
                            <a href="{{ asset('storage/'.$file->path) }}" target="_blank"
                               class="group block rounded-2xl overflow-hidden border border-gray-100 bg-gray-50 hover:shadow-md transition">
                                <img src="{{ asset('storage/'.$file->path) }}"
                                     alt="{{ $file->original_name ?? 'Laboratory file' }}"
                                     class="w-full h-28 object-cover group-hover:scale-[1.02] transition-transform"
                                     loading="lazy">
                                <div class="px-3 py-2 bg-white">
                                    <p class="text-[10px] font-bold text-gray-500 truncate">
                                        {{ $file->original_name ?? 'Laboratory image' }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="py-10 text-center border border-dashed border-gray-200 rounded-3xl">
                        <p class="text-gray-400 font-bold uppercase text-xs tracking-widest">No laboratory files uploaded.</p>
                    </div>
                @endif
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
                @elseif($hasValue($record->medicines_given))
                    <div class="p-4 bg-green-50 rounded-2xl border border-green-100">
                        <p class="font-bold text-gray-700">{{ $record->medicines_given }}</p>
                    </div>
                @else
                    <div class="py-10 text-center border border-dashed border-gray-200 rounded-3xl">
                        <p class="text-gray-400 font-bold uppercase text-xs tracking-widest">No medications prescribed.</p>
                    </div>
                @endif

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="p-3 bg-gray-50 border border-gray-100 rounded-xl">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Previous Consulted By</p>
                        <p class="text-sm font-bold text-gray-700 uppercase">{{ $record->consulted_by ?: '—' }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl">
                        <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-1">Current Consulted By</p>
                        <p class="text-sm font-bold text-blue-700 uppercase">{{ $record->doctor_consulted_by ?: ($record->consulted_by ?: '—') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection