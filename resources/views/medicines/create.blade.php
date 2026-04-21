@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-6 px-4">
    <div class="mb-6">
        <a href="{{ route('medicines.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-3 text-sm font-medium transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Inventory
        </a>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Add New Medicine</h1>
        <p class="text-gray-500 text-sm mt-1">Input the brand and generic details to register the medicine.</p>
    </div>

    <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100 p-8">
        <form action="{{ route('medicines.store') }}" method="POST" id="medicineForm">
            @csrf
            
            <input type="hidden" name="stock" value="0">
            <input type="hidden" name="expiration_date" value="{{ now()->addYear()->format('Y-m-d') }}">
            <input type="hidden" name="arrival_date" value="{{ now()->format('Y-m-d') }}">
            
            {{-- This hidden input will hold the combined name sent to the database --}}
            <input type="hidden" name="name" id="combined_name">

            <div class="space-y-5">
                {{-- Generic Name Search Dropdown --}}
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Generic Name</label>
                    <div class="space-y-2">
                        <input type="text" id="generic_name" list="genericOptions" placeholder="Search or type generic name..." required
                            class="w-full px-5 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition">
                        <datalist id="genericOptions">
                            <option value="Paracetamol"></option>
                            <option value="Amoxicillin"></option>
                            <option value="Ibuprofen"></option>
                            <option value="Cetirizine"></option>
                            <option value="Loratadine"></option>
                            <option value="Mefenamic Acid"></option>
                            <option value="Omeprazole"></option>
                            <option value="Metformin"></option>
                            <option value="Amlodipine"></option>
                            <option value="Losartan"></option>
                            <option value="Salbutamol"></option>
                            <option value="Azithromycin"></option>
                            <option value="Doxycycline"></option>
                            <option value="Ciprofloxacin"></option>
                            <option value="Co-Amoxiclav"></option>
                            <option value="Vitamin C"></option>
                            <option value="Ferrous Sulfate"></option>
                        </datalist>
                        <div class="flex items-center gap-2">
                            <button type="button" id="addGenericBtn"
                                class="hidden px-3 py-1.5 text-xs font-bold rounded-lg border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition">
                                + Add this generic name
                            </button>
                            <span id="genericAddedMsg" class="hidden text-xs font-semibold text-emerald-600">Added to list</span>
                        </div>
                    </div>
                </div>

                {{-- Brand Name Input --}}
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Brand Name</label>
                    <input type="text" id="brand_name" placeholder="e.g. Biogesic" required autofocus
                        class="w-full px-5 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition">
                </div>

                {{-- Dosage Input --}}
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Dosage (mg)</label>
                    <input type="number" id="dosage_mg" placeholder="e.g. 500" min="1" step="1" required
                        class="w-full px-5 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition">
                    <p class="mt-2 text-[10px] text-gray-400 italic">Example output: Brand (Generic Name) 500mg</p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-50 flex items-center gap-3">
                <button type="submit" class="flex-1 py-3.5 bg-blue-600 text-white text-sm font-black rounded-xl hover:bg-blue-700 shadow-md transition transform hover:-translate-y-0.5">
                    Save to Inventory
                </button>
                <a href="{{ route('medicines.index') }}" class="flex-1 py-3.5 bg-gray-50 text-gray-500 text-sm font-bold rounded-xl hover:bg-gray-100 transition text-center border border-gray-100">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    const genericInput = document.getElementById('generic_name');
    const genericList = document.getElementById('genericOptions');
    const addGenericBtn = document.getElementById('addGenericBtn');
    const genericAddedMsg = document.getElementById('genericAddedMsg');

    function currentGenericValues() {
        return Array.from(genericList.options).map(option => option.value.trim().toLowerCase());
    }

    function updateAddGenericVisibility() {
        const value = genericInput.value.trim().toLowerCase();
        const hasValue = value.length > 0;
        const exists = currentGenericValues().includes(value);
        addGenericBtn.classList.toggle('hidden', !hasValue || exists);
        genericAddedMsg.classList.add('hidden');
    }

    genericInput.addEventListener('input', updateAddGenericVisibility);

    addGenericBtn.addEventListener('click', function () {
        const value = genericInput.value.trim();
        if (!value) return;

        const exists = currentGenericValues().includes(value.toLowerCase());
        if (exists) {
            addGenericBtn.classList.add('hidden');
            return;
        }

        const option = document.createElement('option');
        option.value = value;
        genericList.appendChild(option);

        addGenericBtn.classList.add('hidden');
        genericAddedMsg.classList.remove('hidden');
    });

    document.getElementById('medicineForm').addEventListener('submit', function(e) {
        const brand = document.getElementById('brand_name').value.trim();
        const generic = document.getElementById('generic_name').value.trim();
        const dosage = document.getElementById('dosage_mg').value.trim();
        
        // Combines them into "Brand (Generic) Dosagemg" format before sending to Controller
        document.getElementById('combined_name').value = `${brand} (${generic}) ${dosage}mg`;
    });
</script>
@endsection