@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Admin Control Center</h1>
            <p class="text-sm text-slate-500">Centralized operations and system insights.</p>
        </div>
        <p class="text-sm text-slate-500">{{ now()->format('F d, Y') }}</p>
    </div>

    <div class="grid md:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-slate-500">Total Patients</p><p class="text-2xl font-bold">{{ $totalPatients }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-slate-500">Today's Patients</p><p class="text-2xl font-bold">{{ $todaysPatients }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-slate-500">Total Consultations</p><p class="text-2xl font-bold">{{ $totalConsultations }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-slate-500">Pending Consultations</p><p class="text-2xl font-bold text-amber-600">{{ $pendingConsultations }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-slate-500">Low Stock Medicines</p><p class="text-2xl font-bold text-red-600">{{ $lowStockMedicines->count() }}</p></div>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-slate-500">Recovered Patients</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $recoveryAnalytics['recovered_patients'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-slate-500">Patients With Repeated Symptoms</p>
            <p class="text-2xl font-bold text-orange-600">{{ $recoveryAnalytics['repeated_symptoms_patients'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-slate-500">Unresolved Consultations</p>
            <p class="text-2xl font-bold text-red-600">{{ $recoveryAnalytics['unresolved_consultation_count'] ?? 0 }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border p-5">
            <h2 class="font-bold mb-3">Recent Activity Logs</h2>
            <div id="recentActivityLogs" class="space-y-3">
                @forelse($recentLogs as $log)
                    <div class="text-sm border-b pb-2 recent-log-item">
                        <p class="font-medium">{{ $log->user?->full_name ?? 'System' }} - {{ $log->action }}</p>
                        <p class="text-slate-500">{{ $log->description }}</p>
                        <p class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No recent logs.</p>
                @endforelse
            </div>
            <div id="recentActivityPagination" class="mt-4"></div>
        </div>

        <div class="bg-white rounded-xl border p-5">
            <h2 class="font-bold mb-3">Weekly Patients</h2>
            <div class="space-y-2">
                @forelse($weeklyPatients as $row)
                    <div class="flex justify-between text-sm">
                        <span>{{ \Carbon\Carbon::parse($row->day)->format('D, M d') }}</span>
                        <span class="font-semibold">{{ $row->total }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No recent patient data.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

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
</script>
@endsection
