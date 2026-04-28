@extends('layouts.app')

@section('content')
@php
    $doctorAvailable = \App\Models\User::query()
        ->where('role', 'doctor')
        ->get()
        ->contains(fn ($user) => $user->is_doctor_available);
    $clinicName = \App\Models\Setting::getValue('clinic_name', 'Barangay Banilad Health Care Center') ?: 'Barangay Banilad Health Care Center';
    $clinicAddress = \App\Models\Setting::getValue('clinic_address', 'Centralized operations and system insights.') ?: 'Centralized operations and system insights.';
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                Doctor Available
            </div>
            <div>
                <h1 class="text-5xl font-black text-slate-800 tracking-tight">{{ $clinicName }}</h1>
                <p class="text-sm text-slate-500">{{ $clinicAddress }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Today</p>
            <p class="text-2xl font-black text-slate-700">{{ now()->format('F d, Y') }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-blue-900/60 bg-gradient-to-r from-[#0f2c77] via-[#123785] to-[#0e2765] px-4 py-3 shadow-sm">
        <p class="inline-flex items-center gap-2 text-sm font-black text-white mb-3">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-white/10 border border-white/15">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10 13a5 5 0 0 0 7.07 0l2.83-2.83a5 5 0 1 0-7.07-7.07L11.2 4.72"/>
                    <path d="M14 11a5 5 0 0 0-7.07 0L4.1 13.83a5 5 0 0 0 7.07 7.07L12.8 19.3"/>
                </svg>
            </span>
            Quick Actions
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-2">
            <a href="{{ route('record.index') }}" class="group rounded-xl border border-white/15 bg-white/95 px-2 py-2 text-center text-slate-700 hover:bg-blue-50 transition">
                <span class="mx-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#123785] text-white group-hover:bg-[#0f2c77]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <span class="mt-1.5 block text-[11px] font-bold leading-tight">Patients</span>
            </a>
            <a href="{{ route('medicines.index') }}" class="group rounded-xl border border-white/15 bg-white/95 px-2 py-2 text-center text-slate-700 hover:bg-blue-50 transition">
                <span class="mx-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#123785] text-white group-hover:bg-[#0f2c77]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 8.5l7 7m-9.5 1a3.5 3.5 0 010-5l5.5-5.5a3.5 3.5 0 115 5L11 16.5a3.5 3.5 0 01-5 0z"/>
                    </svg>
                </span>
                <span class="mt-1.5 block text-[11px] font-bold leading-tight">Inventory</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="group rounded-xl border border-white/15 bg-white/95 px-2 py-2 text-center text-slate-700 hover:bg-blue-50 transition">
                <span class="mx-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#123785] text-white group-hover:bg-[#0f2c77]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a3 3 0 00-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
                <span class="mt-1.5 block text-[11px] font-bold leading-tight">User Management</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="group rounded-xl border border-white/15 bg-white/95 px-2 py-2 text-center text-slate-700 hover:bg-blue-50 transition">
                <span class="mx-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#123785] text-white group-hover:bg-[#0f2c77]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 20h14a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                    </svg>
                </span>
                <span class="mt-1.5 block text-[11px] font-bold leading-tight">Reports</span>
            </a>
            <a href="{{ route('admin.activity-logs.index') }}" class="group rounded-xl border border-white/15 bg-white/95 px-2 py-2 text-center text-slate-700 hover:bg-blue-50 transition">
                <span class="mx-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#123785] text-white group-hover:bg-[#0f2c77]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <span class="mt-1.5 block text-[11px] font-bold leading-tight">Activity Logs</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="group rounded-xl border border-white/15 bg-white/95 px-2 py-2 text-center text-slate-700 hover:bg-blue-50 transition">
                <span class="mx-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#123785] text-white group-hover:bg-[#0f2c77]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.983 5.25c.472-1.52 2.562-1.52 3.034 0a1.75 1.75 0 002.624 1.016c1.34-.85 2.817.627 1.967 1.967a1.75 1.75 0 001.016 2.624c1.52.472 1.52 2.562 0 3.034a1.75 1.75 0 00-1.016 2.624c.85 1.34-.627 2.817-1.967 1.967a1.75 1.75 0 00-2.624 1.016c-.472 1.52-2.562 1.52-3.034 0a1.75 1.75 0 00-2.624-1.016c-1.34.85-2.817-.627-1.967-1.967a1.75 1.75 0 00-1.016-2.624c-1.52-.472-1.52-2.562 0-3.034a1.75 1.75 0 001.016-2.624c-.85-1.34.627-2.817 1.967-1.967a1.75 1.75 0 002.624-1.016z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
                <span class="mt-1.5 block text-[11px] font-bold leading-tight">Settings</span>
            </a>
            <a href="{{ route('admin.inventory.ledger') }}" class="group rounded-xl border border-white/15 bg-white/95 px-2 py-2 text-center text-slate-700 hover:bg-blue-50 transition">
                <span class="mx-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#123785] text-white group-hover:bg-[#0f2c77]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14l-8 4m-8-4v10l8 4m-8-14l8 4m0 0v10"/>
                    </svg>
                </span>
                <span class="mt-1.5 block text-[11px] font-bold leading-tight">Inventory Ledger</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Total Patients</p>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">👥</span>
            </div>
            <p class="mt-2 text-4xl font-black leading-none text-slate-800">{{ $totalPatients }}</p>
            <p class="mt-3 text-[10px] font-semibold uppercase text-emerald-600">Live registry count</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Today's Patients</p>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">📅</span>
            </div>
            <p class="mt-2 text-4xl font-black leading-none text-slate-800">{{ $todaysPatients }}</p>
            <p class="mt-3 text-[10px] font-semibold uppercase text-slate-500">Current day census</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Total Consultations</p>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">🩺</span>
            </div>
            <p class="mt-2 text-4xl font-black leading-none text-slate-800">{{ $totalConsultations }}</p>
            <p class="mt-3 text-[10px] font-semibold uppercase text-indigo-600">All-time consultations</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-amber-500">Pending Consultations</p>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">⏳</span>
            </div>
            <p class="mt-2 text-4xl font-black leading-none text-amber-600">{{ $pendingConsultations }}</p>
            <p class="mt-3 text-[10px] font-semibold uppercase text-amber-600">Needs doctor review</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-rose-500">Low Stock Medicines</p>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">⚠️</span>
            </div>
            <p class="mt-2 text-4xl font-black leading-none text-rose-600">{{ $lowStockMedicines->count() }}</p>
            <p class="mt-3 text-[10px] font-semibold uppercase text-rose-600">Critical inventory</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                    <span class="text-blue-500">🧾</span> Recent Activity Logs
                </h2>
                <a href="{{ route('admin.activity-logs.index') }}" class="text-sm font-bold text-blue-600 hover:text-blue-700">View All</a>
            </div>
            <div id="recentActivityLogs" class="divide-y divide-slate-100">
                @forelse($recentLogs as $log)
                    @php
                        $displayName = $log->user?->full_name ?? 'System';
                        $nameParts = preg_split('/\s+/', trim((string) $displayName)) ?: [];
                        $avatarInitials = strtoupper(
                            (isset($nameParts[0][0]) ? $nameParts[0][0] : 'S') .
                            (isset($nameParts[1][0]) ? $nameParts[1][0] : '')
                        );
                        $avatarPalette = ['bg-blue-600', 'bg-slate-500', 'bg-emerald-600', 'bg-amber-700', 'bg-indigo-600'];
                        $avatarClass = $avatarPalette[crc32((string) $displayName) % count($avatarPalette)];
                    @endphp
                    <div class="recent-log-item flex items-start justify-between gap-3 px-5 py-3">
                        <div class="min-w-0 flex items-start gap-3">
                            @if(!empty($log->user?->profile_photo_path))
                                <img
                                    src="{{ asset('storage/'.$log->user->profile_photo_path) }}"
                                    alt="{{ $displayName }}"
                                    class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0"
                                >
                            @else
                                <div class="w-8 h-8 rounded-full {{ $avatarClass }} text-white text-[11px] font-black flex items-center justify-center shrink-0">
                                    {{ $avatarInitials }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-base font-black text-slate-800 truncate">{{ $displayName }} - {{ $log->action }}</p>
                                <p class="text-sm text-slate-500">{{ $log->description }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 text-xs font-semibold text-slate-400 mt-1">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-500">No recent logs.</p>
                @endforelse
            </div>
            <div id="recentActivityPagination" class="border-t border-slate-100 px-5 py-3"></div>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-xl font-black text-slate-800 mb-3 flex items-center gap-2">
                    <span class="text-blue-500">📋</span> Patient Insights
                </h3>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-slate-700">Doctor Availability</p>
                        <p class="text-sm mt-1 {{ $doctorAvailable ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $doctorAvailable ? 'Doctor is currently available.' : 'Doctor is currently not available.' }}
                        </p>
                    </div>
                    <span class="w-10 h-10 rounded-full {{ $doctorAvailable ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }} text-xl flex items-center justify-center">
                        {{ $doctorAvailable ? '✓' : '!' }}
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-xl font-black text-slate-800 mb-4 flex items-center gap-2">
                    <span class="text-blue-500">📈</span> Weekly Patients Trend
                </h3>

                @if($weeklyPatients->count() > 0)
                    <div class="relative h-64 rounded-xl border border-slate-100 bg-white p-3">
                        <canvas id="weeklyPatientsTrendChart"></canvas>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                        No recent patient trend data.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const logItems = Array.from(document.querySelectorAll('#recentActivityLogs .recent-log-item'));
        if (logItems.length === 0) return;

        const pager = $('#recentActivityPagination');
        pager.pagination({
            dataSource: logItems,
            pageSize: 10,
            showSizeChanger: false,
            callback: function (data) {
                logItems.forEach(item => item.style.display = 'none');
                data.forEach(item => item.style.display = '');
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('weeklyPatientsTrendChart');
        if (!canvas || typeof Chart === 'undefined') return;

        const rawRows = @json($weeklyPatients->values()->map(function ($row) {
            return [
                'label' => \Carbon\Carbon::parse($row->day)->format('D M d'),
                'value' => (int) $row->total,
            ];
        }));

        if (!Array.isArray(rawRows) || rawRows.length === 0) return;

        const labels = rawRows.map(row => row.label);
        const values = rawRows.map(row => row.value);

        const ctx = canvas.getContext('2d');
        const gradientA = ctx.createLinearGradient(0, 0, 0, 260);
        gradientA.addColorStop(0, 'rgba(16, 185, 129, 0.45)');
        gradientA.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        data: values,
                        borderColor: '#059669',
                        backgroundColor: gradientA,
                        fill: true,
                        tension: 0.2,
                        pointRadius: 4,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#16a34a',
                        pointBorderColor: '#16a34a',
                        pointBorderWidth: 0,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#f8fafc',
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            color: '#475569',
                            font: { size: 11, weight: '600' }
                        },
                        border: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            stepSize: 2,
                            color: '#475569',
                            font: { size: 11, weight: '600' }
                        },
                        grid: {
                            color: '#e2e8f0',
                            lineWidth: 1
                        },
                        border: { display: false }
                    }
                },
                elements: {
                    line: { borderWidth: 3 }
                }
            }
        });
    });
</script>
@endsection
