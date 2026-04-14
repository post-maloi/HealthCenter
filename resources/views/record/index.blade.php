<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Records | CLINIC OS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <aside class="w-64 bg-slate-900 text-white">
            <div class="p-6 text-xl font-bold border-b border-slate-800">CLINIC OS</div>
            <nav class="mt-6">
                <a href="{{ route('dashboard') }}" class="block py-3 px-6 hover:bg-slate-800 transition">Dashboard</a>
                <a href="{{ route('record.index') }}" class="block py-3 px-6 bg-blue-600">Clinic Records</a>
            </nav>
        </aside>

        <main class="flex-1 p-10 overflow-y-auto">
            <div class="max-w-7xl mx-auto">
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Clinic Records</h1>
                        <p class="text-gray-500 text-sm mt-1">Showing unique patient history</p>
                    </div>

                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <form action="{{ route('record.index') }}" method="GET" class="flex items-center gap-2 flex-1 md:w-auto">
                            <div class="relative flex-1 md:w-64">
                                <input 
                                    type="text" 
                                    name="search" 
                                    value="{{ request('search') }}"
                                    placeholder="Search name or diagnosis..." 
                                    class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
                                >
                                <div class="absolute left-3 top-2.5 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>

                            <select 
                                name="age_group" 
                                onchange="this.form.submit()" 
                                class="bg-white border border-gray-200 text-gray-700 py-2 px-4 rounded-lg outline-none focus:border-blue-500 transition cursor-pointer"
                            >
                                <option value="">All Ages</option>
                                <option value="infant" {{ request('age_group') == 'infant' ? 'selected' : '' }}>0-11 Months</option>
                                <option value="child" {{ request('age_group') == 'child' ? 'selected' : '' }}>12-59 Months</option>
                                <option value="senior" {{ request('age_group') == 'senior' ? 'selected' : '' }}>Senior Citizen</option>
                            </select>
                        </form>

                        <a href="{{ route('record.create') }}" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-lg shadow-sm hover:bg-blue-700 transition flex items-center gap-2 shrink-0">
                            <span>+</span> New Entry
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Latest Consultation</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Patient Name</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Age/Gender</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Latest Diagnosis</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($records as $record)
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $record->consultation_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800 capitalize">
                                    {{ $record->patient_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
    @php
        // Use diffInMonths for a whole number directly, 
        // or floor() if you are doing custom math.
        $months = floor($record->birthday->diffInMonths(now()));
    @endphp

    @if($months < 12)
        {{ (int)$months }} Mon / {{ $record->gender }}
    @else
        {{ $record->age }} yrs / {{ $record->gender }}
    @endif
</td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ \Illuminate\Support\Str::limit($record->diagnosis, 50) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('record.show', $record) }}" class="text-blue-600 font-bold hover:text-blue-800 underline">
                                        View History & Details
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                                    @if(request('search') || request('age_group'))
                                        No patient records found matching your filters.
                                        <div class="mt-2">
                                            <a href="{{ route('record.index') }}" class="text-blue-600 underline not-italic font-bold">Clear All Filters</a>
                                        </div>
                                    @else
                                        No records found in the database.
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>