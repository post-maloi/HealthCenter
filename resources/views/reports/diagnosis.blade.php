@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-20 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 mt-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Diagnosis Report</h1>
            <p class="text-gray-500 text-sm mt-1">All consultation diagnosis records</p>
        </div>
        <a href="{{ route('reports.diagnosis.export', ['search' => $search]) }}"
            class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition">
            Export Excel
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <form method="GET" action="{{ route('reports.diagnosis') }}" class="flex gap-3">
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Search diagnosis or patient..."
                    class="w-full md:w-96 px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <button type="submit"
                    class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition">
                    Search
                </button>
            </form>
        </div>

        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Date</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Patient Name</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Diagnosis</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Vital Signs</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Address</th>
                </tr>
            </thead>
            <tbody id="diagnosisReportTableBody" class="divide-y divide-gray-50">
                @forelse($diagnosisReports as $record)
                    <tr class="hover:bg-blue-50/30 transition diagnosis-report-row">
                        <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                            {{ \Carbon\Carbon::parse($record->consultation_date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="font-bold text-gray-800 capitalize">{{ $record->first_name }} {{ $record->last_name }}</div>
                            <div class="text-[10px] font-bold text-blue-500 uppercase tracking-tight">
                                Age: {{ $record->age ?: '--' }} / {{ $record->gender ?: '--' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 italic">"{{ \Illuminate\Support\Str::limit($record->diagnosis, 70) }}"</td>
                        <td class="px-6 py-4 text-xs text-gray-600">
                            T: {{ $record->temp ?: '--' }},
                            BP: {{ $record->bp ?: '--' }},
                            PR: {{ $record->pr ?: '--' }},
                            RR: {{ $record->rr ?: '--' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $record->address_purok }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center text-gray-400 italic">
                            No diagnosis records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="diagnosisReportPagination" class="mt-4"></div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rows = Array.from(document.querySelectorAll('#diagnosisReportTableBody .diagnosis-report-row')).map(row => row.outerHTML);
        renderPaginationTable({
            pagerSelector: '#diagnosisReportPagination',
            tableBodySelector: '#diagnosisReportTableBody',
            rows: rows,
            emptyRowHtml: '<tr><td colspan="5" class="px-6 py-16 text-center text-gray-400 italic">No diagnosis records found.</td></tr>'
        });
    });
</script>
@endpush

