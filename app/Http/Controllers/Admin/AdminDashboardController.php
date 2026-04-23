<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ClinicRecord;
use App\Models\Medicine;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    private function normalizedIssueSignature(ClinicRecord $record): string
    {
        $diagnosis = mb_strtolower(trim((string) $record->diagnosis));
        $subjective = mb_strtolower(trim((string) $record->subjective));
        $subjective = preg_replace('/\s+/', ' ', $subjective);

        return trim($diagnosis . '|' . $subjective);
    }

    private function buildRecoveryAnalytics(): array
    {
        $records = ClinicRecord::query()
            ->whereDate('consultation_date', '>=', now()->subDays(120))
            ->orderBy('consultation_date')
            ->orderBy('id')
            ->get();

        $grouped = $records->groupBy(function (ClinicRecord $record) {
            return mb_strtolower(trim(implode('|', [
                (string) $record->first_name,
                (string) $record->last_name,
                (string) $record->birthday,
            ])));
        });

        $recoveredPatients = 0;
        $repeatedSymptomsPatients = 0;
        $unresolvedConsultations = 0;

        foreach ($grouped as $patientRecords) {
            $latest = $patientRecords->last();
            $latestStatus = trim((string) $latest?->condition_update);

            if ($latestStatus === 'recovered') {
                $recoveredPatients++;
            }

            if (in_array($latestStatus, ['no_improvement', 'worsened'], true)) {
                $unresolvedConsultations++;
            }

            $lastThree = $patientRecords->slice(-3)->values();
            if ($lastThree->count() >= 3) {
                $sameIssue = $lastThree
                    ->map(fn (ClinicRecord $record) => $this->normalizedIssueSignature($record))
                    ->filter()
                    ->unique()
                    ->count() === 1;

                if ($sameIssue) {
                    $repeatedSymptomsPatients++;
                }
            }
        }

        return [
            'recovered_patients' => $recoveredPatients,
            'repeated_symptoms_patients' => $repeatedSymptomsPatients,
            'unresolved_consultation_count' => $unresolvedConsultations,
        ];
    }

    public function index(): View
    {
        $totalPatients = ClinicRecord::query()
            ->select('first_name', 'last_name', 'birthday')
            ->groupBy('first_name', 'last_name', 'birthday')
            ->get()
            ->count();

        $todaysPatients = ClinicRecord::query()->whereDate('consultation_date', today())->count();
        $totalConsultations = ClinicRecord::count();
        $pendingConsultations = ClinicRecord::all()->where('workflow_status', 'waiting_for_doctor')->count();
        $lowStockMedicines = Medicine::query()->where('stock', '<', 10)->orderBy('stock')->limit(8)->get();
        $recentLogs = ActivityLog::with('user')->latest()->limit(200)->get();

        $weeklyPatients = ClinicRecord::query()
            ->whereDate('consultation_date', '>=', now()->subDays(6))
            ->selectRaw('DATE(consultation_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();
        $recoveryAnalytics = $this->buildRecoveryAnalytics();

        return view('admin.dashboard', compact(
            'totalPatients',
            'todaysPatients',
            'totalConsultations',
            'pendingConsultations',
            'lowStockMedicines',
            'recentLogs',
            'weeklyPatients',
            'recoveryAnalytics'
        ));
    }
}
