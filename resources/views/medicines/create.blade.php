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
            
            {{-- This hidden input will hold the combined name sent to the database --}}
            <input type="hidden" name="name" id="combined_name">

            <div class="space-y-5">
                {{-- Generic Name Search Dropdown --}}
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Generic Name</label>
                    <div class="relative" id="genericDropdownWrap">
                        <input type="text" id="generic_name" placeholder="Search or type generic name..." required autocomplete="off"
                            class="w-full px-5 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition">
                        <div id="generic_dropdown" class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-64 overflow-y-auto z-30"></div>
                    </div>
                </div>

                {{-- Brand Name Input --}}
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Brand Name</label>
                    <div class="relative" id="brandDropdownWrap">
                        <input type="text" id="brand_name" placeholder="Search or type brand name..." required autofocus autocomplete="off"
                            class="w-full px-5 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition">
                        <div id="brand_dropdown" class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-64 overflow-y-auto z-30"></div>
                    </div>
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Type</label>
                    <div class="relative" id="medicineTypeDropdownWrap">
                        <input type="hidden" name="type" id="medicine_type" required>
                        <input type="text" id="medicine_type_search" placeholder="Search or type medicine type..." autocomplete="off"
                            class="w-full px-5 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition bg-white">
                        <div id="medicine_type_dropdown" class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-56 overflow-y-auto z-30"></div>
                    </div>
                </div>

                {{-- Dosage (Value + Unit) --}}
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Dosage</label>
                    <div class="grid grid-cols-[1fr_120px] gap-3">
                        <input type="number" name="dosage_value" id="dosage_value" placeholder="e.g. 500" min="0.01" step="0.01" required
                            class="w-full px-5 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition">
                        <select name="dosage_unit" id="dosage_unit" required
                            class="w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition bg-white">
                            <option value="mcg">mcg</option>
                            <option value="mg" selected>mg</option>
                            <option value="g">g</option>
                            <option value="ml">ml</option>
                        </select>
                    </div>
                    <p class="mt-2 text-[10px] text-gray-400 italic">Example output: Brand (Generic Name) 500mg Capsule</p>
                </div>

                {{-- Inventory Details --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Stock Number</label>
                        <input type="text" name="batch_number" placeholder="e.g. LOT-001" value="{{ old('batch_number') }}" required
                            class="w-full px-5 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Date Received</label>
                        <input type="date" name="arrival_date" value="{{ old('arrival_date', now()->format('Y-m-d')) }}" required
                            class="w-full px-5 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Expiration Date</label>
                        <input type="date" name="expiration_date" value="{{ old('expiration_date') }}" required
                            class="w-full px-5 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Quantity</label>
                        <input type="number" name="stock" min="1" value="{{ old('stock', 1) }}" required
                            class="w-full px-5 py-3.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition">
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

<script>
    const genericOptions = [
        { header: 'A' }, 'Acetaminophen', 'Acyclovir', 'Albuterol', 'Allopurinol', 'Amlodipine', 'Amoxicillin', 'Amitriptyline', 'Aspirin', 'Atorvastatin', 'Azithromycin',
        { header: 'B' }, 'Baclofen', 'Beclomethasone', 'Benzonatate', 'Bisoprolol', 'Budesonide',
        { header: 'C' }, 'Captopril', 'Carbamazepine', 'Cefalexin', 'Ceftriaxone', 'Cetirizine', 'Chlorpheniramine', 'Ciprofloxacin', 'Clarithromycin', 'Clopidogrel',
        { header: 'D' }, 'Diazepam', 'Diclofenac', 'Digoxin', 'Diphenhydramine', 'Doxycycline',
        { header: 'E' }, 'Enalapril', 'Erythromycin', 'Escitalopram',
        { header: 'F' }, 'Famotidine', 'Furosemide', 'Fluoxetine',
        { header: 'G' }, 'Gabapentin', 'Gliclazide',
        { header: 'H' }, 'Hydrochlorothiazide', 'Hydrocortisone',
        { header: 'I' }, 'Ibuprofen', 'Insulin', 'Isoniazid',
        { header: 'K' }, 'Ketoconazole',
        { header: 'L' }, 'Lansoprazole', 'Levothyroxine', 'Lisinopril', 'Loratadine', 'Losartan',
        { header: 'M' }, 'Metformin', 'Metoprolol', 'Metronidazole', 'Montelukast',
        { header: 'N' }, 'Naproxen', 'Nifedipine',
        { header: 'O' }, 'Omeprazole', 'Ondansetron',
        { header: 'P' }, 'Pantoprazole', 'Paracetamol', 'Penicillin', 'Prednisone',
        { header: 'R' }, 'Ranitidine', 'Rifampicin',
        { header: 'S' }, 'Salbutamol', 'Sertraline', 'Simvastatin', 'Spironolactone',
        { header: 'T' }, 'Tramadol',
        { header: 'V' }, 'Valsartan',
        { header: 'W' }, 'Warfarin',
        { header: 'Z' }, 'Zinc sulfate', 'Zolpidem',
    ];
    const brandOptions = [
        { header: 'A' }, 'Abilify', 'Actifed', 'Adalat', 'Advil', 'Aerius', 'Aldactone', 'Alaxan', 'Allegra', 'Allerta', 'Ambien', 'Ambrolex', 'Amoxil', 'Augmentin', 'Aspirin Protect', 'Atarax', 'Atenolol (various brands)', 'Ativan', 'Atozet', 'Avamys',
        { header: 'B' }, 'Bactidol', 'Bactrim', 'Benadryl', 'Berocca', 'Betadine', 'Bioflu', 'Biogesic', 'Bonamine', 'Bonviva', 'Brufen', 'Buscopan', 'Bystolic',
        { header: 'C' }, 'Calpol', 'Campral', 'Capoten', 'Cardizem', 'Cataflam', 'Celebrex', 'Ceelin', 'Centrum', 'Cipro', 'Claritin', 'Clarinase', 'Clavulin', 'Co-Amoxiclav (various brands)', 'Cozaar', 'Crestor', 'Cymbalta',
        { header: 'D' }, 'Daktarin', 'Decolgen', 'Deltasone', 'Dettol', 'Diovan', 'Diphereline', 'Dulcolax', 'Duspatalin',
        { header: 'E' }, 'Elica', 'Elavil', 'Elevit', 'Emanera', 'Emeset', 'Enervon', 'Erythrocin', 'Eskinol', 'Exforge', 'Exelon',
        { header: 'F' }, 'Flagyl', 'Flomax', 'Forxiga', 'Fortum', 'Fucidin', 'Furosemide (various brands)',
        { header: 'G' }, 'Gaviscon', 'Glucophage', 'Gluta-C', 'Glycomet', 'Gravol',
        { header: 'H' }, 'Hemarate', 'Hemovit', 'Humalog', 'Humulin', 'Hydrite',
        { header: 'I' }, 'Imodium', 'Imuran', 'Inderal', 'Insulatard', 'Irbesartan (various brands)', 'Isoptin',
        { header: 'J' }, 'Jardiance', 'Josacine',
        { header: 'K' }, 'Kalium Durule', 'Keflex', 'Ketosteril', 'Klaricid', 'Kremil-S',
        { header: 'L' }, 'Lacosteine', 'Lactacyd', 'Lamisil', 'Lasix', 'Leflox', 'Lescol', 'Levoxyl', 'Lexapro', 'Lipitor', 'Lisinopril (various brands)', 'Loperamide (various brands)', 'Losec', 'Lovenox',
        { header: 'M' }, 'Maalox', 'Macrobid', 'Medicol', 'Medrol', 'Meloxicam (various brands)', 'Meronem', 'Micardis', 'Microgynon', 'Motilium', 'Motrin', 'Mucosolvan', 'Myonal',
        { header: 'N' }, 'Natrilix', 'Neozep', 'Neurontin', 'Nexium', 'Nifedipine (various brands)', 'Nizoral', 'Norflex', 'Norvasc', 'Novolin', 'NovoRapid',
        { header: 'O' }, 'Olmetec', 'Omnicef', 'Omron (medical products brand)', 'Onbrez', 'Oracort', 'Oral-B (medical/dental brand)',
        { header: 'P' }, 'Panadol', 'Pantoloc', 'Pariet', 'Pen-Vee K', 'Pharex B-Complex', 'Plasil', 'Plavix', 'Ponstan', 'Pred Forte', 'Protonix', 'Pulmicort',
        { header: 'Q' }, 'Questran', 'Quibron',
        { header: 'R' }, 'Ranitac', 'Relenza', 'Renitec', 'Rhinathiol', 'Rivotril', 'Rocephin', 'Rogin-E', 'Roxithromycin (various brands)',
        { header: 'S' }, 'Salonpas', 'Sandostatin', 'Sandoz (pharma brand)', 'Serc', 'Seretide', 'Seroquel', 'Sinutab', 'Solmux', 'Spiriva', 'Stresstabs', 'Symbicort', 'Synthroid',
        { header: 'T' }, 'Tamiflu', 'Tegretol', 'Tempra', 'Tenormin', 'Terbinafine (various brands)', 'Tetanus Toxoid (various brands)', 'Tobrex', 'Tramal', 'Trihydral', 'Tuseran',
        { header: 'U' }, 'Ultram', 'Unilab (pharma brand)', 'Ursinol',
        { header: 'V' }, 'Valium', 'Vastarel', 'Ventolin', 'Vermox', 'Vibramycin', 'Vidisic', 'Voren',
        { header: 'W' }, 'Warfarin (various brands)',
        { header: 'X' }, 'Xalatan', 'Xanax', 'Xigduo', 'Xyzal',
        { header: 'Y' }, 'Yasmin', 'Yaz',
        { header: 'Z' }, 'Zantac', 'Zestril', 'Zinnat', 'Zithromax', 'Zocor', 'Zoloft', 'Zovirax',
    ];

    const medicineTypes = [
        'Tablet',
        'Capsule',
        'Syrup',
        'Suspension',
        'Drops',
        'Inhaler',
        'Cream',
        'Ointment',
        'Eye Drops',
        'Nebule',
    ];

    const typeWrap = document.getElementById('medicineTypeDropdownWrap');
    const typeSearchInput = document.getElementById('medicine_type_search');
    const typeHiddenInput = document.getElementById('medicine_type');
    const typeDropdown = document.getElementById('medicine_type_dropdown');
    const genericWrap = document.getElementById('genericDropdownWrap');
    const genericInput = document.getElementById('generic_name');
    const genericDropdown = document.getElementById('generic_dropdown');
    const brandWrap = document.getElementById('brandDropdownWrap');
    const brandInput = document.getElementById('brand_name');
    const brandDropdown = document.getElementById('brand_dropdown');

    function renderAlphabeticalDropdown(dropdownEl, options, inputEl, filter = '', emptyText = 'No matching item found.') {
        const query = filter.trim().toLowerCase();
        dropdownEl.innerHTML = '';

        if (query) {
            const matches = options.filter(item => typeof item === 'string' && item.toLowerCase().includes(query));
            if (matches.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'px-4 py-2.5 text-sm text-gray-400';
                empty.textContent = emptyText;
                dropdownEl.appendChild(empty);
                return;
            }

            matches.forEach(name => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'w-full text-left px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-blue-50';
                option.textContent = name;
                option.addEventListener('click', function () {
                    inputEl.value = name;
                    dropdownEl.classList.add('hidden');
                });
                dropdownEl.appendChild(option);
            });

            return;
        }

        options.forEach(item => {
            if (typeof item === 'string') {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'w-full text-left px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-blue-50';
                option.textContent = item;
                option.addEventListener('click', function () {
                    inputEl.value = item;
                    dropdownEl.classList.add('hidden');
                });
                dropdownEl.appendChild(option);
                return;
            }

            const header = document.createElement('div');
            header.className = 'px-4 py-2 text-[11px] font-black text-gray-400 uppercase tracking-wider bg-gray-50 sticky top-0';
            header.textContent = item.header;
            dropdownEl.appendChild(header);
        });
    }

    function renderGenericOptions(filter = '') {
        renderAlphabeticalDropdown(
            genericDropdown,
            genericOptions,
            genericInput,
            filter,
            'No matching generic name found.'
        );
    }
    function renderBrandOptions(filter = '') {
        renderAlphabeticalDropdown(
            brandDropdown,
            brandOptions,
            brandInput,
            filter,
            'No matching brand name found.'
        );
    }

    function renderTypeOptions(filter = '') {
        const query = filter.trim().toLowerCase();
        const filteredTypes = medicineTypes.filter(type => type.toLowerCase().includes(query));
        typeDropdown.innerHTML = '';

        if (filteredTypes.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'px-4 py-2.5 text-sm text-gray-400';
            empty.textContent = 'No matching type found.';
            typeDropdown.appendChild(empty);
            return;
        }

        filteredTypes.forEach(type => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'w-full text-left px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-blue-50';
            option.textContent = type;
            option.addEventListener('click', function () {
                typeSearchInput.value = type;
                typeHiddenInput.value = type;
                typeDropdown.classList.add('hidden');
            });
            typeDropdown.appendChild(option);
        });
    }

    typeSearchInput.addEventListener('focus', function () {
        renderTypeOptions(typeSearchInput.value);
        typeDropdown.classList.remove('hidden');
    });

    typeSearchInput.addEventListener('input', function () {
        const typed = typeSearchInput.value.trim();
        typeHiddenInput.value = typed;
        renderTypeOptions(typed);
        typeDropdown.classList.remove('hidden');
    });

    genericInput.addEventListener('focus', function () {
        renderGenericOptions(genericInput.value);
        genericDropdown.classList.remove('hidden');
    });

    genericInput.addEventListener('input', function () {
        renderGenericOptions(genericInput.value);
        genericDropdown.classList.remove('hidden');
    });
    brandInput.addEventListener('focus', function () {
        renderBrandOptions(brandInput.value);
        brandDropdown.classList.remove('hidden');
    });

    brandInput.addEventListener('input', function () {
        renderBrandOptions(brandInput.value);
        brandDropdown.classList.remove('hidden');
    });

    document.addEventListener('click', function (event) {
        if (!typeWrap.contains(event.target)) {
            typeDropdown.classList.add('hidden');
        }
        if (!genericWrap.contains(event.target)) {
            genericDropdown.classList.add('hidden');
        }
        if (!brandWrap.contains(event.target)) {
            brandDropdown.classList.add('hidden');
        }
    });

    document.getElementById('medicineForm').addEventListener('submit', function(e) {
        const brand = document.getElementById('brand_name').value.trim();
        const generic = document.getElementById('generic_name').value.trim();
        const dosageValue = document.getElementById('dosage_value').value.trim();
        const dosageUnit = document.getElementById('dosage_unit').value.trim();
        const type = document.getElementById('medicine_type').value.trim();
        
        // Combines them into "Brand (Generic) 500mg Tablet" format before sending to Controller
        document.getElementById('combined_name').value = `${brand} (${generic}) ${dosageValue}${dosageUnit} ${type}`.trim();
    });
</script>
@endsection