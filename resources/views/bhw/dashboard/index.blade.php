@extends('layouts.app')

@section('content')
@php
    $doctorAvailable = \App\Models\User::query()
        ->where('role', 'doctor')
        ->get()
        ->contains(fn ($user) => $user->is_doctor_available);
@endphp

<div class="max-w-7xl mx-auto">
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 md:p-8 shadow-sm space-y-6">
        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            {{ $doctorAvailable ? 'Doctor Available' : 'Doctor Not Available' }}
        </div>

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-4xl font-black text-slate-800">Barangay Banilad Health Care Center</h1>
                <p class="text-slate-500 mt-1 text-sm">Daily summary and center management overview</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Current Date</p>
                <p class="text-2xl font-black text-slate-700">{{ now()->format('F d, Y') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Total Patients</p>
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center text-xs">👥</span>
                </div>
                <p class="text-4xl font-black mt-2 text-slate-800">{{ $totalPatients ?? 0 }}</p>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600 mt-2">Live registry count</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Today's Patients</p>
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-xs">🏥</span>
                </div>
                <p class="text-4xl font-black mt-2 text-slate-800">{{ $todayConsultations ?? 0 }}</p>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 mt-2">Current day census</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-rose-500">Low Stock Medicines</p>
                    <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center text-xs">⚠️</span>
                </div>
                <p class="text-4xl font-black mt-2 text-rose-600">{{ $lowStockCount ?? 0 }}</p>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-rose-500 mt-2">Critical inventory</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Recent Activity</h3>
                    <a href="{{ route('bhw.record.index') }}" class="text-sm text-blue-600 font-bold hover:underline">View History</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($recentRecords ?? [] as $recent)
                        <div class="px-5 py-3 flex items-center justify-between gap-3 hover:bg-slate-50 transition">
                            <div class="min-w-0 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center text-[11px] font-black uppercase text-slate-600 shrink-0">
                                    {{ strtoupper(substr($recent->first_name, 0, 1)) }}{{ strtoupper(substr($recent->last_name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-800 truncate">
                                        {{ \Illuminate\Support\Str::title(trim($recent->first_name . ' ' . ($recent->middle_name ? $recent->middle_name . ' ' : '') . $recent->last_name)) }}
                                    </p>
                                    <p class="text-xs text-slate-500 truncate">{{ $recent->diagnosis }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-slate-400 shrink-0">{{ \Carbon\Carbon::parse($recent->consultation_date)->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 italic text-sm">No recent consultations recorded.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm h-fit">
                <h3 class="font-bold text-slate-800 mb-4">Quick Navigation</h3>
                <div class="space-y-3">
                    <a href="{{ route('bhw.record.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-blue-200 bg-blue-50/40 hover:bg-blue-50 transition">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">📄</span>
                        <span class="text-sm font-bold text-slate-700">Patient Records</span>
                    </a>
                    <a href="{{ route('bhw.medicines.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-emerald-200 bg-emerald-50/40 hover:bg-emerald-50 transition">
                        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">🧪</span>
                        <span class="text-sm font-bold text-slate-700">Medicine Inventory</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
