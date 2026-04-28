<?php

namespace App\Http\Controllers;

use App\Models\ClinicRecord;
use App\Models\ClinicRecordFile;
use App\Models\InventoryLog;
use App\Models\Medicine;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DoctorClinicRecordController extends Controller
{
    private const DOCTOR_PLACEHOLDER_DIAGNOSIS = 'waiting_for_doctor/nurse';
    private const NURSE_PLACEHOLDER_DIAGNOSIS = 'For doctor assessment';

    private function currentRole(): string
    {
        return strtolower(trim((string) (Auth::user()->role ?? 'doctor')));
    }

    private function normalizeMedicineName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));
        return mb_strtolower($name);
    }

    private function getDispensableMedicinesForSelection()
    {
        $today = Carbon::today();

        return Medicine::where('stock', '>', 0)
            ->where(function ($query) use ($today) {
                $query->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', $today);
            })
            ->orderByRaw('CASE WHEN expiration_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiration_date')
            ->orderBy('arrival_date')
            ->get()
            ->unique(fn ($item) => $this->normalizeMedicineName((string) $item->name))
            ->values();
    }

    private function formatAgeFromBirthday(string $birthday): string
    {
        $birth = Carbon::parse($birthday);
        $diff = $birth->diff(Carbon::now());

        return ($diff->y === 0) ? $diff->m . ' Mon' : $diff->y . ' yrs';
    }

    private function hasAnyVital(ClinicRecord $record): bool
    {
        foreach (['temp', 'bp', 'pr', 'rr', 'weight', 'height', 'bmi'] as $field) {
            $value = $record->{$field};
            if (!is_null($value) && trim((string) $value) !== '' && strtoupper(trim((string) $value)) !== 'N/A') {
                return true;
            }
        }

        return false;
    }

    private function attachDisplayVitals(Collection $records): Collection
    {
        return $records->map(function (ClinicRecord $record) use ($records) {
            $fallback = $records->first(fn (ClinicRecord $item) => $this->hasAnyVital($item));
            foreach (['temp', 'bp', 'pr', 'rr', 'weight', 'height', 'bmi'] as $field) {
                $current = $record->{$field};
                $record->{"display_{$field}"} = (!is_null($current) && trim((string) $current) !== '' && strtoupper(trim((string) $current)) !== 'N/A')
                    ? $current
                    : ($fallback?->{$field} ?? null);
            }

            return $record;
        });
    }

    private function assignConsultant(array &$members, ?string $rawName, string $defaultRole = 'bhw'): void
    {
        $name = trim((string) $rawName);
        if ($name === '') {
            return;
        }

        $normalized = strtolower($name);
        $role = $defaultRole;
        if (str_starts_with($normalized, 'dr. ') || str_starts_with($normalized, 'dr ')) {
            $role = 'doctor';
            $name = trim(preg_replace('/^dr\.?\s+/i', '', $name) ?? $name);
        } elseif (str_starts_with($normalized, 'nurse ')) {
            $role = 'nurse';
            $name = trim(preg_replace('/^nurse\s+/i', '', $name) ?? $name);
        } elseif (str_starts_with($normalized, 'bhw ')) {
            $role = 'bhw';
            $name = trim(preg_replace('/^bhw\s+/i', '', $name) ?? $name);
        }

        if (!empty($members[$role])) {
            return;
        }

        $members[$role] = match ($role) {
            'doctor' => 'Dr. ' . $name,
            'nurse' => 'Nurse ' . $name,
            default => 'BHW ' . $name,
        };
    }

    private function buildConsultationTeam(Collection $records): array
    {
        $members = [
            'bhw' => null,
            'nurse' => null,
            'doctor' => null,
        ];

        foreach ($records->sortBy('id') as $record) {
            $this->assignConsultant($members, $record->consulted_by, 'bhw');
            $this->assignConsultant($members, $record->doctor_consulted_by, 'doctor');
        }

        return array_values(array_filter([
            $members['bhw'],
            $members['nurse'],
            $members['doctor'],
        ]));
    }

    private function hasResolvedDiagnosis(?string $diagnosis): bool
    {
        $value = trim((string) $diagnosis);
        if ($value === '') {
            return false;
        }

        return !in_array($value, [self::DOCTOR_PLACEHOLDER_DIAGNOSIS, self::NURSE_PLACEHOLDER_DIAGNOSIS], true);
    }

    private function isDoctorFinalizedRecord(ClinicRecord $record): bool
    {
        if (!$this->hasResolvedDiagnosis($record->diagnosis)) {
            return false;
        }

        return str_starts_with(strtolower(trim((string) $record->doctor_consulted_by)), 'dr.');
    }

    private function isPendingDiagnosis(?string $diagnosis): bool
    {
        $value = trim((string) $diagnosis);
        if ($value === '') {
            return false;
        }

        return in_array($value, [self::DOCTOR_PLACEHOLDER_DIAGNOSIS, self::NURSE_PLACEHOLDER_DIAGNOSIS], true);
    }

    private function normalizedIssueSignature(ClinicRecord $record): string
    {
        $diagnosis = mb_strtolower(trim((string) $record->diagnosis));
        $subjective = mb_strtolower(trim((string) $record->subjective));
        $subjective = preg_replace('/\s+/', ' ', $subjective);
        return trim($diagnosis . '|' . $subjective);
    }

    private function recommendationForStatus(string $status): string
    {
        return match ($status) {
            'recovered' => 'Continue home care and routine preventive follow-up.',
            'improving' => 'Continue current treatment and monitor progress on next visit.',
            'no_improvement' => 'Recommend further laboratory testing.',
            'worsened' => 'Patient may require specialist referral.',
            default => 'Schedule follow-up consultation and monitor symptoms closely.',
        };
    }

    private function buildRecoveryMonitoring(): array
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

        $statusRows = collect();
        $trendCounts = [
            'recovered' => 0,
            'improving' => 0,
            'no_improvement' => 0,
            'worsened' => 0,
            'monitoring' => 0,
        ];
        $alerts = [];

        foreach ($grouped as $patientRecords) {
            $resolvedVisits = $patientRecords
                ->filter(fn (ClinicRecord $record) => $this->hasResolvedDiagnosis($record->diagnosis))
                ->values();

            if ($resolvedVisits->isEmpty()) {
                continue;
            }

            $latest = $resolvedVisits->last();
            $latestDiagnosis = trim((string) $latest->diagnosis);
            $resolvedCount = $resolvedVisits->count();
            $selectedStatus = trim((string) $latest->condition_update);

            $status = in_array($selectedStatus, ['recovered', 'improving', 'no_improvement', 'worsened'], true)
                ? $selectedStatus
                : 'monitoring';

            $message = match ($status) {
                'recovered' => "Recovered after {$resolvedCount} follow-up visit(s).",
                'improving' => "Improving after {$resolvedCount} consultation(s).",
                'worsened' => 'Condition worsened. Immediate follow-up recommended.',
                'no_improvement' => "No improvement after {$resolvedCount} consultation(s).",
                default => "Monitoring ({$resolvedCount} consultation(s))",
            };

            if (in_array($status, ['monitoring', 'improving'], true) && $resolvedCount >= 3) {
                $lastThreeSignatures = $resolvedVisits
                    ->slice(-3)
                    ->map(fn (ClinicRecord $record) => $this->normalizedIssueSignature($record))
                    ->filter()
                    ->unique();

                if ($lastThreeSignatures->count() === 1) {
                    $status = 'no_improvement';
                    $message = 'No improvement after 3 consultations.';
                }
            }

            $statusRows->push([
                'record_id' => $latest->id,
                'patient_name' => trim(implode(' ', array_filter([
                    $latest->first_name,
                    $latest->middle_name,
                    $latest->last_name,
                ]))),
                'status' => $status,
                'message' => $message,
                'diagnosis' => $latestDiagnosis,
                'consultation_date' => $latest->consultation_date,
                'follow_up_recommendation' => $latest->follow_up_recommendation ?: $this->recommendationForStatus($status),
            ]);
            $trendCounts[$status] = ($trendCounts[$status] ?? 0) + 1;

            if ($status === 'no_improvement') {
                $alerts[] = "⚠ {$latest->first_name} {$latest->last_name} has 3 consultations with no improvement.";
            } elseif ($status === 'recovered') {
                $alerts[] = "✅ {$latest->first_name} {$latest->last_name} recovered after {$resolvedCount} follow-up visit(s).";
            }
        }

        $priority = ['no_improvement' => 0, 'monitoring' => 1, 'recovered' => 2];
        $rows = $statusRows
            ->sortBy([
                fn (array $row) => $priority[$row['status']] ?? 9,
                fn (array $row) => $row['consultation_date']?->timestamp ? -$row['consultation_date']->timestamp : 0,
            ])
            ->values();

        return [
            'summary' => [
                'recovered' => $rows->where('status', 'recovered')->count(),
                'no_improvement' => $rows->where('status', 'no_improvement')->count(),
                'improving' => $rows->where('status', 'improving')->count(),
                'worsened' => $rows->where('status', 'worsened')->count(),
                'monitoring' => $rows->where('status', 'monitoring')->count(),
                'follow_up' => $rows->whereIn('status', ['monitoring', 'no_improvement', 'worsened'])->count(),
            ],
            'rows' => $rows->take(6),
            'trend' => $trendCounts,
            'alerts' => collect($alerts)->take(4)->values(),
        ];
    }

    public function dashboard()
    {
        $totalPatients = ClinicRecord::select('first_name', 'last_name', 'birthday')
            ->groupBy('first_name', 'last_name', 'birthday')
            ->get()
            ->count();

        $todayConsultations = ClinicRecord::whereDate('consultation_date', today())->count();
        $lowStockCount = Medicine::where('stock', '<', 10)->count();

        $recentRecords = ClinicRecord::query()
            ->orderBy('consultation_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->unique(fn ($item) => $item->first_name . $item->last_name . $item->birthday)
            ->take(5);
        $recoveryMonitoring = $this->buildRecoveryMonitoring();

        return view('doctor.dashboard', [
            'totalPatients'      => $totalPatients,
            'todayConsultations' => $todayConsultations,
            'lowStockCount'      => $lowStockCount,
            'recentRecords'      => $recentRecords,
            'recoveryMonitoring' => $recoveryMonitoring,
            'isDoctorAvailable'  => (bool) (Auth::user()?->is_doctor_available),
        ]);
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $allMedicines = $this->getDispensableMedicinesForSelection();

        $records = ClinicRecord::whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('clinic_records')
                ->groupBy('first_name', 'last_name', 'birthday');
        })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('consultation_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $records = $this->attachDisplayVitals($records);

        return view('doctor.record.index', [
            'records' => $records,
            'allMedicines' => $allMedicines,
        ]);
    }

    public function pendingPatients(Request $request)
    {
        $search = $request->get('search');

        $records = ClinicRecord::whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('clinic_records')
                ->groupBy('first_name', 'last_name', 'birthday');
        })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%");
                });
            })
            ->get()
            ->filter(fn (ClinicRecord $record) => $this->isPendingDiagnosis($record->diagnosis))
            ->sortBy([
                ['consultation_date', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $records = $this->attachDisplayVitals($records);

        return view('doctor.record.pending', [
            'records' => $records,
        ]);
    }

    public function create(Request $request)
    {
        $routePrefix = $this->currentRole() === 'nurse' ? 'nurse' : 'doctor';
        $allMedicines = $this->getDispensableMedicinesForSelection();
        $patientRecordId = $request->query('patient_record_id');
        if (!$patientRecordId) {
            return redirect()->route($routePrefix . '.record.index')->with('success', 'Select a patient first to add a new consultation.');
        }

        $patient = ClinicRecord::findOrFail($patientRecordId);

        $latest = ClinicRecord::where('first_name', $patient->first_name)
            ->where('last_name', $patient->last_name)
            ->where('birthday', $patient->birthday)
            ->orderBy('consultation_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return view('doctor.record.create', [
            'allMedicines' => $allMedicines,
            'patient' => $patient,
            'latest' => $latest,
        ]);
    }

    public function patientInfo($id)
    {
        $record = ClinicRecord::findOrFail($id);

        return response()->json([
            'id' => $record->id,
            'first_name' => $record->first_name,
            'middle_name' => $record->middle_name,
            'last_name' => $record->last_name,
            'birthday' => optional($record->birthday)->format('Y-m-d'),
            'age' => $record->age ?: $this->formatAgeFromBirthday((string) $record->birthday),
            'gender' => $record->gender,
            'civil_status' => $record->civil_status,
            'contact_number' => $record->contact_number,
            'address_purok' => $record->address_purok,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $isNurse = $this->currentRole() === 'nurse';

        $rules = [
            'patient_record_id' => 'required|exists:clinic_records,id',
            'consultation_date' => 'required|date',
            'laboratory_images'   => 'nullable|array|max:5',
            'laboratory_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ];

        if ($isNurse) {
            $rules['subjective'] = 'nullable|string';
            $rules['objective'] = 'nullable|string';
        } else {
            $rules['diagnosis'] = 'required|string';
            $rules['follow_up_recommendation'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        $patient = ClinicRecord::findOrFail($validated['patient_record_id']);
        $latest = ClinicRecord::where('first_name', $patient->first_name)
            ->where('last_name', $patient->last_name)
            ->where('birthday', $patient->birthday)
            ->orderBy('consultation_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $payload = [
            'first_name' => $patient->first_name,
            'middle_name' => $patient->middle_name,
            'last_name' => $patient->last_name,
            'birthday' => $patient->birthday,
            'age' => $patient->age ?: $this->formatAgeFromBirthday((string) $patient->birthday),
            'gender' => $patient->gender,
            'civil_status' => $patient->civil_status,
            'contact_number' => $patient->contact_number,
            'address_purok' => $patient->address_purok,

            'consultation_date' => $validated['consultation_date'],
            'diagnosis' => $isNurse
                ? self::NURSE_PLACEHOLDER_DIAGNOSIS
                : $validated['diagnosis'],
            'condition_update' => null,
            'follow_up_recommendation' => $isNurse ? null : ($validated['follow_up_recommendation'] ?? null),
            // Doctor reviews these values, but they come from the latest BHW consultation.
            'subjective' => $isNurse ? ($validated['subjective'] ?? null) : $latest?->subjective,
            'objective' => $isNurse ? ($validated['objective'] ?? null) : $latest?->objective,
            'temp' => $latest?->temp,
            'bp' => $latest?->bp,
            'pr' => $latest?->pr,
            'rr' => $latest?->rr,
            'weight' => $latest?->weight,
            'height' => $latest?->height,
            'bmi' => $latest?->bmi,
            // Keep original encoder for continuity unless current user is nurse.
            'consulted_by' => $latest?->consulted_by,
        ];

        if (Auth::check()) {
            $actor = Auth::user();
            $actorName = trim(implode(' ', array_filter([
                $actor->first_name ?? null,
                $actor->middle_name ?? null,
                $actor->last_name ?? null,
            ])));
            if ($this->currentRole() === 'nurse') {
                $payload['doctor_consulted_by'] = $actorName ? ('Nurse ' . $actorName) : null;
            } else {
                $payload['doctor_consulted_by'] = $actorName ? ('Dr. ' . $actorName) : null;
            }
        }

        DB::transaction(function () use ($request, $payload, $isNurse) {
            $record = ClinicRecord::create($payload);
            $dispensedSummary = [];

            if ($request->hasFile('laboratory_images')) {
                foreach ($request->file('laboratory_images') as $file) {
                    if (!$file || !$file->isValid()) {
                        continue;
                    }

                    $path = $file->store('laboratories', 'public');
                    ClinicRecordFile::create([
                        'clinic_record_id' => $record->id,
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            if (!$isNurse && $request->has('medicines')) {
                $requestedByMedicineKey = [];
                $requestedLabelByMedicineKey = [];
                $medicineIds = collect($request->medicines)
                    ->pluck('id')
                    ->filter()
                    ->values();

                $selectedMedicines = Medicine::whereIn('id', $medicineIds)->get()->keyBy('id');
                $today = Carbon::today();
                $allInStockLots = Medicine::where('stock', '>', 0)
                    ->where(function ($query) use ($today) {
                        $query->whereNull('expiration_date')
                            ->orWhereDate('expiration_date', '>=', $today);
                    })
                    ->lockForUpdate()
                    ->get();

                foreach ($request->medicines as $med) {
                    if (empty($med['id']) || empty($med['quantity'])) {
                        continue;
                    }

                    $selected = $selectedMedicines->get($med['id']);
                    if (!$selected) {
                        continue;
                    }

                    $medicineKey = $this->normalizeMedicineName((string) $selected->name);
                    $requestedByMedicineKey[$medicineKey] = ($requestedByMedicineKey[$medicineKey] ?? 0) + (int) $med['quantity'];
                    $requestedLabelByMedicineKey[$medicineKey] = $selected->name;
                }

                foreach ($requestedByMedicineKey as $medicineKey => $requestedQty) {
                    if ($requestedQty <= 0) {
                        continue;
                    }

                    $lots = $allInStockLots
                        ->filter(fn ($lot) => $this->normalizeMedicineName((string) $lot->name) === $medicineKey)
                        ->sortBy([
                            ['expiration_date', 'asc'],
                            ['arrival_date', 'asc'],
                        ])
                        ->values();

                    $availableStock = $lots->sum('stock');
                    if ($availableStock < $requestedQty) {
                        $medicineLabel = $requestedLabelByMedicineKey[$medicineKey] ?? 'Selected medicine';
                        throw ValidationException::withMessages([
                            'medicines' => "Insufficient stock for {$medicineLabel}. Requested {$requestedQty}, available {$availableStock}.",
                        ]);
                    }

                    $remaining = $requestedQty;
                    foreach ($lots as $lot) {
                        if ($remaining <= 0) {
                            break;
                        }

                        $take = min($remaining, (int) $lot->stock);
                        if ($take <= 0) {
                            continue;
                        }

                        $record->medicines()->attach($lot->id, ['quantity' => $take]);
                        $dispensedSummary[] = "{$lot->name} (x{$take})";
                        $lot->decrement('stock', $take);
                        $lot->refresh();
                        InventoryLog::create([
                            'medicine_id' => $lot->id,
                            'transaction_type' => 'stock_out',
                            'quantity' => -$take,
                            'balance_after' => (int) $lot->stock,
                            'reference' => "Dispensed for consultation #{$record->id}",
                            'created_by' => auth()->id(),
                        ]);
                        $remaining -= $take;
                    }
                }
            }

            if (!empty($dispensedSummary)) {
                $dispensedBy = $this->currentRole() === 'nurse'
                    ? ($payload['consulted_by'] ?? 'Nurse')
                    : ($payload['doctor_consulted_by'] ?? 'Doctor');
                $record->update([
                    'medicines_given' => implode(', ', $dispensedSummary) . ' | Dispensed by: ' . $dispensedBy,
                ]);
            }

            ActivityLogger::log(
                'consultation_saved',
                "Doctor consultation saved for {$record->first_name} {$record->last_name}",
                $record,
                $request
            );
        });

        $routePrefix = $this->currentRole() === 'nurse' ? 'nurse' : 'doctor';
        return redirect()->route($routePrefix . '.record.index')->with('success', 'Record saved!');
    }

    public function show($id)
    {
        $record = ClinicRecord::with(['medicines', 'laboratoryFiles'])->findOrFail($id);

        $historyRecords = ClinicRecord::with('laboratoryFiles')
            ->where('first_name', $record->first_name)
            ->where('last_name', $record->last_name)
            ->where('birthday', $record->birthday)
            ->orderBy('consultation_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $historyRecords = $this->attachDisplayVitals($historyRecords);
        $history = $historyRecords
            ->filter(fn (ClinicRecord $item) => $this->isDoctorFinalizedRecord($item))
            ->values();

        $consultationDate = optional($record->consultation_date)->format('Y-m-d');
        $consultationTeam = $this->buildConsultationTeam(
            $historyRecords->filter(fn (ClinicRecord $item) => optional($item->consultation_date)->format('Y-m-d') === $consultationDate)
        );

        $record = $this->attachDisplayVitals(collect([$record]))->first();

        return view('doctor.record.show', [
            'record' => $record,
            'history' => $history,
            'consultationTeam' => $consultationTeam,
        ]);
    }

    public function toggleAvailability(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'doctor') {
            abort(403);
        }

        $newState = !$user->is_doctor_available;
        $user->update(['doctor_availability_override' => $newState]);

        ActivityLogger::log(
            'doctor_availability_toggled',
            'Doctor set availability to ' . ($newState ? 'Active' : 'Inactive'),
            $user,
            $request
        );

        return back()->with('success', 'Availability updated to ' . ($newState ? 'Active' : 'Inactive') . '.');
    }
}

