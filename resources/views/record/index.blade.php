@extends('layouts.app')

@section('content')
{{-- Hidden data container for JavaScript --}}
<div id="medicine-data" data-medicines="{{ json_encode($allMedicines ?? []) }}"></div>

<div class="max-w-7xl mx-auto pb-20"> 
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Clinic Records</h1>
            <p class="text-gray-500 text-sm mt-1">Showing unique patient history</p>
        </div>

        <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
            {{-- AGE FILTER DROPDOWN --}}
            <div class="relative w-full md:w-auto">
                <select id="ageFilter" class="w-full md:w-auto appearance-none bg-white border border-gray-200 text-slate-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm font-medium shadow-sm">
                    <option value="all">All Ages</option>
                    <option value="0-11">0-11 Months</option>
                    <option value="12-59">12-59 Months</option>
                    <option value="senior">Senior Citizen</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                </div>
            </div>

            {{-- SEARCH BAR: Ensure this name="search" matches your Controller --}}
            <form action="{{ route('record.index') }}" method="GET" class="flex items-center gap-2 w-full md:w-auto">
                <div class="relative flex-1 md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name..." 
                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition shadow-sm text-sm">
                    <div class="absolute left-3 top-2.5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                
                @if(request('search'))
                    <a href="{{ route('record.index') }}" class="text-sm text-gray-500 hover:text-red-500 underline ml-2">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Latest Date</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Patient Name</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Age/Gender</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Address</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Diagnosis</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="recordsTableBody" class="divide-y divide-gray-100">
                @forelse($records as $record)
                <tr class="hover:bg-blue-50/50 transition patient-row" 
                    data-age-years="{{ \Carbon\Carbon::parse($record->birthday)->diffInYears(now()) }}" 
                    data-age-months="{{ \Carbon\Carbon::parse($record->birthday)->diffInMonths(now()) }}">
                    
                    <td class="px-6 py-4 text-sm text-slate-600">
                        {{ \Carbon\Carbon::parse($record->consultation_date)->format('M d, Y') }}
                    </td>
                    
                    <td class="px-6 py-4 text-sm">
                        <div class="font-bold text-slate-800 capitalize">{{ $record->first_name }} {{ $record->last_name }}</div>
                        {{-- DOB added to distinguish between multiple patients with the same name --}}
                        <div class="text-[10px] font-bold text-blue-500 uppercase tracking-tight">
                            DOB: {{ \Carbon\Carbon::parse($record->birthday)->format('M d, Y') }}
                        </div>
                        <div class="text-xs text-slate-400 mt-1">{{ $record->contact_number ?? 'No contact' }}</div>
                    </td>

                    <td class="px-6 py-4 text-sm text-slate-500">
                        {{-- Integer age display to fix decimal issue --}}
                        <span class="font-medium text-slate-700">
                            {{ (int)\Carbon\Carbon::parse($record->birthday)->diffInYears(now()) }} yrs
                        </span> / {{ $record->gender }}
                    </td>
                    
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $record->address_purok }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ Str::limit($record->diagnosis, 30) }}</td>
                    
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-3">
                            {{-- FIXED: Single quotes around data-record to prevent JSON parsing errors --}}
                            <button type="button" 
                                    data-record='{!! json_encode($record) !!}'
                                    onclick="handleOpenModal(this)"
                                    class="flex items-center justify-center w-9 h-9 rounded-full bg-green-600 text-white hover:bg-green-700 shadow-md transition-transform hover:scale-110"
                                    title="Quick Add">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <a href="{{ route('record.show', $record->id) }}" 
                               class="flex items-center justify-center w-9 h-9 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition-all hover:scale-110"
                               title="View Details">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL SECTION --}}
<div id="quickAddModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/50" onclick="closeQuickAdd()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl sm:max-w-lg w-full overflow-hidden">
            <form action="{{ route('record.store') }}" method="POST">
                @csrf
                <input type="hidden" name="first_name" id="modal_first_name">
                <input type="hidden" name="middle_name" id="modal_middle_name">
                <input type="hidden" name="last_name" id="modal_last_name">
                <input type="hidden" name="birthday" id="modal_birthday">
                <input type="hidden" name="gender" id="modal_gender">
                <input type="hidden" name="civil_status" id="modal_civil_status">
                <input type="hidden" name="address_purok" id="modal_address">
                <input type="hidden" name="contact_number" id="modal_contact">
                <input type="hidden" name="consultation_date" value="{{ now()->format('Y-m-d') }}">

                <div class="p-6">
                    <h3 class="text-xl font-bold text-slate-800 mb-4">New Consultation</h3>
                    <div class="bg-blue-50 rounded-lg p-3 mb-6">
                        <p class="text-[10px] text-blue-600 font-bold uppercase">Target Patient</p>
                        <p id="display_name" class="font-bold text-slate-800"></p>
                        <p id="display_dob" class="text-xs text-slate-500"></p>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Diagnosis</label>
                            <textarea name="diagnosis" rows="3" required class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter current condition..."></textarea>
                        </div>
                        
                        {{-- RESTORED MEDICINE UI --}}
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs font-bold text-slate-500 uppercase">Medicines Given</label>
                                <button type="button" onclick="createMedicineRow()" class="text-blue-600 text-xs font-bold hover:underline">+ ADD MEDICINE</button>
                            </div>
                            <div id="medicine-rows-container" class="space-y-2"></div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t flex justify-end gap-3">
                    <button type="button" onclick="closeQuickAdd()" class="text-sm font-bold text-slate-500 hover:text-slate-700">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 shadow-md">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const medicineDataElement = document.getElementById('medicine-data');
const allMedicines = medicineDataElement ? JSON.parse(medicineDataElement.dataset.medicines) : [];
const container = document.getElementById('medicine-rows-container');
let rowIndex = 0;

function createMedicineRow() {
    const div = document.createElement('div');
    div.className = "flex items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200";
    
    let options = '<option value="">Select...</option>';
    allMedicines.forEach(med => {
        options += `<option value="${med.id}">${med.name} (Stock: ${med.stock})</option>`;
    });

    div.innerHTML = `
        <select name="medicines[${rowIndex}][id]" required class="flex-1 p-2 border border-gray-200 rounded-lg text-sm focus:border-blue-500 outline-none">
            ${options}
        </select>
        <input type="number" name="medicines[${rowIndex}][quantity]" placeholder="Qty" required min="1" class="w-20 p-2 border border-gray-200 rounded-lg text-sm focus:border-blue-500 outline-none">
        <button type="button" class="text-red-400 hover:text-red-600 text-lg px-1" onclick="this.parentElement.remove()">&times;</button>
    `;
    container.appendChild(div);
    rowIndex++;
}

function handleOpenModal(button) {
    try {
        const record = JSON.parse(button.getAttribute('data-record'));
        openQuickAdd(record);
    } catch (e) {
        console.error("Error parsing patient data:", e);
    }
}

function openQuickAdd(record) {
    document.getElementById('modal_first_name').value = record.first_name;
    document.getElementById('modal_middle_name').value = record.middle_name || '';
    document.getElementById('modal_last_name').value = record.last_name;
    document.getElementById('modal_birthday').value = record.birthday;
    document.getElementById('modal_gender').value = record.gender;
    document.getElementById('modal_civil_status').value = record.civil_status || 'Single';
    document.getElementById('modal_address').value = record.address_purok;
    document.getElementById('modal_contact').value = record.contact_number || '';
    
    document.getElementById('display_name').innerText = `${record.first_name} ${record.last_name}`;
    
    // Formatting the date correctly for display
    const dob = new Date(record.birthday).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('display_dob').innerText = `Date of Birth: ${dob}`;
    
    // Ensure medicine rows appear
    container.innerHTML = '';
    createMedicineRow();
    
    document.getElementById('quickAddModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; 
}

function closeQuickAdd() {
    document.getElementById('quickAddModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Age Filter Logic
document.getElementById('ageFilter').addEventListener('change', function() {
    const filter = this.value;
    const rows = document.querySelectorAll('.patient-row');

    rows.forEach(row => {
        const years = parseInt(row.getAttribute('data-age-years'));
        const months = parseInt(row.getAttribute('data-age-months'));
        let show = false;

        if (filter === 'all') show = true;
        else if (filter === '0-11' && years === 0 && months <= 11) show = true;
        else if (filter === '12-59' && (years >= 1 && years < 5)) show = true;
        else if (filter === 'senior' && years >= 60) show = true;

        row.style.display = show ? '' : 'none';
    });
});
</script>
@endsection