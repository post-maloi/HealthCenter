@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">Admin Reports</h1>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border p-4">
            <div class="flex items-center justify-between mb-2 gap-2">
                <h2 class="font-bold">Consultation Report (30 days)</h2>
                <a href="{{ route('admin.reports.consultation.export') }}"
                   class="px-3 py-1.5 text-xs font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                    Export Excel
                </a>
            </div>
            <ul class="text-sm space-y-1">
                @foreach($consultationReport as $row)
                    <li>{{ $row->day }}: <span class="font-semibold">{{ $row->total }}</span></li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <div class="flex items-center justify-between mb-2 gap-2">
                <h2 class="font-bold">Medicine Usage</h2>
                <a href="{{ route('admin.reports.medicine-usage.export') }}"
                   class="px-3 py-1.5 text-xs font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                    Export Excel
                </a>
            </div>
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
