@extends('layouts.app')

@section('content')
@php
    $role = auth()->user()->role ?? 'bhw';
    $backRoute = $role === 'admin'
        ? route('admin.dashboard')
        : ($role === 'doctor'
            ? route('doctor.dashboard')
            : ($role === 'nurse' ? route('nurse.dashboard') : route('bhw.dashboard')));
@endphp

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">View Profile</h1>
            <p class="text-sm text-slate-500">{{ $user->email }}</p>
        </div>
        <a href="{{ $backRoute }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50">
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="bg-white p-6 rounded-xl border space-y-5">
        @csrf
        <div class="flex items-center gap-3">
            @if(!empty($user->profile_photo_path))
                <img src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="{{ $user->full_name }}" class="w-14 h-14 rounded-full object-cover border border-slate-200">
            @else
                <div class="w-14 h-14 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-lg font-bold">
                    {{ strtoupper(substr((string) ($user->first_name ?? 'U'), 0, 1) . substr((string) ($user->last_name ?? ''), 0, 1)) }}
                </div>
            @endif
            <div>
                <p class="text-sm text-slate-500">Profile Photo</p>
                <p class="text-xs text-slate-400">Configured by Admin User Management</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">First Name</label>
                <input name="first_name" value="{{ old('first_name', $user->first_name) }}" class="w-full border rounded-lg px-3 py-2 bg-white text-slate-700">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Middle Name</label>
                <input name="middle_name" value="{{ old('middle_name', $user->middle_name) }}" class="w-full border rounded-lg px-3 py-2 bg-white text-slate-700">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Last Name</label>
                <input name="last_name" value="{{ old('last_name', $user->last_name) }}" class="w-full border rounded-lg px-3 py-2 bg-white text-slate-700">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Suffix</label>
                <input name="suffix" value="{{ old('suffix', $user->suffix) }}" class="w-full border rounded-lg px-3 py-2 bg-white text-slate-700">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full border rounded-lg px-3 py-2 bg-white text-slate-700">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <input value="{{ strtoupper((string) $user->role) }}" disabled class="w-full border rounded-lg px-3 py-2 bg-slate-50 text-slate-700">
            </div>
            <div class="md:col-span-2 rounded-xl border border-slate-100 p-4 bg-slate-50">
                <label class="block text-sm font-bold text-slate-700 mb-2">Add Profile (Optional)</label>
                <input type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp" class="w-full border rounded-lg px-3 py-2 bg-white">
                <p class="text-xs text-slate-500 mt-1">Accepted: JPG, PNG, WEBP (max 2MB)</p>

                @if(!empty($user->profile_photo_path))
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600 mt-3">
                        <input type="checkbox" name="remove_profile_photo" value="1">
                        Remove current profile photo
                    </label>
                @endif
            </div>
        </div>
        <div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
