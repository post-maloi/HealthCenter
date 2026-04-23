@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-800 mb-4">System Settings</h1>
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="bg-white p-6 rounded-xl border space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Clinic Name</label>
            <input name="clinic_name" value="{{ old('clinic_name', $settings['clinic_name'] ?? 'Barangay Clinic') }}" class="w-full border rounded-lg px-3 py-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Address</label>
            <input name="clinic_address" value="{{ old('clinic_address', $settings['clinic_address'] ?? '') }}" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Queue Behavior</label>
            <input name="queue_behavior" value="{{ old('queue_behavior', $settings['queue_behavior'] ?? 'FIFO') }}" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Clinic Logo</label>
            <input type="file" name="logo" class="w-full border rounded-lg px-3 py-2">
        </div>
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="consultation_requires_doctor" value="1" @checked(old('consultation_requires_doctor', ($settings['consultation_requires_doctor'] ?? '1') === '1'))>
            <span class="text-sm">Consultation requires doctor</span>
        </label>
        <div>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold">Save Settings</button>
        </div>
    </form>
</div>
@endsection
