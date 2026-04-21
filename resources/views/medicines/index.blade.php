{{-- resources/views/medicines/index.blade.php --}}
@extends('layouts.app')

@section('content')
{{-- Load Alpine.js for floating overlays --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="max-w-7xl mx-auto px-4 py-6" x-data="{ openDetails: null, openAddLot: null }">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Inventory Medicine</h1>
            <p class="text-gray-500 text-sm mt-1">Full inventory history. Newest arrivals shown in table.</p>
        </div>
        <a href="{{ route('medicines.create') }}" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-lg shadow-sm hover:bg-blue-700 transition">
            + Add Medicine
        </a>
    </div>

    {{-- Search Bar --}}
    <div class="mb-6 relative max-w-md">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        </span>
        <input type="text" id="inventorySearch" onkeyup="runFilters()" placeholder="Search medicine..." class="pl-10 pr-4 py-3 w-full rounded-xl border border-gray-200 outline-none shadow-sm focus:border-blue-500 transition">
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 table-fixed" id="inventoryTable">
            <thead class="bg-slate-50">
                <tr>
                    {{-- Set a fixed width for the name to force wrapping --}}
                    <th class="w-1/3 px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Medicine Name</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Date Received</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Total Stock</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Expiry Date</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="inventoryTableBody" class="divide-y divide-gray-100">
                @forelse($medicines->groupBy('name') as $name => $lots)
                @php 
                    $totalStock = $lots->sum('stock');
                    $latestWithStock = $lots->where('stock', '>', 0)
                                            ->whereNotNull('arrival_date')
                                            ->sortByDesc('arrival_date')
                                            ->first(); 
                    $historyLots = $lots->where('stock', '>', 0)->sortBy('arrival_date');
                    $activeLots = $lots->where('stock', '>', 0);
                    $expiryLot = $activeLots->whereNotNull('expiration_date')->sortBy('expiration_date')->first();
                @endphp
                <tr class="hover:bg-gray-50 transition inventory-row">
                    {{-- whitespace-normal allows the long name to wrap --}}
                    <td class="px-6 py-4 whitespace-normal">
                        <div class="text-sm font-bold text-slate-800 medicine-name break-words">{{ $name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                        @if($latestWithStock && $latestWithStock->arrival_date)
                            {{ $latestWithStock->arrival_date->format('M d, Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $totalStock <= 0 ? 'text-red-500' : 'text-slate-700' }}">
                        {{ $totalStock }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                        @if($expiryLot && $expiryLot->expiration_date)
                            {{ $expiryLot->expiration_date->format('M d, Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    
                    {{-- w-px and whitespace-nowrap prevents the buttons from disappearing or stacking --}}
                    <td class="px-6 py-4 whitespace-nowrap text-center w-px">
                        <div class="flex justify-center items-center gap-2">
                            <button @click="openDetails = '{{ $loop->index }}'" class="flex items-center gap-2 px-4 py-2 bg-[#E9F3F1] text-[#2D8A80] rounded-xl hover:opacity-80 transition shadow-sm font-bold text-xs shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                View Details
                            </button>

                            <button @click="openAddLot = '{{ $loop->index }}'" class="p-2 bg-[#ECFDF5] text-[#10B981] rounded-xl hover:opacity-80 transition shadow-sm shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                            </button>

                            <form action="{{ route('medicines.destroy_group', ['name' => $name]) }}" method="POST" onsubmit="return confirm('Delete all batches and history for {{ $name }}?')" class="m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 bg-[#FFF1F1] text-[#FF5C5C] rounded-xl hover:opacity-80 transition shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>

                        {{-- MODAL 1: VIEW DETAILS --}}
                        <div x-show="openDetails == '{{ $loop->index }}'" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" x-transition.opacity>
                            <div @click.away="openDetails = null" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-hidden flex flex-col text-left whitespace-normal">
                                <div class="px-10 py-8 border-b border-gray-50 flex justify-between items-center shrink-0">
                                    <div class="pr-8">
                                        <h2 class="text-2xl font-black text-gray-900 uppercase tracking-tight break-words">{{ $name }}</h2>
                                        <p class="text-gray-400 text-xs font-bold mt-1 uppercase tracking-widest">Inventory History</p>
                                    </div>
                                    <button @click="openDetails = null" class="text-gray-300 hover:text-gray-600 transition text-2xl font-bold">✕</button>
                                </div>
                                <div class="p-10 overflow-y-auto space-y-6 bg-gray-50/30 flex flex-col-reverse">
                                    @forelse($historyLots as $lotIndex => $lot)
                                    <div class="bg-white border {{ $lot->stock <= 0 ? 'border-gray-50 bg-gray-50/50' : 'border-gray-100' }} rounded-[2rem] p-8 flex items-center gap-8 relative shadow-sm transition hover:shadow-md mb-6 last:mb-0">
                                       

                                        <div class="grow grid grid-cols-3 gap-8 {{ $lot->stock <= 0 ? 'opacity-60' : '' }}">
                                            <div class="text-left">
                                                <h4 class="font-black text-gray-800 text-lg uppercase">{{ $lot->batch_number ?? 'LOT-'.$lot->id }}</h4>
                                                <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest block mt-1">
                                                    Received: @if($lot->arrival_date) {{ $lot->arrival_date->format('M d, Y') }} @else N/A @endif
                                                </span>
                                                @if($lot->stock <= 0)
                                                    <span class="text-[10px] text-red-400 font-black uppercase mt-1 block italic">Out of Stock</span>
                                                @endif
                                            </div>
                                            <div class="text-left text-sm font-bold text-gray-600 flex flex-col justify-center">
                                                <p class="text-[9px] text-gray-400 uppercase tracking-widest">Expiry</p>
                                                <p>@if($lot->expiration_date) {{ $lot->expiration_date->format('M d, Y') }} @else N/A @endif</p>
                                            </div>
                                            <div class="text-right font-black text-slate-800 flex flex-col justify-center">
                                                <p class="text-[9px] text-gray-400 uppercase tracking-widest">Current Stock</p>
                                                <span>{{ $lot->stock }} <small class="text-xs text-slate-400 tracking-normal">units</small></span>
                                            </div>
                                        </div>

                                        <div class="flex flex-col gap-2 pl-6 border-l border-gray-50">
                                            <a href="{{ route('medicines.edit', $lot) }}" class="px-5 py-2 bg-[#E9F3F1] text-[#2D8A80] rounded-xl text-[10px] font-black uppercase text-center hover:bg-teal-50 transition">Edit</a>
                                            <form action="{{ route('medicines.destroy', $lot) }}" method="POST" onsubmit="return confirm('Remove this batch?')" class="m-0">
                                                @csrf @method('DELETE')
                                                <button class="px-5 py-2 bg-[#FFF1F1] text-[#FF5C5C] rounded-xl text-[10px] font-black uppercase hover:bg-red-50 transition">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="bg-white border-2 border-dashed border-gray-200 rounded-[2rem] p-12 text-center w-full">
                                        <p class="text-gray-400 font-bold uppercase tracking-widest text-sm">No stocks recorded</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- MODAL 2: ADD NEW LOT --}}
                        <div x-show="openAddLot == '{{ $loop->index }}'" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" x-transition.opacity>
                            <div @click.away="openAddLot = null" class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden text-left whitespace-normal">
                                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center">
                                    <div>
                                        <h2 class="text-2xl font-bold text-gray-900">Add New Stock</h2>
                                        <p class="text-gray-500 text-xs font-bold uppercase break-words">{{ $name }}</p>
                                    </div>
                                    <button @click="openAddLot = null" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
                                </div>
                                <form action="{{ route('medicines.store') }}" method="POST" class="p-8 space-y-5">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $name }}">
                                    <div><label class="block text-xs font-black text-gray-400 uppercase mb-1 tracking-widest">Stock Number</label><input type="text" name="batch_number" placeholder="ABC-000" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none shadow-sm" required></div>
                                    <div><label class="block text-xs font-black text-gray-400 uppercase mb-1 tracking-widest">Date Received</label><input type="date" name="arrival_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none shadow-sm" required></div>
                                    <div><label class="block text-xs font-black text-gray-400 uppercase mb-1 tracking-widest">Expiration Date</label><input type="date" name="expiration_date" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none shadow-sm" required></div>
                                    <div><label class="block text-xs font-black text-gray-400 uppercase mb-1 tracking-widest">Quantity</label><input type="number" name="stock" value="1" min="1" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none shadow-sm" required></div>
                                    <div class="flex gap-3 pt-4">
                                        <button type="submit" class="flex-grow py-4 bg-blue-600 text-white font-black rounded-xl shadow-lg hover:bg-blue-700 transition uppercase text-xs">Add Stock</button>
                                        <button type="button" @click="openAddLot = null" class="px-6 py-4 bg-gray-50 text-gray-500 font-bold rounded-xl text-xs uppercase">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="inventoryPagination" class="mt-4"></div>
    <div id="inventoryEmptyMessage" class="px-6 py-8 text-center text-gray-400 italic hidden">No medicine records found.</div>
</div>

<script>
    const inventoryRows = Array.from(document.querySelectorAll("#inventoryTableBody .inventory-row")).map((row) => {
        const name = row.querySelector(".medicine-name")?.textContent.toUpperCase() ?? "";
        return { element: row, name };
    });

    function renderInventoryPagination(filteredRows) {
        const pager = $('#inventoryPagination');
        const emptyMessage = document.getElementById("inventoryEmptyMessage");

        if (pager.data('pagination')) {
            pager.pagination('destroy');
        }

        inventoryRows.forEach(item => {
            item.element.style.display = 'none';
        });

        if (filteredRows.length === 0) {
            emptyMessage.classList.remove('hidden');
            return;
        }

        emptyMessage.classList.add('hidden');

        pager.pagination({
            dataSource: filteredRows,
            pageSize: 10,
            showSizeChanger: false,
            callback: function (data) {
                inventoryRows.forEach(item => {
                    item.element.style.display = 'none';
                });
                data.forEach(item => {
                    item.element.style.display = '';
                });
            }
        });
    }

    function runFilters() {
        const input = document.getElementById("inventorySearch").value.toUpperCase();
        const filtered = inventoryRows.filter(row => row.name.includes(input));
        renderInventoryPagination(filtered);
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderInventoryPagination(inventoryRows);
    });
</script>
@endsection