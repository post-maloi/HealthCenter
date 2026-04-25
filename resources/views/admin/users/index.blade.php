@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">User Management</h1>
            <p class="text-sm text-slate-500">Admin-only account access control center.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">+ Add User</a>
    </div>

    <form method="GET" class="max-w-md">
        <input name="search" value="{{ $search }}" placeholder="Search users..." class="w-full px-4 py-2 border rounded-lg">
    </form>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="border-t">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if(!empty($user->profile_photo_path))
                                <img src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="{{ $user->full_name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                            @else
                                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-bold">
                                    {{ strtoupper(substr((string) ($user->first_name ?? 'U'), 0, 1) . substr((string) ($user->last_name ?? ''), 0, 1)) }}
                                </div>
                            @endif
                            <span class="font-medium">{{ $user->full_name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">{{ $user->email }}</td>
                    <td class="px-4 py-3 uppercase">{{ $user->role }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="px-3 py-1.5 bg-slate-100 rounded">Edit</a>
                            <form method="POST" action="{{ route('admin.users.status', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button class="px-3 py-1.5 {{ $user->is_active ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }} rounded">
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $users->links() }}</div>
</div>
@endsection
