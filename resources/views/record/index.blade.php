@extends('layouts.app')

@section('content')
@php
    $role = auth()->user()->role ?? 'bhw';
    $isNurse = $role === 'nurse';
    $isDoctorRole = $role === 'doctor';
    $isBhwRole = $role === 'bhw';
    $canEncodeFindings = $isNurse;
@endphp
{{-- 1. ADD SELECT2 DEPENDENCIES --}}
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

{{-- Hidden data container --}}
<div id="medicine-data" data-medicines="{{ json_encode($allMedicines ?? []) }}"></div>

<div class="max-w-7xl mx-auto pb-20 px-4 sm:px-6 lg:px-8"> 

    {{-- ERROR ALERT SECTION --}}
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

    {{-- Header Section --}}
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

            <select id="genderFilter" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium bg-white focus:ring-2 focus:ring-blue-500 outline-none shadow-sm cursor-pointer">
                <option value="all">All Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>

            <select id="addressFilter" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium bg-white focus:ring-2 focus:ring-blue-500 outline-none shadow-sm cursor-pointer min-w-[180px]">
                <option value="all">All Address</option>
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

    {{-- Table Design --}}
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
                    data-age-months="{{ $ageMonths }}"
                    data-gender="{{ strtolower($record->gender) }}"
                    data-address="{{ strtolower(trim($record->address_purok)) }}">
                    
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
                            @if(!$isNurse)
                            <button type="button" 
                                    data-record='{!! json_encode($record) !!}'
                                    onclick="handleOpenModal(this)"
                                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            @endif

                            <a href="{{ route('record.show', $record->id) }}" 
                               class="flex items-center justify-center w-9 h-9 rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-800 hover:text-white transition-all shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>

                            <a href="{{ route('record.edit', $record->id) }}"
                               class="flex items-center justify-center w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm"
                               title="{{ $isNurse ? 'Add vitals and triage' : 'Edit Entry' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M17.414 2.586a2 2 0 010 2.828l-8.5 8.5a1 1 0 01-.39.242l-3 1a1 1 0 01-1.265-1.265l1-3a1 1 0 01.242-.39l8.5-8.5a2 2 0 012.828 0z"/>
                                    <path d="M5 16a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z"/>
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

@if(!$isNurse && !$isDoctorRole && !$isBhwRole)
<a href="{{ route('record.create') }}"
   class="fixed bottom-8 right-8 z-40 inline-flex items-center gap-2 rounded-full bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-blue-700">
    <span class="text-base leading-none">+</span>
    Add New Consultation
</a>
@endif

{{-- MODAL SECTION --}}
<div id="quickAddModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeQuickAdd()"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl sm:max-w-xl w-full overflow-hidden border border-gray-100">
            <form action="{{ route('record.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                {{-- Patient Identity Hidden Inputs --}}
                <input type="hidden" name="first_name" id="modal_first_name">
                <input type="hidden" name="middle_name" id="modal_middle_name">
                <input type="hidden" name="last_name" id="modal_last_name">
                <input type="hidden" name="birthday" id="modal_birthday">
                <input type="hidden" name="gender" id="modal_gender">
                <input type="hidden" name="civil_status" id="modal_civil_status">
                <input type="hidden" name="address_purok" id="modal_address">
                <input type="hidden" name="age" id="modal_age">
                <input type="hidden" name="contact_number" id="modal_contact">
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
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2 ml-1">Subjective Findings</label>
                            <textarea
                                name="subjective"
                                rows="2"
                                {{ $canEncodeFindings ? '' : 'readonly onclick=showNurseOnlyNotice(\'Subjective Findings\')' }}
                                class="w-full p-4 border rounded-2xl outline-none transition text-sm {{ $canEncodeFindings ? 'bg-gray-50 border-gray-100 focus:ring-2 focus:ring-blue-100 focus:bg-white' : 'bg-gray-50 border-gray-100 text-gray-500 cursor-not-allowed' }}"
                                placeholder="{{ $canEncodeFindings ? 'Patient\'s complaints...' : 'Only nurse can fill this out.' }}"
                            ></textarea>
                        </div>

                        {{-- VITALS GRID --}}
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-3 ml-1">Vital Signs</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <input type="text" name="temp" placeholder="Temp °C" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:ring-2 focus:ring-blue-100 text-sm">
                                </div>
                                <div>
                                    <input type="text" name="bp" placeholder="BP (120/80)" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:ring-2 focus:ring-blue-100 text-sm">
                                </div>
                                <div>
                                    <input type="text" name="weight" id="quick_weight" placeholder="Weight (kg)" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:ring-2 focus:ring-blue-100 text-sm">
                                </div>
                                <div>
                                    <input type="text" name="height" id="quick_height" placeholder="Height (cm)" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:ring-2 focus:ring-blue-100 text-sm">
                                </div>
                                <div>
                                    <input type="text" name="pr" placeholder="PR (bpm)" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:ring-2 focus:ring-blue-100 text-sm">
                                </div>
                                <div>
                                    <input type="text" name="rr" placeholder="RR (cpm)" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:ring-2 focus:ring-blue-100 text-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <input type="text" id="quick_bmi_display" placeholder="BMI (Auto)" readonly class="w-full p-3 bg-blue-50 border border-blue-100 rounded-xl outline-none text-sm font-semibold text-blue-700">
                                </div>
                            </div>
                        </div>

                        {{-- PHYSICAL EXAM (OBJECTIVE) --}}
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2 ml-1">Physical Exam (Objective)</label>
                            <textarea
                                name="objective"
                                rows="2"
                                {{ $canEncodeFindings ? '' : 'readonly onclick=showNurseOnlyNotice(\'Objective Findings\')' }}
                                class="w-full p-4 border border-gray-100 rounded-2xl outline-none transition text-sm {{ $canEncodeFindings ? 'bg-gray-50 focus:ring-2 focus:ring-blue-100 focus:bg-white' : 'bg-gray-50 text-gray-500 cursor-not-allowed' }}"
                                placeholder="{{ $canEncodeFindings ? 'Findings from physical examination...' : 'Only nurse can fill this out.' }}"
                            ></textarea>
                        </div>

                        {{-- BHW: diagnosis is reserved for doctor --}}
                        <input type="hidden" name="diagnosis" value="waiting_for_doctor/nurse">

                        <div class="rounded-xl border border-dashed border-amber-200 bg-amber-50 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-amber-700">
                                Laboratory uploads and medicine dispensing are for doctor/nurse only.
                            </p>
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

const recordRows = Array.from(document.querySelectorAll('#recordsTableBody .patient-row')).map((row) => {
    const patientNameElement = row.querySelector('.patient-name');
    return {
        html: row.outerHTML,
        name: patientNameElement ? patientNameElement.innerText.toLowerCase() : '',
        years: parseInt(row.getAttribute('data-age-years') || '0', 10),
        months: parseInt(row.getAttribute('data-age-months') || '0', 10),
        gender: (row.getAttribute('data-gender') || '').toLowerCase(),
        address: (row.getAttribute('data-address') || '').toLowerCase(),
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

// Search & Filter Logic
function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const ageFilter = document.getElementById('ageFilter').value;
    const genderFilter = document.getElementById('genderFilter').value;
    const addressFilter = document.getElementById('addressFilter').value;
    const filtered = recordRows.filter((row) => {
        const matchesSearch = row.name.includes(searchTerm);
        let matchesAge = false;
        const matchesGender = genderFilter === 'all' ? true : row.gender === genderFilter;
        const matchesAddress = addressFilter === 'all' ? true : row.address === addressFilter;

        if (ageFilter === 'all') matchesAge = true;
        else if (ageFilter === '0-11' && row.years === 0 && row.months <= 11) matchesAge = true;
        else if (ageFilter === '12-59' && (row.years >= 1 && row.years < 5)) matchesAge = true;
        else if (ageFilter === 'senior' && row.years >= 60) matchesAge = true;

        return matchesSearch && matchesAge && matchesGender && matchesAddress;
    });

    renderRecordPagination(filtered);
}

document.getElementById('searchInput').addEventListener('keyup', applyFilters);
document.getElementById('ageFilter').addEventListener('change', applyFilters);
document.getElementById('genderFilter').addEventListener('change', applyFilters);
document.getElementById('addressFilter').addEventListener('change', applyFilters);
document.addEventListener('DOMContentLoaded', function () {
    const addressFilter = document.getElementById('addressFilter');
    const uniqueAddresses = [...new Set(recordRows.map(item => item.address).filter(Boolean))].sort();
    uniqueAddresses.forEach((address) => {
        const opt = document.createElement('option');
        opt.value = address;
        opt.textContent = address.toUpperCase();
        addressFilter.appendChild(opt);
    });
    renderRecordPagination(recordRows);
});

function handleOpenModal(button) {
    const record = JSON.parse(button.getAttribute('data-record'));
    document.getElementById('modal_first_name').value = record.first_name;
    document.getElementById('modal_middle_name').value = record.middle_name || '';
    document.getElementById('modal_last_name').value = record.last_name;
    document.getElementById('modal_birthday').value = record.birthday;
    document.getElementById('modal_gender').value = record.gender;
    document.getElementById('modal_civil_status').value = record.civil_status || 'Single';
    document.getElementById('modal_address').value = record.address_purok;
    document.getElementById('modal_age').value = record.age || '';
    document.getElementById('modal_contact').value = record.contact_number || '';
    document.getElementById('display_name').innerText = `${record.first_name} ${record.last_name}`;
    
    const dob = new Date(record.birthday).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('display_dob').innerText = `Date of Birth: ${dob}`;
    
    document.getElementById('quickAddModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; 
}

function closeQuickAdd() {
    document.getElementById('quickAddModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
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

// BMI AUTO CALCULATION
document.addEventListener('input', function (e) {
    if (e.target.id === 'quick_weight' || e.target.id === 'quick_height') {
        const weight = parseFloat(document.getElementById('quick_weight').value);
        const heightCm = parseFloat(document.getElementById('quick_height').value);
        
        if (weight > 0 && heightCm > 0) {
            const heightM = heightCm / 100;
            const bmi = (weight / (heightM * heightM)).toFixed(2);
            document.getElementById('modal_bmi').value = bmi;
            document.getElementById('quick_bmi_display').value = bmi;
        } else {
            document.getElementById('modal_bmi').value = 'N/A';
            document.getElementById('quick_bmi_display').value = '';
        }
    }
});
</script>
@endsection