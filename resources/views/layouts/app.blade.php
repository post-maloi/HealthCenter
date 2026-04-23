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
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        
        <aside class="w-64 bg-slate-900 text-white flex flex-col shadow-xl z-10">
            <div class="p-6 text-xl font-bold border-b border-slate-800 flex items-center gap-2">
                <span class="text-blue-500">✚</span> CLINIC OS
            </div>
            
            @php
                $role = auth()->check() ? (auth()->user()->role ?? 'bhw') : 'guest';
                $isDoctor = in_array($role, ['doctor', 'nurse'], true);
                $isNurse = $role === 'nurse';
                $isBhw = $role === 'bhw';
                $isAdmin = $role === 'admin';
                $authUser = auth()->user();
                $displayName = $authUser?->full_name ?: trim(implode(' ', array_filter([$authUser?->first_name, $authUser?->last_name]))) ?: 'Clinic User';
                $roleLabel = $authUser ? strtoupper((string) $authUser->role) : 'GUEST';
                $initials = strtoupper(substr((string) ($authUser?->first_name ?? 'C'), 0, 1) . substr((string) ($authUser?->last_name ?? 'U'), 0, 1));
                $doctorAvailable = \App\Models\User::query()
                    ->where('role', 'doctor')
                    ->get()
                    ->contains(fn($u) => $u->is_doctor_available);
            @endphp

            <nav class="mt-6 flex-1 px-4 space-y-1">
                <a href="{{ $isAdmin ? route('admin.dashboard') : ($isDoctor ? route('doctor.dashboard') : route('dashboard')) }}"
                   class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('dashboard') || request()->routeIs('doctor.dashboard') || request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-2">🏠</span> Dashboard
                </a>
                
                {{-- Clinic Records (Using wildcard * to stay active when viewing specific records) --}}
                <a href="{{ $isDoctor ? route('doctor.record.index') : route('record.index') }}" 
                   class="block py-3 px-4 rounded-lg transition {{ $isDoctor ? (request()->routeIs('doctor.record.*') && !request()->routeIs('doctor.record.create') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white') : (request()->routeIs('record.*') && !request()->routeIs('record.create') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white') }}">
                    <span class="mr-2">🧾</span> Patients
                </a>

                @unless($isDoctor || $isBhw)
                    <a href="{{ route('record.index') }}"
                       class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('record.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <span class="mr-2">🩺</span> Consultations
                    </a>
                @endunless

                <a href="{{ route('medicines.index') }}"
                   class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('medicines.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-2">💊</span> Inventory
                </a>

                @if($isAdmin)
                    <a href="{{ route('admin.users.index') }}" class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"><span class="mr-2">👥</span> User Management</a>
                    <a href="{{ route('admin.reports.index') }}" class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('admin.reports.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"><span class="mr-2">📊</span> Reports</a>
                    <a href="{{ route('admin.activity-logs.index') }}" class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('admin.activity-logs.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"><span class="mr-2">🕒</span> Activity Logs</a>
                    <a href="{{ route('admin.settings.index') }}" class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"><span class="mr-2">⚙️</span> Settings</a>
                    <a href="{{ route('admin.inventory.ledger') }}" class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('admin.inventory.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"><span class="mr-2">📦</span> Inventory Ledger</a>
                @endif

                @unless($isDoctor || $isAdmin)
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
                            <a href="{{ route('reports.diagnosis') }}"
                               class="block py-2 px-4 rounded-lg text-sm transition {{ request()->routeIs('reports.diagnosis') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                📊 Diagnosis
                            </a>
                            <a href="{{ route('reports.patients') }}"
                               class="block py-2 px-4 rounded-lg text-sm transition {{ request()->routeIs('reports.patients') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                🧾 Patient
                            </a>
                        </div>
                    </div>
                    @endif

                    <div class="pt-4 mt-4 border-t border-slate-800">
                        <a href="{{ $isNurse ? route('record.index') : route('record.create') }}" 
                           class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('record.create') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            @if($isNurse)
                                <span class="mr-2 text-blue-500 font-bold">+</span> Nurse Vitals / Triage
                            @else
                                <span class="mr-2 text-blue-500 font-bold">+</span> Add New Consultation
                            @endif
                        </a>
                    </div>
                @endunless

            </nav>

            <div class="p-4 border-t border-slate-800 relative" x-data="{ openUserMenu: false }">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-slate-700 text-slate-100 flex items-center justify-center text-xs font-black">
                        {{ $initials }}
                    </div>
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
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left py-3 px-4 text-slate-200 hover:bg-slate-700 hover:text-white flex items-center gap-2 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="flex-1 p-10 overflow-y-auto relative bg-slate-50">
            <div class="mb-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold {{ $doctorAvailable ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    <span class="w-2 h-2 rounded-full {{ $doctorAvailable ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    {{ $doctorAvailable ? 'Doctor Available' : 'Doctor Not Available' }}
                </div>
            </div>
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