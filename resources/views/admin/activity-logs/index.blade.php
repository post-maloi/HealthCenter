@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <h1 class="text-2xl font-bold text-slate-800">Activity Logs</h1>
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Action</th>
                    <th class="px-4 py-3 text-left">Details</th>
                    <th class="px-4 py-3 text-left">Timestamp</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $log->user?->full_name ?? 'System' }}</td>
                    <td class="px-4 py-3">{{ $log->action }}</td>
                    <td class="px-4 py-3">{{ $log->description }}</td>
                    <td class="px-4 py-3">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No logs available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $logs->links() }}</div>
</div>
@endsection
