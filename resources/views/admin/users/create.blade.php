@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-800 mb-4">Add User</h1>
    <form method="POST" action="{{ route('admin.users.store') }}" class="bg-white p-6 rounded-xl border space-y-4">
        @csrf
        @include('admin.users._form')
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold">Create User</button>
    </form>
</div>
@endsection
