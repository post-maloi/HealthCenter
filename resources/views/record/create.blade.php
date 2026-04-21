@extends('layouts.app')

@section('content')
{{-- Hidden data provider for JavaScript --}}
<div id="medicine-data" data-list='@json($allMedicines ?? [])' style="display: none;"></div>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="max-w-6xl mx-auto py-8 px-4">
    {{-- Error Alerts --}}
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-xl shadow-sm">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-bold">Please correct the following errors:</p>
                    <ul class="text-xs text-red-600 list-disc ml-5 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('record.store') }}" method="POST">
            @csrf

            {{-- Header Section --}}
            <div class="p-8 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 uppercase">Individual Treatment Record</h2>
                    <p class="text-sm text-gray-500 uppercase tracking-widest mt-1">Add New Consultation</p>
                </div>
                <div class="text-right">
                    <label class="block text-xs font-bold text-gray-400 uppercase">Consultation Date</label>
                    <input type="date" name="consultation_date" value="{{ old('consultation_date', \Carbon\Carbon::now()->format('Y-m-d')) }}" 
                        class="border-none bg-transparent font-bold text-gray-700 text-lg p-0 focus:ring-0 text-right outline-none">
                </div>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                    
                    {{-- LEFT COLUMN: PATIENT DATA --}}
                    <div class="lg:col-span-5 space-y-6">
                        <h3 class="font-bold text-blue-600 border-b pb-2 text-sm uppercase tracking-wider">Patient Information</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Full Name</label>
                                <div class="flex gap-2">
                                    <input type="text" name="last_name" placeholder="Last" value="{{ old('last_name') }}" required 
                                        class="w-1/3 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-blue-400 outline-none uppercase">
                                    <input type="text" name="first_name" placeholder="First" value="{{ old('first_name') }}" required 
                                        class="w-1/3 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-blue-400 outline-none uppercase">
                                    <input type="text" name="middle_name" placeholder="M.I." value="{{ old('middle_name') }}"
                                        class="w-1/4 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-blue-400 outline-none uppercase">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Birthday</label>
                                <input type="date" name="birthday" id="birthday" value="{{ old('birthday') }}" onchange="calculateAge()" required 
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-blue-400 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Age</label>
                                <input type="text" id="age_display" readonly placeholder="Auto"
                                    class="w-full px-3 py-2 rounded-lg bg-gray-50 border-gray-100 text-sm text-gray-500 outline-none cursor-default">
                                <input type="hidden" name="age" id="age_hidden" value="{{ old('age') }}">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Gender</label>
                                <select name="gender" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none">
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Civil Status</label>
                                <select name="civil_status" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none">
                                    <option value="Single" {{ old('civil_status') == 'Single' ? 'selected' : '' }}>Single</option>
                                    <option value="Married" {{ old('civil_status') == 'Married' ? 'selected' : '' }}>Married</option>
                                    <option value="Widowed" {{ old('civil_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                </select>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Address / Purok</label>
                                <input type="text" name="address_purok" value="{{ old('address_purok') }}" required
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-blue-400 outline-none uppercase">
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN: S.O.A.P. --}}
                    <div class="lg:col-span-7 space-y-6 border-l border-gray-100 lg:pl-10">
                        
                        {{-- S - Subjective --}}
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded font-bold text-xs">S</span>
                                <label class="text-xs font-bold text-gray-700 uppercase">Subjective Findings</label>
                            </div>
                            <textarea name="subjective" rows="2" placeholder="Patient's complaints..." class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-blue-100 outline-none">{{ old('subjective') }}</textarea>
                        </div>

                        {{-- O - Objective / Vital Signs --}}
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded font-bold text-xs">O</span>
                                <label class="text-xs font-bold text-gray-700 uppercase">Objective / Vitals</label>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div class="relative"><span class="absolute left-2 top-2 text-[10px] font-bold text-gray-400">T</span><input type="text" name="temp" value="{{ old('temp') }}" placeholder="°C" class="w-full pl-6 pr-2 py-2 border rounded-lg text-xs outline-none"></div>
                                <div class="relative"><span class="absolute left-2 top-2 text-[10px] font-bold text-gray-400">BP</span><input type="text" name="bp" value="{{ old('bp') }}" placeholder="0/0" class="w-full pl-8 pr-2 py-2 border rounded-lg text-xs outline-none"></div>
                                <div class="relative"><span class="absolute left-2 top-2 text-[10px] font-bold text-gray-400">PR</span><input type="text" name="pr" value="{{ old('pr') }}" placeholder="bpm" class="w-full pl-8 pr-2 py-2 border rounded-lg text-xs outline-none"></div>
                                <div class="relative"><span class="absolute left-2 top-2 text-[10px] font-bold text-gray-400">RR</span><input type="text" name="rr" value="{{ old('rr') }}" placeholder="cpm" class="w-full pl-8 pr-2 py-2 border rounded-lg text-xs outline-none"></div>
                                <div class="relative"><span class="absolute left-2 top-2 text-[10px] font-bold text-gray-400">WT</span><input type="number" step="0.1" id="weight" name="weight" value="{{ old('weight') }}" oninput="calculateBMI()" placeholder="kg" class="w-full pl-8 pr-2 py-2 border rounded-lg text-xs outline-none"></div>
                                <div class="relative"><span class="absolute left-2 top-2 text-[10px] font-bold text-gray-400">HT</span><input type="number" step="0.1" id="height" name="height" value="{{ old('height') }}" oninput="calculateBMI()" placeholder="cm" class="w-full pl-8 pr-2 py-2 border rounded-lg text-xs outline-none"></div>
                                <div class="relative col-span-2">
                                    <span class="absolute left-2 top-2 text-[10px] font-bold text-blue-500">BMI</span>
                                    <input type="text" id="bmi_result" name="bmi" value="{{ old('bmi') }}" readonly placeholder="Auto" class="w-full pl-10 pr-2 py-2 border border-blue-100 bg-blue-50/50 rounded-lg text-xs font-bold text-blue-700">
                                </div>
                            </div>
                            <textarea name="objective" rows="2" placeholder="Physical Examination details..." class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-blue-100 outline-none">{{ old('objective') }}</textarea>
                        </div>

                        {{-- A - Assessment --}}
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded font-bold text-xs">A</span>
                                <label class="text-xs font-bold text-gray-700 uppercase">Assessment / Diagnosis</label>
                            </div>
                            <textarea name="diagnosis" rows="2" required placeholder="Medical assessment..." class="w-full px-4 py-3 rounded-xl border-2 border-blue-50 bg-blue-50/10 text-sm outline-none">{{ old('diagnosis') }}</textarea>
                        </div>

                        {{-- P - Plan / Medicines --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded font-bold text-xs">P</span>
                                    <label class="text-xs font-bold text-gray-700 uppercase">Plan / Medicines</label>
                                </div>
                                <button type="button" id="add-medicine-btn" class="text-blue-600 hover:text-blue-800 text-[10px] font-bold tracking-widest">+ ADD ITEM</button>
                            </div>
                            <div id="medicine-rows-container" class="space-y-3"></div>
                        </div>

                        <div class="pt-6 flex gap-4">
                            <button type="submit" class="flex-grow bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 shadow-lg transition active:scale-[0.98]">Save Patient Record</button>
                            <a href="{{ route('record.index') }}" class="px-8 py-4 bg-gray-100 text-gray-500 rounded-xl font-bold hover:bg-gray-200 transition">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    function calculateAge() {
        const bday = document.getElementById('birthday').value;
        const display = document.getElementById('age_display');
        const hidden = document.getElementById('age_hidden');
        if (!bday) return;

        const birthDate = new Date(bday);
        const today = new Date();
        let years = today.getFullYear() - birthDate.getFullYear();
        let months = today.getMonth() - birthDate.getMonth();
        
        if (months < 0 || (months === 0 && today.getDate() < birthDate.getDate())) { 
            years--; 
            months += 12; 
        }
        
        const ageString = (years <= 0) ? `${months} Mon` : `${years} yrs`;
        display.value = ageString;
        hidden.value = ageString;
    }

    function calculateBMI() {
        const w = parseFloat(document.getElementById('weight').value);
        const h = parseFloat(document.getElementById('height').value);
        const display = document.getElementById('bmi_result');
        if (w > 0 && h > 0) {
            const m = h / 100;
            const bmi = w / (m * m);
            display.value = bmi.toFixed(1);
        } else { 
            display.value = ""; 
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Trigger initial calculations if values exist (e.g., after validation error)
        if(document.getElementById('birthday').value) calculateAge();
        if(document.getElementById('weight').value && document.getElementById('height').value) calculateBMI();

        const container = document.getElementById('medicine-rows-container');
        const addBtn = document.getElementById('add-medicine-btn');
        let rowIndex = 0;
        const allMedicines = JSON.parse(document.getElementById('medicine-data').dataset.list || '[]');

        function createMedicineRow() {
            const div = document.createElement('div');
            div.className = "grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_120px_auto] gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100 medicine-row";
            
            let options = '<option value="" disabled selected>Select Medicine</option>';
            allMedicines.forEach(med => { 
                options += `<option value="${med.id}">${med.name} (Stock: ${med.stock})</option>`; 
            });

            div.innerHTML = `
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Medicine</label>
                    <select name="medicines[${rowIndex}][id]" class="med-select" required>${options}</select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Quantity</label>
                    <input type="number" name="medicines[${rowIndex}][quantity]" required min="1" value="1" placeholder="Qty" 
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg h-[42px] text-sm outline-none">
                </div>
                <button type="button" class="mb-1 self-end justify-self-end text-gray-300 hover:text-red-500 remove-row" title="Remove medicine row">✕</button>
            `;

            container.appendChild(div);
            $(div).find('.med-select').select2({ width: '100%' });
            rowIndex++;
        }

        $(document).on('click', '.remove-row', function() {
            $(this).closest('.medicine-row').remove();
        });

        addBtn.addEventListener('click', createMedicineRow);
    });
</script>
@endsection