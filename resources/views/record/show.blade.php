<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient Record | CLINIC OS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen bg-gray-100">
        <aside class="w-64 bg-slate-900 text-white hidden md:block">
            <div class="p-6 text-xl font-bold border-b border-slate-800">CLINIC OS</div>
            <nav class="mt-6">
                <a href="{{ route('dashboard') }}" class="block py-3 px-6 hover:bg-slate-800 {{ request()->routeIs('dashboard') ? 'bg-blue-600' : '' }}">Dashboard</a>
                <a href="{{ route('record.index') }}" class="block py-3 px-6 hover:bg-slate-800 {{ request()->routeIs('record.*') ? 'bg-blue-600' : '' }}">Clinic Records</a>
            </nav>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">
            <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-200">
                
                <div class="p-8 border-b border-gray-100 flex justify-between items-start">
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-800">{{ $record->patient_name }}</h2>
                        <p class="text-blue-600 font-medium mt-1">Patient Clinical File</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Date of Consultation</span>
                        <p class="text-lg font-semibold text-slate-700">{{ $record->consultation_date->format('M d, Y') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-8 p-8 bg-slate-50/50">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase">Gender</label>
                        <p class="text-slate-800 font-medium">{{ $record->gender }}</p>
                    </div>
                  <div>
    <label class="block text-xs font-bold text-gray-400 uppercase">Age</label>
    <p class="text-slate-800 font-medium">
        @if($record->birthday->diffInMonths(now()) < 12)
            {{ $record->birthday->diffInMonths(now()) }} Months
        @else
            {{ $record->age }} Years
        @endif
    </p>
</div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase">Birthday</label>
                        <p class="text-slate-800 font-medium">{{ $record->birthday->format('F d, Y') }}</p>
                    </div>
                </div>

                <div class="p-8 space-y-8">
                    <section>
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800 uppercase mb-3">
                            <span class="w-2 h-2 bg-red-500 rounded-full"></span> Current Diagnosis
                        </h3>
                        <div class="p-5 bg-white border border-gray-200 rounded-xl text-slate-700 leading-relaxed italic">
                            {{ $record->diagnosis }}
                        </div>
                    </section>

                    <section>
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800 uppercase mb-3">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Medicines Given
                        </h3>
                        <div class="p-5 bg-white border border-gray-200 rounded-xl text-slate-700 leading-relaxed">
                            {{ $record->medicines_given }}
                        </div>
                    </section>
                </div>

                <div class="p-8 bg-slate-50 border-t border-gray-100">
                    <h3 class="text-sm font-bold text-slate-800 uppercase mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Past Consultations ({{ $history->count() }})
                    </h3>
                    
                    <div class="space-y-3">
                        @foreach($history as $visit)
                            <div class="p-4 bg-white border rounded-xl shadow-sm flex justify-between items-center {{ $visit->id == $record->id ? 'ring-2 ring-blue-500' : '' }}">
                                <div>
                                    <p class="font-bold text-slate-700">{{ $visit->consultation_date->format('M d, Y') }}</p>
                                    <p class="text-sm text-gray-500 truncate max-w-xs">Diagnosis: {{ $visit->diagnosis }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($visit->id == $record->id)
                                        <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-2 py-1 rounded uppercase">Current View</span>
                                    @else
                                        <a href="{{ route('record.show', $visit->id) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">View History →</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-end gap-4">
                    <a href="{{ route('record.index') }}" class="px-5 py-2 rounded-lg border border-gray-300 text-gray-600 font-semibold hover:bg-white transition">Back to List</a>
                    <button class="px-5 py-2 rounded-lg border border-gray-300 text-gray-600 font-semibold hover:bg-white transition">Print PDF</button>
                    <button class="px-5 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 shadow-md transition">Edit Entry</button>
                </div>
            </div>
        </main>
    </div>
</body>
</html>