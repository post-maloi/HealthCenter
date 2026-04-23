@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Inventory Ledger</h1>
        <p class="text-sm text-slate-500">Append-only stock movement history.</p>
    </div>

    @if($lowStockMedicines->isNotEmpty())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <p class="font-semibold text-red-700">Low Stock Alert</p>
        <div class="text-sm text-red-700 mt-2">
            {{ $lowStockMedicines->map(fn($m) => "{$m->name} ({$m->total_stock})")->implode(', ') }}
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Medicine</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Qty</th>
                    <th class="px-4 py-3 text-left">Balance</th>
                    <th class="px-4 py-3 text-left">Reference</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                    <td class="px-4 py-3">{{ $log->medicine?->name }}</td>
                    <td class="px-4 py-3 uppercase">{{ $log->transaction_type }}</td>
                    <td class="px-4 py-3">{{ $log->quantity }}</td>
                    <td class="px-4 py-3 font-semibold">{{ $log->balance_after }}</td>
                    <td class="px-4 py-3">
                        @php
                            $referenceLabel = $log->reference ?: 'Inventory entry';
                            if (preg_match('/Dispensed for consultation #(\d+)/i', (string) $referenceLabel, $matches)) {
                                $consultationId = (int) $matches[1];
                                $patientName = $consultationNames[$consultationId] ?? null;
                                if ($patientName) {
                                    $referenceLabel = 'Dispensed for: ' . $patientName;
                                }
                            }
                            $actorLabel = $log->user?->full_name ?? 'System';
                            $actionByLabel = $log->transaction_type === 'stock_out' ? 'Dispensed by' : 'Logged by';
                        @endphp
                        <div class="font-semibold text-slate-800">{{ $referenceLabel }}</div>
                        <div class="text-xs text-slate-500">{{ $actionByLabel }}: {{ $actorLabel }}</div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No inventory logs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex items-center justify-between gap-4">
        <p class="text-xs text-slate-500">
            Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} results
        </p>

        @if ($logs->lastPage() > 1)
        <div class="inline-flex items-center overflow-hidden rounded-lg border border-slate-700 bg-slate-800 text-sm">
            @if ($logs->onFirstPage())
                <span class="px-3 py-2 text-slate-500">‹</span>
            @else
                <a href="{{ $logs->previousPageUrl() }}" class="px-3 py-2 text-slate-200 hover:bg-slate-700">‹</a>
            @endif

            @foreach ($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                @if ($page == $logs->currentPage())
                    <span class="px-3 py-2 bg-slate-600 text-white font-semibold border-x border-slate-700">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-3 py-2 text-slate-200 hover:bg-slate-700 border-x border-slate-700">{{ $page }}</a>
                @endif
            @endforeach

            @if ($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}" class="px-3 py-2 text-slate-200 hover:bg-slate-700">›</a>
            @else
                <span class="px-3 py-2 text-slate-500">›</span>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
