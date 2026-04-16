@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-6 px-4"> {{-- Reduced max-width and vertical padding --}}
    <div class="mb-6"> {{-- Reduced margin --}}
        <a href="{{ route('medicines.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-3 text-sm font-medium transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Inventory
        </a>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Add New Medicine</h1> {{-- Smaller Heading --}}
        <p class="text-gray-500 text-sm mt-1">Select the medicine to register in the system.</p>
    </div>

    <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100 p-8"> {{-- Slightly tighter padding and radius --}}
        <form action="{{ route('medicines.store') }}" method="POST">
            @csrf
            
            <input type="hidden" name="stock" value="0">
            <input type="hidden" name="expiration_date" value="{{ now()->addYear()->format('Y-m-d') }}">
            <input type="hidden" name="arrival_date" value="{{ now()->format('Y-m-d') }}">

            <div class="space-y-5">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Medicine Name</label>
                    <div class="relative">
                        <select name="name" required autofocus
                            class="w-full px-5 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition bg-white appearance-none cursor-pointer">
                            <option value="" disabled selected>Select a medicine...</option>
                            
                            {{-- Analgesics & Antipyretics --}}
                            <optgroup label="Pain & Fever">
                                <option value="Biogesic (Paracetamol 500mg Tablet)">Biogesic (Paracetamol 500mg Tablet)</option>
                                <option value="Alaxan FR (Ibuprofen 200mg + Paracetamol 325mg Capsule)">Alaxan FR (Ibuprofen + Paracetamol Capsule)</option>
                                <option value="Tempra (Paracetamol 120mg/5ml Syrup)">Tempra (Paracetamol 120mg/5ml Syrup)</option>
                                <option value="Advil (Ibuprofen 200mg Softgel)">Advil (Ibuprofen 200mg Softgel)</option>
                            </optgroup>

                            {{-- Cough, Cold & Allergy --}}
                            <optgroup label="Cough & Cold">
                                <option value="Neozep Forte (Phenylephrine + Chlorphenamine + Paracetamol Tablet)">Neozep Forte (Tablet)</option>
                                <option value="Bioflu (Phenylephrine + Chlorphenamine + Paracetamol Tablet)">Bioflu (Tablet)</option>
                                <option value="Solmux (Carbocisteine 500mg Capsule)">Solmux (Carbocisteine 500mg Capsule)</option>
                                <option value="Solmux Kids (Carbocisteine 200mg/5ml Syrup)">Solmux Kids (Carbocisteine Syrup)</option>
                                <option value="Allerta (Loratadine 10mg Tablet)">Allerta (Loratadine 10mg Tablet)</option>
                                <option value="Virlix (Cetirizine 10mg Tablet)">Virlix (Cetirizine 10mg Tablet)</option>
                            </optgroup>

                            {{-- Antibiotics & Infection --}}
                            <optgroup label="Antibiotics">
                                <option value="Amoxicillin (Generic 500mg Capsule)">Amoxicillin (Generic 500mg Capsule)</option>
                                <option value="Amoxicillin (Generic 250mg/5ml Syrup)">Amoxicillin (Generic 250mg/5ml Syrup)</option>
                                <option value="Bactrim (Sulfamethoxazole + Trimethoprim 400mg/80mg Tablet)">Bactrim (Tablet)</option>
                            </optgroup>

                            {{-- Digestive Health --}}
                            <optgroup label="Digestive Health">
                                <option value="Kremil-S (Aluminum Hydroxide + Magnesium Hydroxide Tablet)">Kremil-S (Antacid Tablet)</option>
                                <option value="Diatabs (Loperamide 2mg Capsule)">Diatabs (Loperamide 2mg Capsule)</option>
                                <option value="Gaviscon (Sodium Alginate + Bicarbonate Liquid Sachet/Syrup)">Gaviscon (Liquid Syrup)</option>
                                <option value="Buscopan (Hyoscine N-butylbromide 10mg Tablet)">Buscopan (10mg Tablet)</option>
                            </optgroup>

                            {{-- Vitamins & Supplements --}}
                            <optgroup label="Vitamins">
                                <option value="Enervon (Vitamin B-Complex + Vitamin C Tablet)">Enervon (Tablet)</option>
                                <option value="Ceelin (Ascorbic Acid 100mg/ml Drops)">Ceelin (Vitamin C Drops)</option>
                                <option value="Potencee (Ascorbic Acid 500mg Tablet)">Potencee (Vitamin C 500mg Tablet)</option>
                                <option value="Sangobion (Ferrous Gluconate + Vitamins Capsule)">Sangobion (Iron Capsule)</option>
                            </optgroup>
                        </select>
                        
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-5 text-gray-400">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                            </svg>
                        </div>
                    </div>
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
@endsection