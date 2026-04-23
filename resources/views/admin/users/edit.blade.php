@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Edit User</h1>
        <p class="text-sm text-slate-500">{{ $user->email }}</p>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white p-6 rounded-xl border space-y-4">
        @csrf
        @method('PUT')
        @include('admin.users._form')
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold">Save Changes</button>
    </form>

    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="bg-white p-6 rounded-xl border space-y-3">
        @csrf
        <h2 class="font-bold text-slate-700">Reset Password</h2>
        <div class="grid md:grid-cols-2 gap-3">
            <input type="password" name="password" placeholder="New Password" class="border rounded-lg px-3 py-2" required>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" class="border rounded-lg px-3 py-2" required>
        </div>
        <button class="px-4 py-2 bg-amber-500 text-white rounded-lg font-semibold">Reset Password</button>
    </form>
</div>
@endsection
