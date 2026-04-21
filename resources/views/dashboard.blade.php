@extends('layouts.app')

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- 1. HEADER SECTION --}}
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Barangay Banilad Health Care Center</h1>
            <p class="text-gray-500 mt-1 text-sm">Daily summary and center management overview</p>
        </div>
        <div class="text-right hidden md:block">
            <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Current Date</p>
            <p class="text-lg font-bold text-slate-700">{{ now()->format('F d, Y') }}</p>
        </div>
    </div>

    {{-- 2. STATS OVERVIEW CARDS (Redistributed to 3 columns) --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    {{-- Total Patients Stat --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
        <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase">Total Patients</p>
            <h3 class="text-2xl font-bold text-gray-800">{{ $totalPatients ?? '0' }}</h3>
        </div>
    </div>

    {{-- Today's Consultations --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
        <div class="p-3 bg-green-50 rounded-xl text-green-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase">Today's Visits</p>
            <h3 class="text-2xl font-bold text-gray-800">{{ $todayConsultations ?? '0' }}</h3>
        </div>
    </div>

    {{-- Low Stock Alert --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
        <div class="p-3 bg-red-50 rounded-xl text-red-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase">Low Stock</p>
            <h3 class="text-2xl font-bold text-red-600">{{ $lowStockCount ?? '0' }}</h3>
        </div>
    </div>
</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- 3. RECENT PATIENTS LIST --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Recent Activity</h3>
                <a href="{{ route('record.index') }}" class="text-sm text-blue-600 font-bold hover:underline">View History</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentRecords ?? [] as $recent)
                <div class="p-4 flex items-center justify-between hover:bg-slate-50/50 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-bold uppercase text-slate-500">
                            {{ strtoupper(substr($recent->first_name, 0, 1)) }}{{ strtoupper(substr($recent->last_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-800">
                                {{ \Illuminate\Support\Str::title(trim($recent->first_name . ' ' . ($recent->middle_name ? $recent->middle_name . ' ' : '') . $recent->last_name)) }}
                            </p>
                            <p class="text-xs text-slate-500 line-clamp-1">{{ $recent->diagnosis }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-medium text-slate-400">{{ \Carbon\Carbon::parse($recent->consultation_date)->diffForHumans() }}</span>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 italic text-sm">No recent consultations recorded.</div>
                @endforelse
            </div>
        </div>

        {{-- 4. NAVIGATION & INVENTORY PREVIEW --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-800 mb-4">Quick Navigation</h3>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('record.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-gray-50 hover:border-blue-100 hover:bg-blue-50/50 transition group">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span class="text-sm font-bold text-slate-600">Patient Records</span>
                    </a>
                    <a href="{{ route('medicines.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-gray-50 hover:border-green-100 hover:bg-green-50/50 transition group">
                        <div class="w-8 h-8 rounded-lg bg-green-100 text-green-600 flex items-center justify-center group-hover:bg-green-600 group-hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                        <span class="text-sm font-bold text-slate-600">Medicine Inventory</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection