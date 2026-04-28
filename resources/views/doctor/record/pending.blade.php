@extends('layouts.app')

@section('content')
@php
    $roleNormalized = strtolower(trim((string) (auth()->user()->role ?? 'doctor')));
    $routePrefix = $roleNormalized === 'nurse' ? 'nurse' : 'doctor';
@endphp
<style>
    .triage-row {
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        border-left-width: 4px;
    }
    .triage-row:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
    }
    .priority-dot {
        width: 14px;
        height: 14px;
        border-radius: 9999px;
        position: relative;
    }
    .priority-dot::after {
        content: '';
        position: absolute;
        inset: -5px;
        border-radius: 9999px;
        opacity: 0.35;
        animation: pulse 2s infinite;
    }
    .urgency-critical .priority-dot {
        background: #ef4444;
    }
    .urgency-critical .priority-dot::after {
        background: #ef4444;
    }
    .urgency-high .priority-dot {
        background: #f59e0b;
    }
    .urgency-high .priority-dot::after {
        background: #f59e0b;
    }
    .urgency-watch .priority-dot {
        background: #22c55e;
    }
    .urgency-watch .priority-dot::after {
        background: #22c55e;
    }
    .waiting-number {
        min-width: 72px;
    }
    .smart-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border-radius: 8px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        border: 1px solid #dbe1ea;
        background: #f1f5f9;
        color: #334155;
    }
    .smart-badge.abnormal-critical {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #b91c1c;
    }
    .smart-badge.abnormal-warning {
        background: #fef3c7;
        border-color: #fcd34d;
        color: #a16207;
    }
    .triage-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }
    .trend-up {
        color: #ef4444;
    }
    .trend-mid {
        color: #f59e0b;
    }
    .trend-low {
        color: #22c55e;
    }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 0.35; }
        70% { transform: scale(1.35); opacity: 0; }
        100% { transform: scale(1.35); opacity: 0; }
    }
</style>

<div class="max-w-7xl mx-auto pb-20 px-4 sm:px-6 lg:px-8 mt-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-4xl font-black text-slate-800">Automated Clinical Triage Dashboard</h1>
            <p class="text-slate-500 text-sm mt-1">Priority-based list of patients waiting for consultation. Critical vitals highlighted automatically.</p>
        </div>
        <div class="w-full md:w-auto flex gap-2">
            <input type="text" id="searchInput" placeholder="Search pending patients..." class="pl-4 pr-4 py-2.5 w-full md:w-80 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
            <button type="button" class="w-11 h-11 rounded-xl border border-gray-200 bg-white text-slate-500 inline-flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h18l-7 8v5l-4 2v-7L3 5z"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 lg:grid-cols-3 gap-3">
        <div class="rounded-2xl border border-red-200 bg-red-50/80 p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                <h3 class="text-xs font-black uppercase tracking-wider text-red-700">Immediate Attention</h3>
            </div>
            <p class="text-xs text-red-700 leading-relaxed">
                Triggered by critical vitals (e.g., Temp &lt; 35&deg;C or &gt; 39&deg;C).
            </p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <h3 class="text-xs font-black uppercase tracking-wider text-amber-700">Moderate Priority</h3>
            </div>
            <p class="text-xs text-amber-700 leading-relaxed">
                Triggered by borderline vitals, such as mild fever or elevated blood pressure.
            </p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h3 class="text-xs font-black uppercase tracking-wider text-emerald-700">Low Priority</h3>
            </div>
            <p class="text-xs text-emerald-700 leading-relaxed">
                Stable patients with normal vitals (e.g., around 120/80 BP and 36.5&deg;C Temp).
            </p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-slate-100/80 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-slate-500 uppercase">Prioritization</th>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-slate-500 uppercase">Patient Identity</th>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-slate-500 uppercase">Age / Gender</th>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-slate-500 uppercase">Address</th>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-slate-500 uppercase">Smart Vitals</th>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-slate-500 uppercase">Trend</th>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-slate-500 uppercase">Triage Status</th>
                    <th class="px-4 py-3 text-right text-[10px] font-black text-slate-500 uppercase">Quick Actions</th>
                </tr>
            </thead>
            <tbody id="recordsTableBody" class="divide-y divide-slate-200">
                @forelse($records as $record)
                    @php
                        $birthDate = \Carbon\Carbon::parse($record->birthday);
                        $ageYears = (int) $birthDate->diffInYears(now());
                        $ageMonths = (int) $birthDate->diffInMonths(now());
                        $queueAt = \Carbon\Carbon::parse($record->consultation_date);
                    @endphp
                    <tr
                        class="group triage-row patient-row border-l-slate-300"
                        data-name="{{ strtolower(trim($record->first_name . ' ' . $record->last_name)) }}"
                        data-queue-ts="{{ $queueAt->timestamp }}"
                        data-queue-date="{{ $queueAt->format('M d, Y') }}"
                        data-age-years="{{ $ageYears }}"
                        data-age-months="{{ $ageMonths }}"
                        data-temp="{{ (string) ($record->display_temp ?? '') }}"
                        data-bp="{{ (string) ($record->display_bp ?? '') }}"
                        data-weight="{{ (string) ($record->display_weight ?? '') }}"
                    >
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 urgency-watch">
                                <span class="priority-dot"></span>
                                <span class="text-xs font-semibold text-slate-500 priority-rank">--</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-bold text-gray-800 capitalize patient-name">{{ $record->first_name }} {{ $record->last_name }}</div>
                            <div class="text-[10px] font-bold text-blue-500 uppercase tracking-tight">
                                DOB: {{ $birthDate->format('M d, Y') }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            <span class="font-bold text-gray-700">
                                @if($ageMonths < 12)
                                    {{ $ageMonths }} mon
                                @else
                                    {{ $ageYears }} yrs
                                @endif
                            </span>
                            <span class="text-gray-300 mx-1">|</span> {{ $record->gender }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $record->address_purok }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            <div class="flex flex-wrap gap-2">
                                <span class="smart-badge vital-bp">BP: {{ $record->display_bp ?: '--' }}</span>
                                <span class="smart-badge vital-temp">T: {{ $record->display_temp ?: '--' }}</span>
                                <span class="smart-badge vital-wt">WT: {{ $record->display_weight ?: '--' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="trend-indicator inline-flex items-center gap-1 text-xs font-bold trend-low">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17l6-6 4 4 7-7"/>
                                </svg>
                                Stable
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <span class="triage-pill bg-green-100 text-green-700 triage-status">Low Priority</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="action-hub flex justify-end gap-2">
                                <a href="{{ route($routePrefix . '.record.create', ['patient_record_id' => $record->id]) }}"
                                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm"
                                    title="Add new consultation for this patient">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                <a href="{{ route($routePrefix . '.record.show', $record->id) }}"
                                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-800 hover:text-white transition-all shadow-sm"
                                    title="View patient history">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center text-gray-400 italic">No pending patients in queue.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="recordsPagination" class="mt-4"></div>
</div>

<script>
const WAIT_TARGET_MINUTES = 180;

function numberFromText(value) {
    const match = String(value ?? '').match(/(\d+(\.\d+)?)/);
    return match ? parseFloat(match[1]) : null;
}

function parseBloodPressure(bpText) {
    const match = String(bpText ?? '').match(/(\d+)\s*\/\s*(\d+)/);
    if (!match) {
        return { systolic: null, diastolic: null };
    }
    return {
        systolic: parseInt(match[1], 10),
        diastolic: parseInt(match[2], 10),
    };
}

function getTempRange(ageMonths) {
    if (ageMonths < 12) {
        return { min: 36.2, max: 37.7 };
    }
    return { min: 36.0, max: 37.6 };
}

function getBpRange(ageYears, ageMonths) {
    if (ageMonths < 1) return { sysMin: 60, sysMax: 90, diaMin: 30, diaMax: 60 };
    if (ageMonths < 12) return { sysMin: 70, sysMax: 100, diaMin: 35, diaMax: 65 };
    if (ageYears <= 3) return { sysMin: 80, sysMax: 110, diaMin: 45, diaMax: 75 };
    if (ageYears <= 5) return { sysMin: 80, sysMax: 112, diaMin: 50, diaMax: 80 };
    if (ageYears <= 12) return { sysMin: 85, sysMax: 120, diaMin: 55, diaMax: 80 };
    return { sysMin: 90, sysMax: 140, diaMin: 60, diaMax: 90 };
}

function getWeightRange(ageYears, ageMonths) {
    if (ageMonths < 12) return { min: 3, max: 12 };
    if (ageYears <= 3) return { min: 8, max: 18 };
    if (ageYears <= 5) return { min: 12, max: 24 };
    if (ageYears <= 12) return { min: 18, max: 55 };
    return { min: 40, max: 110 };
}

function getVitalFlags(row) {
    const ageYears = parseInt(row.dataset.ageYears || '0', 10);
    const ageMonths = parseInt(row.dataset.ageMonths || '0', 10);
    const temp = numberFromText(row.dataset.temp);
    const weight = numberFromText(row.dataset.weight);
    const bp = parseBloodPressure(row.dataset.bp);

    const tempRange = getTempRange(ageMonths);
    const bpRange = getBpRange(ageYears, ageMonths);
    const wtRange = getWeightRange(ageYears, ageMonths);

    const tempCritical = temp !== null && (temp < 35 || temp > 39);
    const tempWarning = temp !== null && !tempCritical && (temp < tempRange.min || temp > tempRange.max);

    const bpCritical = bp.systolic !== null && bp.diastolic !== null && (
        bp.systolic >= 160 || bp.diastolic >= 100 || bp.systolic < 80 || bp.diastolic < 50
    );
    const bpWarning = bp.systolic !== null && bp.diastolic !== null && !bpCritical && (
        bp.systolic < bpRange.sysMin ||
        bp.systolic > bpRange.sysMax ||
        bp.diastolic < bpRange.diaMin ||
        bp.diastolic > bpRange.diaMax
    );
    const wtCritical = weight !== null && (weight < wtRange.min * 0.75 || weight > wtRange.max * 1.3);
    const wtWarning = weight !== null && !wtCritical && (weight < wtRange.min || weight > wtRange.max);

    return {
        tempCritical,
        tempWarning,
        bpCritical,
        bpWarning,
        wtCritical,
        wtWarning,
    };
}

function classifyUrgency(waitMinutes, criticalCount, warningCount) {
    if (criticalCount > 0) {
        return {
            key: 'critical',
            badgeClass: 'urgency-critical border-red-400 bg-red-50',
            statusClass: 'bg-red-100 text-red-700',
            statusText: 'Immediate Attention',
            trendClass: 'trend-up',
            trendText: 'Escalating',
            trendIcon: 'up',
        };
    }
    if (warningCount > 0) {
        return {
            key: 'high',
            badgeClass: 'urgency-high border-amber-400 bg-amber-50',
            statusClass: 'bg-amber-100 text-amber-700',
            statusText: 'Moderate Priority',
            trendClass: 'trend-mid',
            trendText: 'Watch Closely',
            trendIcon: 'mid',
        };
    }
    return {
        key: 'watch',
        badgeClass: 'urgency-watch border-emerald-400 bg-slate-50',
        statusClass: 'bg-green-100 text-green-700',
        statusText: 'Low Priority',
        trendClass: 'trend-low',
        trendText: 'Stable',
        trendIcon: 'low',
    };
}

function getTrendIcon(type) {
    if (type === 'up') {
        return '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>';
    }
    if (type === 'mid') {
        return '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"/></svg>';
    }
    return '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17l6-6 4 4 7-7"/></svg>';
}

function hydrateTriageRow(row) {
    const queueTs = parseInt(row.dataset.queueTs || '0', 10) * 1000;
    const now = Date.now();
    const waitMinutes = Math.max(0, Math.floor((now - queueTs) / 60000));
    const flags = getVitalFlags(row);
    const criticalCount = [flags.tempCritical, flags.bpCritical, flags.wtCritical].filter(Boolean).length;
    const warningCount = [flags.tempWarning, flags.bpWarning, flags.wtWarning].filter(Boolean).length;
    const urgency = classifyUrgency(waitMinutes, criticalCount, warningCount);
    const riskScore = (urgency.key === 'critical' ? 300 : urgency.key === 'high' ? 200 : 100) + (criticalCount * 35) + (warningCount * 20) + Math.min(waitMinutes, WAIT_TARGET_MINUTES);

    row.dataset.waitMinutes = String(waitMinutes);
    row.dataset.riskScore = String(riskScore);

    const tempBadge = row.querySelector('.vital-temp');
    const bpBadge = row.querySelector('.vital-bp');
    const wtBadge = row.querySelector('.vital-wt');
    if (tempBadge) {
        tempBadge.className = `smart-badge vital-temp ${flags.tempCritical ? 'abnormal-critical' : (flags.tempWarning ? 'abnormal-warning' : '')}`;
    }
    if (bpBadge) {
        bpBadge.className = `smart-badge vital-bp ${flags.bpCritical ? 'abnormal-critical' : (flags.bpWarning ? 'abnormal-warning' : '')}`;
    }
    if (wtBadge) {
        wtBadge.className = `smart-badge vital-wt ${flags.wtCritical ? 'abnormal-critical' : (flags.wtWarning ? 'abnormal-warning' : '')}`;
    }

    row.className = row.className.replace(/urgency-(critical|high|watch)/g, '').trim();
    row.classList.add(...urgency.badgeClass.split(' '));

    const minutesNode = row.querySelector('.wait-minutes');
    if (minutesNode) {
        minutesNode.textContent = String(waitMinutes);
    }

    const trendNode = row.querySelector('.trend-indicator');
    if (trendNode) {
        trendNode.className = `trend-indicator inline-flex items-center gap-1 text-xs font-bold ${urgency.trendClass}`;
        trendNode.innerHTML = `${getTrendIcon(urgency.trendIcon)}${urgency.trendText}`;
    }

    const triageNode = row.querySelector('.triage-status');
    if (triageNode) {
        triageNode.className = `triage-pill triage-status ${urgency.statusClass}`;
        triageNode.textContent = urgency.statusText;
    }
}

const triageRows = Array.from(document.querySelectorAll('#recordsTableBody .patient-row')).map((row) => {
    hydrateTriageRow(row);
    return {
        html: row.outerHTML,
        queueTs: parseInt(row.dataset.queueTs || '0', 10),
        name: (row.dataset.name || '').toLowerCase(),
        riskScore: parseInt(row.dataset.riskScore || '0', 10),
        waitMinutes: parseInt(row.dataset.waitMinutes || '0', 10),
    };
});

function renderPendingPagination(filteredRows) {
    if (filteredRows.length === 0) {
        renderPaginationTable({
            pagerSelector: '#recordsPagination',
            tableBodySelector: '#recordsTableBody',
            rows: [],
            emptyRowHtml: '<tr><td colspan="9" class="px-6 py-16 text-center text-gray-400 italic">No pending patients in queue.</td></tr>'
        });
        return;
    }

    renderPaginationTable({
        pagerSelector: '#recordsPagination',
        tableBodySelector: '#recordsTableBody',
        rows: filteredRows.map(item => item.html),
        emptyRowHtml: '<tr><td colspan="9" class="px-6 py-16 text-center text-gray-400 italic">No pending patients in queue.</td></tr>'
    });
}

function applyPendingSearch() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const sortedRows = [...triageRows].sort((a, b) => {
        if (b.riskScore !== a.riskScore) return b.riskScore - a.riskScore;
        if (b.waitMinutes !== a.waitMinutes) return b.waitMinutes - a.waitMinutes;
        return a.queueTs - b.queueTs;
    }).map((row, index) => ({
        ...row,
        html: row.html.replace(
            /<span class="text-xs font-semibold text-slate-500 priority-rank">--<\/span>/,
            `<span class="text-xs font-semibold text-slate-500 priority-rank">#${index + 1}</span>`
        ),
    }));

    const filtered = sortedRows
        .filter((row) => row.name.includes(searchTerm))
        .map((row, index) => ({
            ...row,
            html: row.html.replace(
                /<span class="text-xs font-semibold text-slate-500 priority-rank">#\d+<\/span>/,
                `<span class="text-xs font-semibold text-slate-500 priority-rank">#${index + 1}</span>`
            ),
        }));

    renderPendingPagination(filtered);
}

document.getElementById('searchInput').addEventListener('keyup', applyPendingSearch);
document.addEventListener('DOMContentLoaded', function () {
    applyPendingSearch();
});
</script>
@endsection
