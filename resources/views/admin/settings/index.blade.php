@extends('layouts.app')

@section('content')
@php
    $currentLogo = !empty($settings['clinic_logo']) ? asset('storage/' . ltrim((string) $settings['clinic_logo'], '/')) : null;
@endphp
<div class="max-w-5xl mx-auto space-y-5">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Admin</p>
                <h1 class="text-3xl font-black text-slate-800 mt-1">System Settings</h1>
                <p class="text-sm text-slate-500 mt-1">Manage clinic identity and workflow defaults.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                Settings Center
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3">
            <div class="lg:col-span-2 p-6 space-y-4 border-b lg:border-b-0 lg:border-r border-slate-100">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Clinic Name</label>
                    <input
                        name="clinic_name"
                        value="{{ old('clinic_name', $settings['clinic_name'] ?? 'Barangay Clinic') }}"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Address</label>
                    <input
                        name="clinic_address"
                        value="{{ old('clinic_address', $settings['clinic_address'] ?? '') }}"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="Enter clinic address"
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Queue Behavior</label>
                    <input
                        name="queue_behavior"
                        value="{{ old('queue_behavior', $settings['queue_behavior'] ?? 'FIFO') }}"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="FIFO"
                    >
                </div>

                <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <input type="checkbox" name="consultation_requires_doctor" value="1" @checked(old('consultation_requires_doctor', ($settings['consultation_requires_doctor'] ?? '1') === '1'))>
                    <span class="text-sm font-medium text-slate-700">Consultation requires doctor</span>
                </label>
            </div>

            <div class="p-6 space-y-3">
                <p class="text-sm font-bold text-slate-700">Clinic Logo</p>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-center min-h-36">
                    @if($currentLogo)
                        <img src="{{ $currentLogo }}" alt="Clinic logo" class="max-h-24 w-auto object-contain rounded-md">
                    @else
                        <span class="text-xs font-semibold text-slate-400 uppercase">No logo uploaded</span>
                    @endif
                </div>
                <input type="file" name="logo" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                <p class="text-xs text-slate-400">Accepted: JPG, PNG, WEBP (max 4MB)</p>
            </div>
        </div>

        <div class="border-t border-slate-100 px-6 py-4 bg-slate-50 flex items-center justify-end">
            <button class="px-5 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">Save Settings</button>
        </div>
    </form>
</div>
@endsection
