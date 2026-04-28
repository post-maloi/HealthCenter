@extends('layouts.app')

@section('content')
@php
    $role = auth()->user()->role ?? 'bhw';
    $isNurse = $role === 'nurse';
    $canEncodeFindings = $isNurse;
@endphp
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
        <form action="{{ route('record.store') }}" method="POST" enctype="multipart/form-data">
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
                                <label class="block text-xs font-bold text-gray-500 mb-1">Cellphone Number</label>
                                <input type="text" name="contact_number" value="{{ old('contact_number') }}" placeholder="09XXXXXXXXX"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-blue-400 outline-none">
                            </div>

                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Address / Purok</label>
                                <select id="address_purok_select" name="address_purok" required
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-blue-400 outline-none uppercase">
                                    <option value="" disabled {{ old('address_purok') ? '' : 'selected' }}>Select address</option>
                                    @foreach (['BAYANIHAN', 'TABUNON', 'GABI', 'BALAANONG TUBIG', 'BALAHAN', 'RIBOMA', 'PAG-ASA', 'PUROK-ANO'] as $address)
                                        <option value="{{ $address }}" {{ old('address_purok') === $address ? 'selected' : '' }}>{{ $address }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Laboratory Upload (Optional) --}}
                        <div class="pt-2 relative opacity-60" x-data="labUploader()">
                            <div class="absolute inset-0 z-10 cursor-not-allowed" onclick="showDoctorOnlyNotice('Laboratory Upload')"></div>
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Laboratory Upload (Optional)</label>
                                <button type="button" @click="clearAll()" x-show="files.length > 0"
                                    class="text-[10px] font-black uppercase tracking-widest text-red-500 hover:text-red-700 transition"
                                    style="display:none;">
                                    Clear
                                </button>
                            </div>

                            <input
                                x-ref="input"
                                type="file"
                                name="laboratory_images[]"
                                multiple
                                accept=".jpg,.jpeg,.png,.webp"
                                class="hidden"
                                @change="onPick($event)"
                            >

                            <div
                                class="rounded-2xl border-2 border-dashed border-gray-200 bg-white p-6 text-center cursor-pointer hover:border-blue-300 hover:bg-blue-50/30 transition"
                                @click="$refs.input.click()"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="onDrop($event)"
                                :class="isDragging ? 'border-blue-400 bg-blue-50/40' : ''"
                            >
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-600">Drag files to upload</p>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest">or</p>
                                    <button type="button"
                                        class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-blue-600 font-black text-[10px] uppercase tracking-widest hover:bg-gray-50 transition">
                                        Browse Files
                                    </button>
                                    <p class="text-[10px] text-gray-400 mt-1">
                                        Max files: <span class="font-bold">5</span> • Max size: <span class="font-bold">5MB</span> each
                                    </p>
                                    <p class="text-[9px] text-gray-300 uppercase tracking-widest">JPG, PNG, WEBP only</p>
                                </div>
                            </div>

                            <template x-if="errors.length > 0">
                                <div class="mt-3 p-3 bg-red-50 border border-red-100 rounded-xl">
                                    <template x-for="(msg, idx) in errors" :key="idx">
                                        <p class="text-[10px] font-bold text-red-600" x-text="msg"></p>
                                    </template>
                                </div>
                            </template>

                            <template x-if="files.length > 0">
                                <div class="mt-4 space-y-2">
                                    <template x-for="(f, idx) in files" :key="f.key">
                                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-2xl border border-gray-100">
                                            <img :src="f.preview" class="w-12 h-12 rounded-xl object-cover border border-gray-100" alt="Preview" />
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-black text-gray-700 truncate" x-text="f.name"></p>
                                                <p class="text-[10px] text-gray-400" x-text="formatBytes(f.size)"></p>
                                            </div>
                                            <button type="button" class="w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-200 transition"
                                                @click="removeAt(idx)" title="Remove">
                                                ✕
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
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
                            <textarea
                                name="subjective"
                                rows="2"
                                placeholder="{{ $canEncodeFindings ? "Patient's complaints..." : 'Only nurse can fill this out.' }}"
                                {{ $canEncodeFindings ? '' : 'readonly onclick=showNurseOnlyNotice(\'Subjective Findings\')' }}
                                class="w-full px-4 py-3 rounded-xl border text-sm outline-none {{ $canEncodeFindings ? 'border-gray-200 focus:ring-2 focus:ring-blue-100' : 'border-gray-100 bg-gray-50 text-gray-500 cursor-not-allowed' }}"
                            >{{ old('subjective') }}</textarea>
                        </div>

                        {{-- V - Vital Signs --}}
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded font-bold text-xs">V</span>
                                <label class="text-xs font-bold text-gray-700 uppercase">Vitals</label>
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
                        </div>

                        {{-- O - Objective Findings --}}
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded font-bold text-xs">O</span>
                                <label class="text-xs font-bold text-gray-700 uppercase">Objective Findings</label>
                            </div>
                            <textarea
                                name="objective"
                                rows="2"
                                placeholder="{{ $canEncodeFindings ? 'Physical examination details...' : 'Only nurse can fill this out.' }}"
                                {{ $canEncodeFindings ? '' : 'readonly onclick=showNurseOnlyNotice(\'Objective Findings\')' }}
                                class="w-full px-4 py-3 rounded-xl border text-sm outline-none {{ $canEncodeFindings ? 'border-gray-200 focus:ring-2 focus:ring-blue-100' : 'border-gray-100 bg-gray-50 text-gray-500 cursor-not-allowed' }}"
                            >{{ old('objective') }}</textarea>
                        </div>

                        {{-- A - Assessment --}}
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded font-bold text-xs">A</span>
                                <label class="text-xs font-bold text-gray-700 uppercase">Assessment / Diagnosis</label>
                            </div>
                            <textarea rows="2" readonly onclick="showDoctorOnlyNotice('Assessment / Diagnosis')" placeholder="Only doctor can fill this out"
                                class="w-full px-4 py-3 rounded-xl border-2 border-blue-50 bg-gray-50 text-sm text-gray-500 outline-none cursor-not-allowed">Only doctor can fill this out.</textarea>
                        </div>

                        {{-- P - Plan / Medicines --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded font-bold text-xs">P</span>
                                    <label class="text-xs font-bold text-gray-700 uppercase">Plan / Medicines</label>
                                </div>
                                <button type="button" id="add-medicine-btn" onclick="showDoctorOnlyNotice('Plan / Medicines')"
                                    class="text-gray-400 text-[10px] font-bold tracking-widest cursor-not-allowed">+ ADD ITEM</button>
                            </div>
                            <div id="medicine-rows-container" class="space-y-3">
                                <div class="p-3 rounded-xl border border-dashed border-gray-200 bg-gray-50 text-xs font-semibold text-gray-500">
                                    Only doctor can fill Plan / Medicines.
                                </div>
                            </div>
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
    function labUploader() {
        const MAX_FILES = 5;
        const MAX_BYTES = 5 * 1024 * 1024; // 5MB
        const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];

        function fileKey(file) {
            return `${file.name}-${file.size}-${file.lastModified}`;
        }

        return {
            isDragging: false,
            files: [],
            errors: [],

            formatBytes(bytes) {
                if (!bytes && bytes !== 0) return '';
                const units = ['B', 'KB', 'MB', 'GB'];
                let i = 0;
                let value = bytes;
                while (value >= 1024 && i < units.length - 1) {
                    value /= 1024;
                    i++;
                }
                return `${value.toFixed(i === 0 ? 0 : 1)} ${units[i]}`;
            },

            validateFile(file) {
                if (!ALLOWED.includes(file.type)) {
                    return 'Only JPG, PNG, or WEBP images are allowed.';
                }
                if (file.size > MAX_BYTES) {
                    return 'A file exceeds the 5MB limit.';
                }
                return null;
            },

            addFiles(fileList) {
                this.errors = [];
                const incoming = Array.from(fileList || []);
                if (incoming.length === 0) return;

                for (const file of incoming) {
                    if (this.files.length >= MAX_FILES) {
                        this.errors.push(`Only up to ${MAX_FILES} files are allowed.`);
                        break;
                    }

                    const key = fileKey(file);
                    if (this.files.some(x => x.key === key)) continue;

                    const err = this.validateFile(file);
                    if (err) {
                        this.errors.push(`${file.name}: ${err}`);
                        continue;
                    }

                    const preview = URL.createObjectURL(file);
                    this.files.push({
                        key,
                        file,
                        name: file.name,
                        size: file.size,
                        preview,
                    });
                }

                this.syncToInput();
            },

            syncToInput() {
                const dt = new DataTransfer();
                this.files.forEach(f => dt.items.add(f.file));
                this.$refs.input.files = dt.files;
            },

            onPick(e) {
                this.addFiles(e.target.files);
            },

            onDrop(e) {
                this.isDragging = false;
                this.addFiles(e.dataTransfer.files);
            },

            removeAt(idx) {
                const removed = this.files.splice(idx, 1);
                if (removed?.[0]?.preview) URL.revokeObjectURL(removed[0].preview);
                this.syncToInput();
            },

            clearAll() {
                this.files.forEach(f => f.preview && URL.revokeObjectURL(f.preview));
                this.files = [];
                this.errors = [];
                this.syncToInput();
            },
        };
    }

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

    function showDoctorOnlyNotice(sectionName) {
        const existing = document.getElementById('doctor-only-alert');
        if (existing) existing.remove();

        const alert = document.createElement('div');
        alert.id = 'doctor-only-alert';
        alert.className = 'fixed top-5 right-5 z-[9999] bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl shadow-lg text-sm font-semibold';
        alert.textContent = `${sectionName} can only be filled out by a Doctor.`;
        document.body.appendChild(alert);

        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 2200);
    }

    function showNurseOnlyNotice(sectionName) {
        const existing = document.getElementById('nurse-only-alert');
        if (existing) existing.remove();

        const alert = document.createElement('div');
        alert.id = 'nurse-only-alert';
        alert.className = 'fixed top-5 right-5 z-[9999] bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl shadow-lg text-sm font-semibold';
        alert.textContent = `${sectionName} can only be filled out by a Nurse.`;
        document.body.appendChild(alert);

        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 2200);
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Trigger initial calculations if values exist (e.g., after validation error)
        if(document.getElementById('birthday').value) calculateAge();
        if(document.getElementById('weight').value && document.getElementById('height').value) calculateBMI();

        const container = document.getElementById('medicine-rows-container');
        const addBtn = document.getElementById('add-medicine-btn');
        const allMedicines = JSON.parse(document.getElementById('medicine-data').dataset.list || '[]');

        $('#address_purok_select').select2({
            width: '100%',
            placeholder: 'Select address',
            allowClear: false
        });

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
        }

        $(document).on('click', '.remove-row', function() {
            $(this).closest('.medicine-row').remove();
        });

        // Keep function available but BHW button is intentionally locked.
        if (addBtn) {
            addBtn.addEventListener('click', function (e) {
                e.preventDefault();
            });
        }
    });
</script>
@endsection