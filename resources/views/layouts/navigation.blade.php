<div class="flex-1 px-4 py-6 space-y-2">
    
    <a href="{{ route('record.create') }}" 
       class="flex items-center gap-3 px-4 py-3 mb-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all shadow-lg font-bold">
        <span class="text-xl">+</span>
        <span>New Entry</span>
    </a>

    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl hover:bg-slate-800">
        Dashboard
    </a>
    
    <a href="{{ route('record.index') }}" class="flex items-center px-4 py-3 rounded-xl hover:bg-slate-800">
        Clinic Records
    </a>
</div>