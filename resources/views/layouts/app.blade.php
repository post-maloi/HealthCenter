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
            
            <nav class="mt-6 flex-1 px-4 space-y-1">
                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}" 
                   class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    Dashboard
                </a>
                
                {{-- Clinic Records (Using wildcard * to stay active when viewing specific records) --}}
                <a href="{{ route('record.index') }}" 
                   class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('record.*') && !request()->routeIs('record.create') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    Clinic Records
                </a>
                
                {{-- Inventory Medicine --}}
                <a href="{{ route('medicines.index') }}" 
                   class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('medicines.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    Inventory Medicine
                </a>

                {{-- Reports Dropdown --}}
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
                            Diagnosis
                        </a>
                        <a href="{{ route('reports.patients') }}"
                           class="block py-2 px-4 rounded-lg text-sm transition {{ request()->routeIs('reports.patients') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            Patient
                        </a>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-800">
                    {{-- Add New Consultation --}}
                    <a href="{{ route('record.create') }}" 
                       class="block py-3 px-4 rounded-lg transition {{ request()->routeIs('record.create') ? 'bg-slate-800 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <span class="mr-2 text-blue-500 font-bold">+</span> Add New Consultation
                    </a>
                </div>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-left py-2 px-4 text-slate-400 hover:text-red-400 flex items-center gap-2 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Logout
                    </button>
                </form>
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