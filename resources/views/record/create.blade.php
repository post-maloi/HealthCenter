@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Add New Consultation</h2>
        </div>

        <form action="{{ route('record.store') }}" method="POST" class="p-8 space-y-6">
            @csrf

            {{-- Name Section --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">First Name</label>
                    <input type="text" name="first_name" placeholder="First Name" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Middle Name</label>
                    <input type="text" name="middle_name" placeholder="Optional"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Last Name</label>
                    <input type="text" name="last_name" placeholder="Last Name" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-gray-50">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Date of Consultation</label>
                    <input type="date" name="consultation_date" value="{{ date('Y-m-d') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Birthday</label>
                    <input type="date" name="birthday" id="birthday" onchange="calculateAge()" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-gray-50">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Gender</label>
                    <select name="gender" required 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-white">
                        <option value="" disabled selected>Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Civil Status</label>
                    <select name="civil_status" required 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-white">
                        <option value="" disabled selected>Select Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                        <option value="Separated">Separated</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Age</label>
                    <input type="text" id="age_display" placeholder="Auto-calculated" disabled
                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 text-gray-900 outline-none cursor-not-allowed">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Contact Number</label>
                    <input type="text" name="contact_number" placeholder="09xxxxxxxxx"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Address / Purok</label>
                    <input type="text" name="address_purok" placeholder="Street, Purok, Brgy" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Diagnosis</label>
                <textarea name="diagnosis" rows="3" placeholder="Describe symptoms/results" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition"></textarea>
            </div>

            {{-- Optimized Medicines Given Section --}}
            <div class="border border-slate-100 rounded-2xl p-6 mt-8 bg-white">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-sm font-bold text-slate-500 uppercase">Medicines Given</h3>
                    
                    <button type="button" id="add-medicine-btn" class="flex items-center gap-2 bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-xl hover:bg-blue-700 transition active:scale-95 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        ADD MEDICINE
                    </button>
                </div>

                <div id="medicine-rows-container" class="space-y-4">
                    {{-- Rows are injected via JS --}}
                </div>
            </div>

            <div class="pt-6 flex gap-4">
                <button type="submit" class="flex-grow bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 shadow-lg transition">
                    Save Record
                </button>
                <a href="{{ route('record.index') }}" class="px-8 py-4 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function calculateAge() {
        const birthdayInput = document.getElementById('birthday').value;
        const display = document.getElementById('age_display');
        
        if (!birthdayInput) return;

        const birthDate = new Date(birthdayInput);
        const today = new Date();
        
        let years = today.getFullYear() - birthDate.getFullYear();
        let months = today.getMonth() - birthDate.getMonth();

        if (months < 0 || (months === 0 && today.getDate() < birthDate.getDate())) {
            years--;
            months += 12;
        }

        display.value = (years === 0) ? `${months} Mon` : `${years} yrs`;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('medicine-rows-container');
        const addBtn = document.getElementById('add-medicine-btn');
        let rowIndex = 0;

        // Medicines data from Controller
const allMedicines = {!! json_encode($allMedicines) !!};

        function createMedicineRow() {
            const rowId = `medicine-row-${rowIndex}`;
            const div = document.createElement('div');
            div.id = rowId;
            div.className = "flex items-center gap-4 transition-all duration-300 opacity-0 transform -translate-y-2";
            
            let options = '<option value="" disabled selected>Click to select medicine...</option>';
            allMedicines.forEach(med => {
                options += `<option value="${med.id}">${med.name} (Available: ${med.stock})</option>`;
            });

            div.innerHTML = `
                <div class="flex-1">
                    <select name="medicines[${rowIndex}][id]" required class="w-full px-4 py-2.5 border border-blue-600 rounded-xl focus:ring-1 focus:ring-blue-600 outline-none text-sm transition bg-white shadow-sm">
                        ${options}
                    </select>
                </div>
                <div class="w-32">
                    <input type="number" name="medicines[${rowIndex}][quantity]" placeholder="Qty" required min="1" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-1 focus:ring-slate-400 outline-none text-sm placeholder:text-slate-300 transition shadow-sm">
                </div>
                <div>
                    <button type="button" class="text-red-300 hover:text-red-500 transition text-2xl px-2 leading-none" onclick="this.parentElement.parentElement.remove()">
                        &times;
                    </button>
                </div>
            `;

            container.appendChild(div);
            
            // Animation trigger
            requestAnimationFrame(() => {
                div.classList.remove('opacity-0', '-translate-y-2');
            });

            rowIndex++;
        }

        addBtn.addEventListener('click', createMedicineRow);

        // Auto-add first row
        createMedicineRow();
    });
</script>
@endsection