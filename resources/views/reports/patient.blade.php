@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-20 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 mt-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Patient Report</h1>
            <p class="text-gray-500 text-sm mt-1">Unique patient list and latest consultation details</p>
        </div>
        <a href="{{ route('reports.patients.export', ['search' => $search, 'age_group' => $ageGroup]) }}"
            class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition">
            Export Excel
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <form method="GET" action="{{ route('reports.patients') }}" class="flex gap-3 flex-wrap">
                <select id="age_group_filter" name="age_group"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium bg-white focus:ring-2 focus:ring-blue-500 outline-none shadow-sm cursor-pointer">
                    <option value="all" {{ $ageGroup === 'all' ? 'selected' : '' }}>All Ages</option>
                    <option value="0-11" {{ $ageGroup === '0-11' ? 'selected' : '' }}>Infants (0-11 months)</option>
                    <option value="12-59" {{ $ageGroup === '12-59' ? 'selected' : '' }}>Children (12-59 months)</option>
                    <option value="senior" {{ $ageGroup === 'senior' ? 'selected' : '' }}>Seniors (60+ years)</option>
                </select>
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Search patient name or address..."
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
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Age / Gender</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Address</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Diagnosis</th>
                </tr>
            </thead>
            <tbody id="patientReportTableBody" class="divide-y divide-gray-50">
                @forelse($patients as $record)
                    @php
                        $birthDate = \Carbon\Carbon::parse($record->birthday);
                        $ageYears = (int) $birthDate->diffInYears(now());
                        $ageMonths = (int) $birthDate->diffInMonths(now());
                    @endphp
                    <tr class="hover:bg-blue-50/30 transition patient-report-row">
                        <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                            {{ \Carbon\Carbon::parse($record->consultation_date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="font-bold text-gray-800 capitalize">{{ $record->first_name }} {{ $record->last_name }}</div>
                            <div class="text-[10px] font-bold text-blue-500 uppercase tracking-tight">
                                DOB: {{ $birthDate->format('M d, Y') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <span class="font-bold text-gray-700">
                                @if($ageMonths < 12)
                                    {{ $ageMonths }} mon
                                @else
                                    {{ $ageYears }} yrs
                                @endif
                            </span> <span class="text-gray-300 mx-1">|</span> {{ $record->gender }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $record->address_purok }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 italic">"{{ \Illuminate\Support\Str::limit($record->diagnosis, 40) }}"</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center text-gray-400 italic">
                            No patient records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="patientReportPagination" class="mt-4"></div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ageFilter = document.getElementById('age_group_filter');
        if (!ageFilter || !ageFilter.form) return;

        ageFilter.addEventListener('change', function () {
            ageFilter.form.submit();
        });

        const rows = Array.from(document.querySelectorAll('#patientReportTableBody .patient-report-row')).map(row => row.outerHTML);
        renderPaginationTable({
            pagerSelector: '#patientReportPagination',
            tableBodySelector: '#patientReportTableBody',
            rows: rows,
            emptyRowHtml: '<tr><td colspan="5" class="px-6 py-16 text-center text-gray-400 italic">No patient records found.</td></tr>'
        });
    });
</script>
@endpush

