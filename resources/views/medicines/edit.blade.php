@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('medicines.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2 font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Inventory
            </a>
        </div>
        <h1 class="text-3xl font-bold text-gray-800">Inventory History</h1>
        <p class="text-gray-500 mt-1">Stock-in and dispense logs for <span class="font-semibold text-slate-700">{{ $medicine->name }}</span>.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="space-y-4">
            @forelse($inventoryLogs as $log)
                @php
                    $actionLabel = match($log->transaction_type) {
                        'stock_in' => 'Stock-In',
                        'stock_out' => 'Dispense',
                        default => 'Adjustment',
                    };
                    $actionClass = match($log->transaction_type) {
                        'stock_in' => 'bg-emerald-100 text-emerald-700',
                        'stock_out' => 'bg-blue-100 text-blue-700',
                        default => 'bg-slate-100 text-slate-700',
                    };
                @endphp
                <div class="rounded-xl border border-gray-100 p-4 bg-gray-50/40">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 text-sm">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">User</p>
                            <p class="font-semibold text-slate-800">{{ $log->user?->full_name ?? 'System' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Medicine Name</p>
                            <p class="font-semibold text-slate-800">{{ $log->medicine?->name ?? $medicine->name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Quantity</p>
                            <p class="font-semibold text-slate-800">{{ abs((int) $log->quantity) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Date/Time</p>
                            <p class="font-semibold text-slate-800">{{ $log->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Action Type</p>
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold {{ $actionClass }}">{{ $actionLabel }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center border border-dashed border-gray-200 rounded-xl">
                    <p class="text-gray-400 font-semibold">No inventory logs recorded for this medicine yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection