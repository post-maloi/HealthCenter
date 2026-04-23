<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClinicRecord; 
use App\Models\Medicine;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class ClinicRecordController extends Controller
{
    private const DOCTOR_PLACEHOLDER_DIAGNOSIS = 'waiting_for_doctor/nurse';

    private function currentRole(): string
    {
        return (string) (Auth::user()->role ?? 'bhw');
    }

    private function buildQueueLabel(?string $existingObjective = null): string
    {
        $todayCount = ClinicRecord::whereDate('consultation_date', Carbon::today())->count() + 1;
        $queueLine = 'Queue No: ' . $todayCount;

        if (!$existingObjective) {
            return $queueLine;
        }

        return trim($existingObjective . PHP_EOL . $queueLine);
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
            // Group by normalized name so minor spacing/case differences do not duplicate entries.
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

    public function index(Request $request)
    {
        $search = $request->get('search');
        $allMedicines = $this->getDispensableMedicinesForSelection();

        $records = ClinicRecord::whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('clinic_records')
                ->groupBy('first_name', 'last_name', 'birthday');
        })
        ->when($search, function($query) use ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%");
            });
        })
        ->orderBy('consultation_date', 'desc')
        ->get();

        $records = $this->attachDisplayVitals($records);

        return view('record.index', [
            'records' => $records,
            // Use one primary lot per medicine name to avoid duplicate picker options.
            'allMedicines' => $allMedicines
        ]);
    }

    public function create()
    {
        $allMedicines = $this->getDispensableMedicinesForSelection();
        $addressOptions = ClinicRecord::query()
            ->whereNotNull('address_purok')
            ->where('address_purok', '!=', '')
            ->select('address_purok')
            ->distinct()
            ->orderBy('address_purok')
            ->pluck('address_purok')
            ->values();

        return view('record.create', [
            // Keep quick/full consultation medicine list consistent and non-duplicated.
            'allMedicines' => $allMedicines,
            'addressOptions' => $addressOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = $this->currentRole();
        if ($role === 'nurse') {
            throw new AuthorizationException('Nurse cannot create a new consultation record.');
        }

        $validated = $request->validate([
            'last_name'         => 'required|string|max:255',
            'first_name'        => 'required|string|max:255',
            'middle_name'       => 'nullable|string|max:255',
            'birthday'          => 'required|date',
            'consultation_date' => 'required|date',
            'gender'            => 'required|string',
            'civil_status'      => 'required|string',
            'contact_number'    => 'nullable|string|max:50',
            'address_purok'     => 'required|string',
            'diagnosis'         => 'nullable|string',
            'subjective'        => 'nullable|string',
            'objective'         => 'nullable|string',
            'temp'              => 'nullable|string',
            'bp'                => 'nullable|string',
            'pr'                => 'nullable|string',
            'rr'                => 'nullable|string',
            'weight'            => 'nullable|numeric',
            'height'            => 'nullable|numeric',
            'bmi'               => 'nullable|string',
            'age'               => 'nullable|string',
        ]);

        // Track who consulted the patient (BHW user).
        if (Auth::check()) {
            $user = Auth::user();
            $validated['consulted_by'] = trim(implode(' ', array_filter([
                $user->first_name ?? null,
                $user->middle_name ?? null,
                $user->last_name ?? null,
            ])));
        }

        // Always keep a clean, consistent age format for both full form and quick add modal.
        if (empty($validated['age'])) {
            $validated['age'] = $this->formatAgeFromBirthday($validated['birthday']);
        } elseif (is_numeric($validated['age'])) {
            $validated['age'] = round($validated['age']) . ' yrs';
        }

        // BHW can only create patient + symptoms + queue; diagnosis/medications/lab are doctor-only.
        // Keep captured vitals so they remain visible in all history views.
        $validated['diagnosis'] = self::DOCTOR_PLACEHOLDER_DIAGNOSIS;
        $validated['objective'] = $this->buildQueueLabel($validated['objective'] ?? null);

        DB::transaction(function () use ($request, $validated) {
            $record = ClinicRecord::create($validated);
            $record->medicines()->detach();

            ActivityLogger::log(
                'patient_record_created',
                "Created record for {$record->first_name} {$record->last_name}",
                $record,
                $request
            );
        });

        return redirect()->route('record.index')->with('success', 'Record saved!');
    }

    public function show($id)
    {
        $record = ClinicRecord::with(['medicines', 'laboratoryFiles'])->findOrFail($id);

        $history = ClinicRecord::with('laboratoryFiles')
            ->where('first_name', $record->first_name)
            ->where('last_name', $record->last_name)
            ->where('birthday', $record->birthday)
            ->orderBy('consultation_date', 'desc')
            ->get();

        $history = $this->attachDisplayVitals($history);
        $record = $this->attachDisplayVitals(collect([$record]))->first();

        return view('record.show', [
            'record' => $record,
            'history' => $history
        ]);
    }

    public function edit($id)
    {
        $record = ClinicRecord::with('medicines')->findOrFail($id);
        $allMedicines = $this->getDispensableMedicinesForSelection();

        return view('record.edit', [
            'record' => $record,
            'allMedicines' => $allMedicines,
        ]);
    }

    public function print($id)
    {
        $record = ClinicRecord::with('medicines')->findOrFail($id);

        return view('record.print', [
            'record' => $record,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $record = ClinicRecord::findOrFail($id);
        $role = $this->currentRole();

        if ($role === 'nurse') {
            $validated = $request->validate([
                'temp' => 'nullable|string|max:50',
                'bp' => 'nullable|string|max:50',
                'weight' => 'nullable|numeric',
                'objective' => 'nullable|string',
            ]);

            $triage = trim((string) $request->input('triage'));
            $monitoring = trim((string) $request->input('monitoring_notes'));
            $objectiveParts = [];
            if ($triage !== '') {
                $objectiveParts[] = 'Triage: ' . $triage;
            }
            if ($monitoring !== '') {
                $objectiveParts[] = 'Monitoring: ' . $monitoring;
            }

            $record->update([
                'temp' => $validated['temp'] ?? null,
                'bp' => $validated['bp'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'objective' => !empty($objectiveParts)
                    ? implode(PHP_EOL, $objectiveParts)
                    : ($validated['objective'] ?? $record->objective),
                'diagnosis' => self::DOCTOR_PLACEHOLDER_DIAGNOSIS,
            ]);

            return redirect()->route('record.show', $id)->with('success', 'Nurse updates saved. Status: waiting_for_doctor');
        }

        if ($role === 'bhw') {
            $validated = $request->validate([
                'first_name'        => 'required|string|max:255',
                'middle_name'       => 'nullable|string|max:255',
                'last_name'         => 'required|string|max:255',
                'birthday'          => 'required|date',
                'consultation_date' => 'required|date',
                'gender'            => 'required|string',
                'civil_status'      => 'required|string',
                'contact_number'    => 'nullable|string|max:50',
                'address_purok'     => 'required|string',
                'temp'              => 'nullable|string',
                'bp'                => 'nullable|string',
                'pr'                => 'nullable|string',
                'rr'                => 'nullable|string',
                'weight'            => 'nullable|numeric',
                'height'            => 'nullable|numeric',
                'bmi'               => 'nullable|string',
                'subjective'        => 'nullable|string',
                'objective'         => 'nullable|string',
            ]);

            $record->update([
                ...$validated,
                'age' => $this->formatAgeFromBirthday($validated['birthday']),
                // Preserve doctor/nurse diagnosis and dispensed medicines.
                'diagnosis' => $record->diagnosis,
                'medicines_given' => $record->medicines_given,
            ]);

            ActivityLogger::log(
                'patient_record_updated',
                "BHW updated record for {$record->first_name} {$record->last_name}",
                $record,
                $request
            );

            return redirect()->route('record.show', $id)->with('success', 'Record updated successfully.');
        }
        
        $validated = $request->validate([
            'first_name'        => 'required|string|max:255',
            'middle_name'       => 'nullable|string|max:255',
            'last_name'         => 'required|string|max:255',
            'birthday'          => 'required|date',
            'consultation_date' => 'required|date',
            'gender'            => 'required|string',
            'civil_status'      => 'required|string',
            'contact_number'    => 'nullable|string|max:50',
            'address_purok'     => 'required|string',
            'diagnosis'         => 'nullable|string',
            'temp'              => 'nullable|string',
            'bp'                => 'nullable|string',
            'pr'                => 'nullable|string',
            'rr'                => 'nullable|string',
            'weight'            => 'nullable|numeric',
            'height'            => 'nullable|numeric',
            'bmi'               => 'nullable|string',
            'subjective'        => 'nullable|string',
            'objective'         => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $record, $validated) {
            $validated['age'] = $this->formatAgeFromBirthday($validated['birthday']);
            $validated['diagnosis'] = self::DOCTOR_PLACEHOLDER_DIAGNOSIS;

            // Prevent non-doctor edits from prescribing/dispensing via this route.
            $record->medicines()->detach();

            $record->update($validated);
            ActivityLogger::log(
                'patient_record_updated',
                "Updated record for {$record->first_name} {$record->last_name}",
                $record,
                $request
            );
        });

        return redirect()->route('record.show', $id)->with('success', 'Record updated successfully!');
    }
}