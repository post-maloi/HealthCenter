@extends('layouts.app')

@section('content')
@php
    $role = auth()->user()->role ?? 'bhw';
    $isNurse = $role === 'nurse';
    $isBhw = $role === 'bhw';
@endphp
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="max-w-4xl mx-auto py-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Edit Consultation Record</h2>
        </div>

        <form action="{{ route('record.update', $record->id) }}" method="POST" class="p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Name Section - CRITICAL: These must match existing records for history to work --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $record->first_name) }}" required {{ $isNurse ? 'readonly' : '' }}
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name', $record->middle_name) }}" {{ $isNurse ? 'readonly' : '' }}
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $record->last_name) }}" required {{ $isNurse ? 'readonly' : '' }}
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-gray-50">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Date of Consultation</label>
                    <input type="date" name="consultation_date" value="{{ old('consultation_date', $record->consultation_date->format('Y-m-d')) }}" required {{ $isNurse ? 'readonly' : '' }}
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Birthday</label>
                    <input type="date" name="birthday" id="birthday" value="{{ old('birthday', $record->birthday->format('Y-m-d')) }}" onchange="calculateAge()" required {{ $isNurse ? 'readonly' : '' }}
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-gray-50">
                </div>
            </div>

            {{-- Contact & Address Section - Added to prevent "disappearing" identity --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number', $record->contact_number) }}" {{ $isNurse ? 'readonly' : '' }}
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Purok / Address</label>
                    @if($isBhw)
                        @php
                            $defaultAddressOptions = collect(['BAYANIHAN', 'TABUNON', 'GABI', 'BALAANONG TUBIG', 'BALAHAN', 'RIBOMA', 'PAG-ASA', 'PUROK-ANO']);
                            $selectedAddress = old('address_purok', $record->address_purok);
                            $addressList = $defaultAddressOptions
                                ->merge($addressOptions ?? collect())
                                ->push($selectedAddress)
                                ->filter()
                                ->map(fn ($value) => strtoupper(trim((string) $value)))
                                ->unique()
                                ->values();
                        @endphp
                        <select name="address_purok" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-white">
                            <option value="" disabled {{ $selectedAddress ? '' : 'selected' }}>Select address</option>
                            @foreach($addressList as $address)
                                <option value="{{ $address }}" {{ strtoupper((string) $selectedAddress) === $address ? 'selected' : '' }}>
                                    {{ $address }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="address_purok" value="{{ old('address_purok', $record->address_purok) }}" required {{ $isNurse ? 'readonly' : '' }}
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition">
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Gender</label>
                    <select name="gender" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-white" {{ $isNurse ? 'disabled' : '' }}>
                        <option value="Male" {{ $record->gender == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ $record->gender == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Civil Status</label>
                    <select name="civil_status" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition bg-white" {{ $isNurse ? 'disabled' : '' }}>
                        <option value="Single" {{ $record->civil_status == 'Single' ? 'selected' : '' }}>Single</option>
                        <option value="Married" {{ $record->civil_status == 'Married' ? 'selected' : '' }}>Married</option>
                        <option value="Widowed" {{ $record->civil_status == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                        <option value="Separated" {{ $record->civil_status == 'Separated' ? 'selected' : '' }}>Separated</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Age</label>
                    <input type="text" id="age_display" placeholder="Auto-calculated" disabled
                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 text-gray-900 outline-none cursor-not-allowed">
                </div>
            </div>

            @if($isNurse)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Temperature</label>
                    <input type="text" name="temp" value="{{ old('temp', $record->temp) }}" class="w-full px-4 py-3 rounded-xl border border-gray-200">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Blood Pressure</label>
                    <input type="text" name="bp" value="{{ old('bp', $record->bp) }}" class="w-full px-4 py-3 rounded-xl border border-gray-200">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Weight (kg)</label>
                    <input type="number" step="0.1" name="weight" value="{{ old('weight', $record->weight) }}" class="w-full px-4 py-3 rounded-xl border border-gray-200">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Triage</label>
                    <input type="text" name="triage" value="{{ old('triage') }}" placeholder="e.g. Urgent / Non-Urgent" class="w-full px-4 py-3 rounded-xl border border-gray-200">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Monitoring Notes</label>
                    <input type="text" name="monitoring_notes" value="{{ old('monitoring_notes') }}" placeholder="Patient monitoring notes" class="w-full px-4 py-3 rounded-xl border border-gray-200">
                </div>
            </div>
            <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl text-sm text-amber-700">
                Nurse scope: vitals, triage, and monitoring only. Status remains pending.
            </div>
            @elseif($isBhw)
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Diagnosis</label>
                <textarea rows="3" readonly
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-500 outline-none cursor-not-allowed">{{ old('diagnosis', $record->diagnosis) }}</textarea>
            </div>

            <div class="border border-slate-100 rounded-2xl p-6 mt-8 bg-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-slate-500 uppercase">Medicines Given</h3>
                </div>
                <div class="p-3 rounded-xl border border-dashed border-amber-200 bg-amber-50 text-xs font-semibold text-amber-700">
                    BHW cannot edit diagnosis and medicine entries.
                </div>
            </div>
            @else
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Diagnosis</label>
                <textarea name="diagnosis" rows="3" placeholder="Describe symptoms/results" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none transition">{{ old('diagnosis', $record->diagnosis) }}</textarea>
            </div>

            {{-- Medicines Given Section --}}
            <div class="border border-slate-100 rounded-2xl p-6 mt-8 bg-white">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-sm font-bold text-slate-500 uppercase">Medicines Given</h3>
                    <button type="button" id="add-medicine-btn" class="flex items-center gap-2 bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-xl hover:bg-blue-700 transition shadow-md">
                        + ADD MEDICINE
                    </button>
                </div>

                <div id="medicine-rows-container" class="space-y-4">
                    {{-- Row injection --}}
                </div>
            </div>
            @endif

            <div class="pt-6 flex gap-4">
                <button type="submit" class="flex-grow bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 shadow-lg transition">
                    Update Record
                </button>
                <a href="{{ route('record.index') }}" class="px-8 py-4 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Data Bridge --}}
<div id="medicine-data" 
     data-all-meds='@json($allMedicines ?? [])' 
     data-existing-meds='@json($record->medicines ?? [])' 
     style="display:none;"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
        const dataProvider = document.getElementById('medicine-data');
        const allMedicines = JSON.parse(dataProvider.dataset.allMeds);
        const existingMeds = JSON.parse(dataProvider.dataset.existingMeds);

        const container = document.getElementById('medicine-rows-container');
        const addBtn = document.getElementById('add-medicine-btn');
        let rowIndex = 0;

        function createMedicineRow(selectedId = null, quantity = 1) {
            const selectId = `select-med-${rowIndex}`;
            const div = document.createElement('div');
            div.className = "flex items-center gap-4 transition-all duration-300 opacity-0 transform -translate-y-2";
            
            let options = '<option value="" disabled selected>Search medicine...</option>';
            allMedicines.forEach(med => {
                const isSelected = selectedId == med.id ? 'selected' : '';
                options += `<option value="${med.id}" ${isSelected}>${med.name} (Stock: ${med.stock})</option>`;
            });

            div.innerHTML = `
                <div class="flex-1">
                    <select name="medicines[${rowIndex}][id]" id="${selectId}" required class="w-full">
                        ${options}
                    </select>
                </div>
                <div class="w-32">
                    <input type="number" name="medicines[${rowIndex}][quantity]" value="${quantity}" required min="1" 
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-xl outline-none text-sm h-[48px]">
                </div>
                <button type="button" class="text-red-300 hover:text-red-500 text-2xl px-2" onclick="this.parentElement.remove()">
                    &times;
                </button>
            `;

            container.appendChild(div);
            $(`#${selectId}`).select2({ placeholder: "Search medicine...", width: '100%' });
            requestAnimationFrame(() => div.classList.remove('opacity-0', '-translate-y-2'));
            rowIndex++;
        }

        if (existingMeds.length > 0) {
            existingMeds.forEach(med => createMedicineRow(med.id, med.pivot.quantity));
        } else {
            createMedicineRow();
        }

        addBtn.addEventListener('click', () => createMedicineRow());
        calculateAge();
    });
</script>

<style>
    .select2-container--default .select2-selection--single {
        height: 48px !important;
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        display: flex;
        align-items: center;
        padding-left: 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px !important; }
</style>
@endsection