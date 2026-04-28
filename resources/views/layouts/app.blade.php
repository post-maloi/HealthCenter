<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic OS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/paginationjs@2.6.0/dist/pagination.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/paginationjs@2.6.0/dist/pagination.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down { animation: fadeInDown 0.3s ease-out; }

        /* Unified PaginationJS (Clinic OS): summary left, dark segmented controls right */
        .clinic-os-pagination {
            display: flex !important;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem 1rem;
            width: 100%;
            padding: 0.5rem 0;
        }
        .clinic-os-pagination::after { display: none !important; content: none !important; }
        .clinic-os-pagination .paginationjs-nav {
            float: none !important;
            margin: 0 !important;
            font-size: 0.875rem;
            line-height: 1.5;
            color: #64748b;
            font-weight: 500;
        }
        .clinic-os-pagination .paginationjs-pages {
            float: none !important;
            margin-left: 0 !important;
        }
        .clinic-os-pagination .paginationjs-pages ul {
            float: none !important;
            display: flex;
            margin: 0;
            padding: 0;
            list-style: none;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }
        .clinic-os-pagination .paginationjs-pages li {
            float: none !important;
            display: flex;
            margin: 0;
            border: none !important;
            border-right: 1px solid #334155 !important;
        }
        .clinic-os-pagination .paginationjs-pages li:last-child {
            border-right: none !important;
        }
        /* Base cell: dark bar (all segments same family as reference image) */
        .clinic-os-pagination .paginationjs-pages li > a {
            min-width: 2.5rem;
            min-height: 2.5rem;
            line-height: 2.5rem;
            display: block;
            text-align: center;
            text-decoration: none;
            background: #1e293b !important;
            color: #f1f5f9 !important;
            font-size: 0.875rem;
            font-weight: 600;
            border: none !important;
            cursor: pointer;
            box-sizing: border-box;
        }
        /* Page numbers: bright text */
        .clinic-os-pagination .paginationjs-pages li.J-paginationjs-page > a {
            color: #f1f5f9 !important;
        }
        /* Prev / Next: muted chevrons (not pure white) */
        .clinic-os-pagination .paginationjs-pages li.paginationjs-prev > a,
        .clinic-os-pagination .paginationjs-pages li.paginationjs-next > a {
            color: #94a3b8 !important;
            font-weight: 600;
        }
        .clinic-os-pagination .paginationjs-pages li.paginationjs-prev > a:hover,
        .clinic-os-pagination .paginationjs-pages li.paginationjs-next > a:hover {
            color: #cbd5e1 !important;
            background: #334155 !important;
        }
        .clinic-os-pagination .paginationjs-pages li > a:hover {
            background: #334155 !important;
        }
        /* Active page: slightly darker segment only; label stays light */
        .clinic-os-pagination .paginationjs-pages li.active > a,
        .clinic-os-pagination .paginationjs-pages li.active > a:hover {
            background: #0f172a !important;
            color: #f8fafc !important;
            cursor: default;
        }
        .clinic-os-pagination .paginationjs-pages li.disabled > a,
        .clinic-os-pagination .paginationjs-pages li.disabled > a:hover {
            background: #1e293b !important;
            color: #64748b !important;
            cursor: not-allowed;
        }
        .clinic-os-pagination .paginationjs-pages li.disabled.paginationjs-prev > a,
        .clinic-os-pagination .paginationjs-pages li.disabled.paginationjs-next > a,
        .clinic-os-pagination .paginationjs-pages li.disabled.paginationjs-prev > a:hover,
        .clinic-os-pagination .paginationjs-pages li.disabled.paginationjs-next > a:hover {
            color: #64748b !important;
        }
        .clinic-os-pagination .paginationjs-pages li.paginationjs-ellipsis {
            border-right: 1px solid #334155 !important;
        }
        .clinic-os-pagination .paginationjs-pages li.paginationjs-ellipsis a {
            background: #1e293b !important;
            color: rgba(255, 255, 255, 0.7) !important;
        }
        @media (max-width: 640px) {
            .clinic-os-pagination { flex-direction: column; align-items: stretch !important; }
            .clinic-os-pagination .paginationjs-pages { align-self: center; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        
        <aside class="w-64 bg-slate-900 text-white flex flex-col shadow-xl z-10">
            <div class="p-6 text-xl font-bold border-b border-slate-800 flex items-center gap-2">
                @php
                    $clinicName = \App\Models\Setting::getValue('clinic_name', 'Brgy. Banilad Health Center') ?: 'Brgy. Banilad Health Center';
                    $clinicLogoPath = \App\Models\Setting::getValue('clinic_logo');
                    $clinicLogoUrl = $clinicLogoPath ? asset('storage/' . ltrim($clinicLogoPath, '/')) : null;
                @endphp
                @if($clinicLogoUrl)
                    <img src="{{ $clinicLogoUrl }}" alt="Clinic logo" class="w-7 h-7 rounded-md object-cover border border-slate-700">
                @else
                    <span class="text-blue-500">✚</span>
                @endif
                <span class="leading-tight">{{ $clinicName }}</span>
            </div>
            
            @php
                $role = auth()->check() ? strtolower(trim((string) (auth()->user()->role ?? 'bhw'))) : 'guest';
                $isDoctor = $role === 'doctor';
                $isNurse = $role === 'nurse';
                $isBhw = $role === 'bhw';
                $isAdmin = $role === 'admin';
                $authUser = auth()->user();
                $displayName = $authUser?->full_name ?: trim(implode(' ', array_filter([$authUser?->first_name, $authUser?->last_name]))) ?: 'Clinic User';
                $roleLabel = $authUser ? strtoupper((string) $authUser->role) : 'GUEST';
                $initials = strtoupper(substr((string) ($authUser?->first_name ?? 'C'), 0, 1) . substr((string) ($authUser?->last_name ?? 'U'), 0, 1));
                $profilePhotoUrl = (!empty($authUser?->profile_photo_path))
                    ? asset('storage/'.$authUser->profile_photo_path)
                    : null;
                $doctorAvailable = \App\Models\User::query()
                    ->where('role', 'doctor')
                    ->get()
                    ->contains(fn($u) => $u->is_doctor_available);
            @endphp

            <nav class="mt-6 flex-1 px-4 space-y-1">
                <a href="{{ $isAdmin ? route('admin.dashboard') : ($isNurse ? route('nurse.dashboard') : ($role === 'doctor' ? route('doctor.dashboard') : route('bhw.dashboard'))) }}"
                   class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('dashboard') || request()->routeIs('bhw.dashboard') || request()->routeIs('nurse.dashboard') || request()->routeIs('doctor.dashboard') || request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-2 inline-flex align-middle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.5L12 3l9 7.5M5.25 9.75V20a1 1 0 001 1h4.5v-5.25a1 1 0 011-1h.5a1 1 0 011 1V21h4.5a1 1 0 001-1V9.75"/>
                        </svg>
                    </span> Dashboard
                </a>
                
                {{-- Clinic Records (Using wildcard * to stay active when viewing specific records) --}}
                <a href="{{ $role === 'doctor' ? route('doctor.record.index') : ($isNurse ? route('nurse.record.index') : ($isBhw ? route('bhw.record.index') : route('record.index'))) }}" 
                   class="block py-3 px-4 rounded-lg transition {{ ($role === 'doctor' && request()->routeIs('doctor.record.*') && !request()->routeIs('doctor.record.create')) || ($isNurse && request()->routeIs('nurse.record.*') && !request()->routeIs('nurse.record.create')) || ($isBhw && request()->routeIs('bhw.record.*') && !request()->routeIs('bhw.record.create')) || (!$isDoctor && !$isNurse && !$isBhw && request()->routeIs('record.*') && !request()->routeIs('record.create')) ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-2 inline-flex align-middle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span> Patients
                </a>

                <a href="{{ $isBhw ? route('bhw.medicines.index') : route('medicines.index') }}"
                   class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('medicines.*') || request()->routeIs('bhw.medicines.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-2 inline-flex align-middle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 8.5l7 7m-9.5 1a3.5 3.5 0 010-5l5.5-5.5a3.5 3.5 0 115 5L11 16.5a3.5 3.5 0 01-5 0z"/>
                        </svg>
                    </span> Inventory
                </a>

                @if($isDoctor || $isNurse)
                    <a href="{{ $isDoctor ? route('doctor.pending.index') : route('nurse.pending.index') }}"
                       class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('doctor.pending.*') || request()->routeIs('nurse.pending.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <span class="mr-2 inline-flex align-middle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-6 14h6M9 5a1 1 0 000 2h.5a1 1 0 011 1v1.5a3.5 3.5 0 01-1.025 2.475L8.8 13.15a2 2 0 000 2.828l1.175 1.175A3.5 3.5 0 0111 19.628V20a1 1 0 01-1 1H9m6-16a1 1 0 010 2h-.5a1 1 0 00-1 1v1.5a3.5 3.5 0 001.025 2.475L15.2 13.15a2 2 0 010 2.828l-1.175 1.175A3.5 3.5 0 0013 19.628V20a1 1 0 001 1h1"/>
                            </svg>
                        </span> Pending Patients
                    </a>
                @endif

                @if($isAdmin)
                    <a href="{{ route('admin.users.index') }}" class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"><span class="mr-2 inline-flex align-middle"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a3 3 0 00-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span> User Management</a>
                    <a href="{{ route('admin.reports.index') }}" class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('admin.reports.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"><span class="mr-2 inline-flex align-middle"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 20h14a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg></span> Reports</a>
                    <a href="{{ route('admin.activity-logs.index') }}" class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('admin.activity-logs.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"><span class="mr-2 inline-flex align-middle"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span> Activity Logs</a>
                    <a href="{{ route('admin.settings.index') }}" class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"><span class="mr-2 inline-flex align-middle"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.983 5.25c.472-1.52 2.562-1.52 3.034 0a1.75 1.75 0 002.624 1.016c1.34-.85 2.817.627 1.967 1.967a1.75 1.75 0 001.016 2.624c1.52.472 1.52 2.562 0 3.034a1.75 1.75 0 00-1.016 2.624c.85 1.34-.627 2.817-1.967 1.967a1.75 1.75 0 00-2.624 1.016c-.472 1.52-2.562 1.52-3.034 0a1.75 1.75 0 00-2.624-1.016c-1.34.85-2.817-.627-1.967-1.967a1.75 1.75 0 00-1.016-2.624c-1.52-.472-1.52-2.562 0-3.034a1.75 1.75 0 001.016-2.624c-.85-1.34.627-2.817 1.967-1.967a1.75 1.75 0 002.624-1.016z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span> Settings</a>
                    <a href="{{ route('admin.inventory.ledger') }}" class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('admin.inventory.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"><span class="mr-2 inline-flex align-middle"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14l-8 4m-8-4v10l8 4m-8-14l8 4m0 0v10"/></svg></span> Inventory Ledger</a>
                @endif

                @unless($isDoctor || $isAdmin || $isNurse)
                    {{-- Reports Dropdown --}}
                    @if($isBhw)
                    <div class="pt-1">
                        <button type="button"
                            onclick="toggleReportsMenu()"
                            class="w-full flex justify-between items-center py-3 px-4 rounded-lg transition {{ request()->routeIs('reports.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <span>Reports</span>
                            <svg id="reports-arrow" class="w-4 h-4 transition-transform {{ request()->routeIs('reports.*') ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div id="reports-menu" class="ml-4 mt-1 space-y-1 {{ request()->routeIs('reports.*') ? '' : 'hidden' }}">
                            <a href="{{ route('bhw.reports.diagnosis') }}"
                               class="block py-2 px-4 rounded-lg text-sm transition {{ request()->routeIs('reports.diagnosis') || request()->routeIs('bhw.reports.diagnosis') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span class="mr-2 inline-flex align-middle"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 20h14a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg></span>Diagnosis
                            </a>
                            <a href="{{ route('bhw.reports.patients') }}"
                               class="block py-2 px-4 rounded-lg text-sm transition {{ request()->routeIs('reports.patients') || request()->routeIs('bhw.reports.patients') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span class="mr-2 inline-flex align-middle"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>Patient
                            </a>
                        </div>
                    </div>
                    @endif

                    <div class="pt-4 mt-4 border-t border-slate-800">
                        <a href="{{ route('bhw.record.create') }}" 
                           class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('record.create') || request()->routeIs('bhw.record.create') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <span class="mr-2 inline-flex align-middle text-blue-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span> Add New Consultation
                        </a>
                    </div>
                @endunless

            </nav>

            <div class="p-4 border-t border-slate-800 relative" x-data="{ openUserMenu: false }">
                <div class="flex items-center gap-3">
                    @if($profilePhotoUrl)
                        <img src="{{ $profilePhotoUrl }}" alt="Profile photo" class="w-9 h-9 rounded-full object-cover border border-slate-600">
                    @else
                        <div class="w-9 h-9 rounded-full bg-slate-700 text-slate-100 flex items-center justify-center text-xs font-black">
                            {{ $initials }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-100 truncate">{{ $displayName }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $roleLabel }}</p>
                    </div>
                    <button type="button"
                        class="w-8 h-8 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition flex items-center justify-center"
                        @click="openUserMenu = !openUserMenu"
                        aria-label="Open user menu">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 5.5A1.5 1.5 0 1010 8a1.5 1.5 0 000 3.5zM11.5 15a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                        </svg>
                    </button>
                </div>

                <div x-show="openUserMenu"
                    @click.away="openUserMenu = false"
                    x-transition
                    style="display:none;"
                    class="absolute left-4 right-4 bottom-16 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl overflow-hidden z-50">
                    <a href="{{ route('profile.show') }}" class="w-full text-left py-3 px-4 text-slate-200 hover:bg-slate-700 hover:text-white flex items-center gap-2 transition border-b border-slate-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.88 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        View Profile
                    </a>
                    <a href="{{ route('logout.get') }}" class="w-full text-left py-3 px-4 text-slate-200 hover:bg-slate-700 hover:text-white flex items-center gap-2 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Log Out
                    </a>
                </div>
            </div>
        </aside>

        <main class="flex-1 p-10 overflow-y-auto relative bg-slate-50">
            @if(session('success'))
                <div id="alert-msg" class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 shadow-sm rounded-r-lg flex justify-between items-center animate-fade-in-down">
                    <span>{{ session('success') }}</span>
                    <button onclick="document.getElementById('alert-msg').remove()" class="text-green-900 font-bold">&times;</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleReportsMenu() {
            const menu = document.getElementById('reports-menu');
            const arrow = document.getElementById('reports-arrow');
            if (!menu || !arrow) return;

            menu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }

        setTimeout(() => {
            const alert = document.getElementById('alert-msg');
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
    </script>
    <script src="{{ asset('js/pagination.js') }}"></script>
    @stack('scripts')
</body>
</html>