@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-800">Add User</h1>
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50">
            Back
        </a>
    </div>
    <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="bg-white p-6 rounded-xl border space-y-4">
        @csrf
        @include('admin.users._form')
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold">Create User</button>
    </form>
</div>
@endsection
