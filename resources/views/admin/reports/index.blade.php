@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">Admin Reports</h1>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-700 text-white rounded-lg">Print</button>
    </div>

    <form method="GET" class="max-w-xs">
        <label class="text-sm text-slate-500">Daily Patient Report Date</label>
        <input type="date" name="date" value="{{ $date }}" class="w-full border rounded-lg px-3 py-2 mt-1">
    </form>

    <div class="grid lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border p-4">
            <h2 class="font-bold mb-2">Daily Patients</h2>
            <p class="text-sm text-slate-500 mb-2">{{ $date }}</p>
            <ul class="text-sm space-y-1">
                @forelse($dailyPatients as $patient)
                    <li>{{ trim($patient->first_name.' '.$patient->last_name) }} - {{ $patient->diagnosis }}</li>
                @empty
                    <li class="text-slate-500">No records.</li>
                @endforelse
            </ul>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <h2 class="font-bold mb-2">Consultation Report (30 days)</h2>
            <ul class="text-sm space-y-1">
                @foreach($consultationReport as $row)
                    <li>{{ $row->day }}: <span class="font-semibold">{{ $row->total }}</span></li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <h2 class="font-bold mb-2">Medicine Usage</h2>
            <ul class="text-sm space-y-1">
                @forelse($medicineUsage as $usage)
                    <li>{{ $usage->medicine?->name ?? 'Unknown' }}: <span class="font-semibold">{{ $usage->used_quantity }}</span></li>
                @empty
                    <li class="text-slate-500">No usage logs.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
