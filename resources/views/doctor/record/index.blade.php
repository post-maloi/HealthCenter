@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        display: flex;
        align-items: center;
    }
    .select2-container--open { z-index: 9999 !important; }
    input[type=number]::-webkit-inner-spin-button { opacity: 1; }
</style>

<div id="medicine-data" data-medicines="{{ json_encode($allMedicines ?? []) }}"></div>

<div class="max-w-7xl mx-auto pb-20 px-4 sm:px-6 lg:px-8"> 
    @if ($errors->any())
        <div class="mt-8 bg-red-50 border-l-4 border-red-400 p-4 rounded-xl shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-bold">Failed to save record:</p>
                    <ul class="text-xs text-red-600 list-disc ml-5 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 mt-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Clinic Records</h1>
            <p class="text-gray-500 text-sm mt-1">Showing unique patient history</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <select id="ageFilter" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium bg-white focus:ring-2 focus:ring-blue-500 outline-none shadow-sm cursor-pointer">
                <option value="all">All Ages</option>
                <option value="0-11">Infants (0-11 months)</option>
                <option value="12-59">Children (12-59 months)</option>
                <option value="senior">Seniors (60+ years)</option>
            </select>

            <div class="relative flex-grow md:flex-grow-0">
                <input type="text" id="searchInput" placeholder="Search patients..." 
                    class="pl-10 pr-4 py-2.5 w-full md:w-64 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                <svg class="w-5 h-5 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Date</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Patient Name</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Age / Gender</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Address</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Latest Vitals</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody id="recordsTableBody" class="divide-y divide-gray-50">
                @forelse($records as $record)
                @php
                    $birthDate = \Carbon\Carbon::parse($record->birthday);
                    $ageYears = (int)$birthDate->diffInYears(now()); 
                    $ageMonths = (int)$birthDate->diffInMonths(now());
                @endphp
                <tr class="hover:bg-blue-50/30 transition patient-row" 
                    data-age-years="{{ $ageYears }}" 
                    data-age-months="{{ $ageMonths }}">
                    
                    <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                        {{ \Carbon\Carbon::parse($record->consultation_date)->format('M d, Y') }}
                    </td>
                    
                    <td class="px-6 py-4 text-sm">
                        <div class="font-bold text-gray-800 capitalize patient-name">{{ $record->first_name }} {{ $record->last_name }}</div>
                        <div class="text-[10px] font-bold text-blue-500 uppercase tracking-tight">
                            DOB: {{ $birthDate->format('M d, Y') }}
                        </div>
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-500">
                        <span class="font-bold text-gray-700">
                            @if($ageMonths < 12)
                                {{ $ageMonths }} mon
                            @else
                                {{ $ageYears }} yrs
                            @endif
                        </span> <span class="text-gray-300 mx-1">|</span> {{ $record->gender }}
                    </td>
                    
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $record->address_purok }}</td>
                    <td class="px-6 py-4 text-xs text-gray-600">
                        T: {{ $record->display_temp ?: '--' }} | BP: {{ $record->display_bp ?: '--' }} | WT: {{ $record->display_weight ?: '--' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 italic">
                        @if(in_array(trim((string) $record->diagnosis), ['For doctor assessment', 'waiting_for_doctor/nurse'], true))
                            <span class="px-2 py-1 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold uppercase tracking-wide">pending</span>
                        @else
                            <span class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase tracking-wide">completed</span>
                        @endif
                    </td>
                    
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('doctor.record.create', ['patient_record_id' => $record->id]) }}"
                                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm"
                                    title="Add new consultation for this patient">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                            </a>

                            <a href="{{ route('doctor.record.show', $record->id) }}" 
                               class="flex items-center justify-center w-9 h-9 rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-800 hover:text-white transition-all shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-16 text-center text-gray-400 italic">No patient records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="recordsPagination" class="mt-4"></div>
</div>

<div id="quickAddModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeQuickAdd()"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl sm:max-w-xl w-full overflow-hidden border border-gray-100">
            <form action="{{ route('doctor.record.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="patient_record_id" id="modal_patient_record_id">
                <input type="hidden" name="consultation_date" value="{{ now()->format('Y-m-d') }}">

                <input type="hidden" name="bmi" id="modal_bmi" value="N/A">

                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Quick Consultation</h3>
                        <button type="button" onclick="closeQuickAdd()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    
                    <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-4 mb-6">
                        <p class="text-[10px] text-blue-500 font-bold uppercase tracking-widest mb-1">Active Patient</p>
                        <p id="display_name" class="font-bold text-lg text-gray-800"></p>
                        <p id="display_dob" class="text-xs text-gray-500 font-medium"></p>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-3 ml-1">Vital Signs</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div><input type="text" name="temp" placeholder="Temp °C" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:ring-2 focus:ring-blue-100 text-sm"></div>
                                <div><input type="text" name="bp" placeholder="BP (120/80)" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:ring-2 focus:ring-blue-100 text-sm"></div>
                                <div><input type="text" name="weight" id="quick_weight" placeholder="Weight (kg)" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:ring-2 focus:ring-blue-100 text-sm"></div>
                                <div><input type="text" name="height" id="quick_height" placeholder="Height (cm)" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:ring-2 focus:ring-blue-100 text-sm"></div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2 ml-1">Subjective</label>
                            <textarea name="subjective" rows="2" class="w-full p-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-2 focus:ring-blue-100 focus:bg-white transition text-sm" placeholder="Patient's complaints..."></textarea>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2 ml-1">Physical Exam (Objective)</label>
                            <textarea name="objective" rows="2" class="w-full p-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-2 focus:ring-blue-100 focus:bg-white transition text-sm" placeholder="Findings from physical examination..."></textarea>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2 ml-1">Diagnosis / Assessment</label>
                            <textarea name="diagnosis" rows="2" required class="w-full p-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-2 focus:ring-blue-100 focus:bg-white transition text-sm" placeholder="What is the diagnosis?"></textarea>
                        </div>

                        <div x-data="labUploader()" class="pt-1">
                            <div class="flex items-center justify-between">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2 ml-1">Laboratory Upload (Optional)</label>
                                <button type="button" @click="clearAll()" x-show="files.length > 0"
                                    class="text-[10px] font-black uppercase tracking-widest text-red-500 hover:text-red-700 transition"
                                    style="display:none;">
                                    Clear
                                </button>
                            </div>

                            <input x-ref="input" type="file" name="laboratory_images[]" multiple accept=".jpg,.jpeg,.png,.webp" class="hidden" @change="onPick($event)">

                            <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-white p-5 text-center cursor-pointer hover:border-blue-300 hover:bg-blue-50/30 transition"
                                @click="$refs.input.click()"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="onDrop($event)"
                                :class="isDragging ? 'border-blue-400 bg-blue-50/40' : ''">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-9 h-9 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-600">Drag files to upload</p>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest">or</p>
                                    <button type="button" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-blue-600 font-black text-[10px] uppercase tracking-widest hover:bg-gray-50 transition">Browse Files</button>
                                    <p class="text-[10px] text-gray-400 mt-1">Max files: <span class="font-bold">5</span> • Max size: <span class="font-bold">5MB</span> each</p>
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
                                                @click="removeAt(idx)" title="Remove">✕</button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Medicines Given</label>
                                <button type="button" onclick="createMedicineRow()" class="text-blue-600 text-[10px] font-bold hover:text-blue-800 transition uppercase tracking-widest">+ Add Item</button>
                            </div>
                            <p class="mb-2 ml-1 text-[10px] text-amber-600 font-semibold uppercase tracking-wide">Dispensing uses earliest expiry first (FEFO).</p>
                            <div id="medicine-rows-container" class="space-y-3"></div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" onclick="closeQuickAdd()" class="px-6 py-3 text-sm font-bold text-gray-400 hover:text-gray-600 transition">Discard</button>
                    <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-md transition active:scale-95">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function labUploader() {
    const MAX_FILES = 5;
    const MAX_BYTES = 5 * 1024 * 1024;
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
            if (!ALLOWED.includes(file.type)) return 'Only JPG, PNG, or WEBP images are allowed.';
            if (file.size > MAX_BYTES) return 'A file exceeds the 5MB limit.';
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
                this.files.push({ key, file, name: file.name, size: file.size, preview });
            }

            this.syncToInput();
        },

        syncToInput() {
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f.file));
            this.$refs.input.files = dt.files;
        },

        onPick(e) { this.addFiles(e.target.files); },
        onDrop(e) { this.isDragging = false; this.addFiles(e.dataTransfer.files); },
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

const recordRows = Array.from(document.querySelectorAll('#recordsTableBody .patient-row')).map((row) => {
    const patientNameElement = row.querySelector('.patient-name');
    return {
        html: row.outerHTML,
        name: patientNameElement ? patientNameElement.innerText.toLowerCase() : '',
        years: parseInt(row.getAttribute('data-age-years') || '0', 10),
        months: parseInt(row.getAttribute('data-age-months') || '0', 10),
    };
});

function renderRecordPagination(filteredRows) {
    if (filteredRows.length === 0) {
        renderPaginationTable({
            pagerSelector: '#recordsPagination',
            tableBodySelector: '#recordsTableBody',
            rows: [],
            emptyRowHtml: '<tr><td colspan="7" class="px-6 py-16 text-center text-gray-400 italic">No patient records found.</td></tr>'
        });
        return;
    }

    renderPaginationTable({
        pagerSelector: '#recordsPagination',
        tableBodySelector: '#recordsTableBody',
        rows: filteredRows.map(item => item.html),
        emptyRowHtml: '<tr><td colspan="7" class="px-6 py-16 text-center text-gray-400 italic">No patient records found.</td></tr>'
    });
}

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filter = document.getElementById('ageFilter').value;
    const filtered = recordRows.filter((row) => {
        const matchesSearch = row.name.includes(searchTerm);
        let matchesAge = false;

        if (filter === 'all') matchesAge = true;
        else if (filter === '0-11' && row.years === 0 && row.months <= 11) matchesAge = true;
        else if (filter === '12-59' && (row.years >= 1 && row.years < 5)) matchesAge = true;
        else if (filter === 'senior' && row.years >= 60) matchesAge = true;

        return matchesSearch && matchesAge;
    });

    renderRecordPagination(filtered);
}

document.getElementById('searchInput').addEventListener('keyup', applyFilters);
document.getElementById('ageFilter').addEventListener('change', applyFilters);
document.addEventListener('DOMContentLoaded', function () {
    renderRecordPagination(recordRows);
});

const medicineDataElement = document.getElementById('medicine-data');
const allMedicines = medicineDataElement ? JSON.parse(medicineDataElement.dataset.medicines) : [];
const container = document.getElementById('medicine-rows-container');
let rowIndex = 0;

function createMedicineRow() {
    const div = document.createElement('div');
    const selectId = `med-select-${rowIndex}`;
    div.className = "grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_120px_auto] gap-3 p-3 bg-gray-50/50 rounded-xl border border-gray-100 animate-in fade-in slide-in-from-top-1";
    
    let options = '<option value="">Search medicine...</option>';
    allMedicines.forEach(med => {
        options += `<option value="${med.id}">${med.name} (Stock: ${med.stock})</option>`;
    });

    div.innerHTML = `
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Medicine</label>
            <select id="${selectId}" name="medicines[${rowIndex}][id]" required class="w-full">${options}</select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Quantity</label>
            <input type="number" name="medicines[${rowIndex}][quantity]" placeholder="Qty" required min="1" value="1"
                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-semibold h-[42px] outline-none focus:border-blue-400">
        </div>
        <button type="button" class="text-gray-300 hover:text-red-500 self-end mb-1 transition justify-self-end" title="Remove medicine row" onclick="this.parentElement.remove()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    `;
    container.appendChild(div);

    $(`#${selectId}`).select2({
        dropdownParent: $('#quickAddModal'),
        width: '100%'
    });
    rowIndex++;
}

function handleOpenModal(button) {
    const record = JSON.parse(button.getAttribute('data-record'));
    document.getElementById('modal_patient_record_id').value = record.id;
    document.getElementById('display_name').innerText = `${record.first_name} ${record.last_name}`;
    
    const dob = new Date(record.birthday).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('display_dob').innerText = `Date of Birth: ${dob}`;
    
    container.innerHTML = '';
    createMedicineRow();
    
    document.getElementById('quickAddModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; 
}

function closeQuickAdd() {
    document.getElementById('quickAddModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function showDoctorFloatingHint() {
    const existing = document.getElementById('doctor-floating-hint');
    if (existing) existing.remove();

    const hint = document.createElement('div');
    hint.id = 'doctor-floating-hint';
    hint.className = 'fixed bottom-24 right-8 z-50 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 shadow-lg';
    hint.textContent = 'Select a patient row and click the + icon to add consultation.';
    document.body.appendChild(hint);

    setTimeout(() => {
        hint.style.opacity = '0';
        hint.style.transition = 'opacity 0.3s ease';
        setTimeout(() => hint.remove(), 300);
    }, 2200);
}

document.addEventListener('input', function (e) {
    if (e.target.id === 'quick_weight' || e.target.id === 'quick_height') {
        const weight = parseFloat(document.getElementById('quick_weight').value);
        const heightCm = parseFloat(document.getElementById('quick_height').value);
        
        if (weight > 0 && heightCm > 0) {
            const heightM = heightCm / 100;
            const bmi = (weight / (heightM * heightM)).toFixed(2);
            document.getElementById('modal_bmi').value = bmi;
        }
    }
});
</script>
@endsection

