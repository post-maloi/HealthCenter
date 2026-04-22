<?php

namespace App\Http\Controllers;

use App\Models\ClinicRecord;
use Illuminate\Http\Request;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ReportController extends Controller
{
    private function applyAgeGroupFilter($query, ?string $ageGroup)
    {
        return $query->when($ageGroup, function ($q) use ($ageGroup) {
            if ($ageGroup === '0-11') {
                $q->whereRaw('TIMESTAMPDIFF(MONTH, birthday, CURDATE()) BETWEEN 0 AND 11');
            } elseif ($ageGroup === '12-59') {
                $q->whereRaw('TIMESTAMPDIFF(MONTH, birthday, CURDATE()) BETWEEN 12 AND 59');
            } elseif ($ageGroup === 'senior') {
                $q->whereRaw('TIMESTAMPDIFF(YEAR, birthday, CURDATE()) >= 60');
            }
        });
    }

    public function patient(Request $request)
    {
        $search = $request->get('search');
        $ageGroup = $request->get('age_group', 'all');
        $gender = strtolower((string) $request->get('gender', 'all'));
        $address = $request->get('address', 'all');

        $patientsQuery = ClinicRecord::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('clinic_records')
                ->groupBy('first_name', 'last_name', 'birthday');
        })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('address_purok', 'like', "%{$search}%");
                });
            })
            ->when($gender !== 'all', function ($query) use ($gender) {
                $query->whereRaw('LOWER(gender) = ?', [$gender]);
            })
            ->when($address !== 'all', function ($query) use ($address) {
                $query->where('address_purok', $address);
            })
            ->orderBy('consultation_date', 'desc');

        $patients = $this->applyAgeGroupFilter($patientsQuery, $ageGroup === 'all' ? null : $ageGroup)->get();
        $addressOptions = ClinicRecord::query()
            ->whereNotNull('address_purok')
            ->where('address_purok', '!=', '')
            ->select('address_purok')
            ->distinct()
            ->orderBy('address_purok')
            ->pluck('address_purok')
            ->values();

        return view('reports.patient', [
            'patients' => $patients,
            'search' => $search,
            'ageGroup' => $ageGroup,
            'gender' => $gender,
            'address' => $address,
            'addressOptions' => $addressOptions,
        ]);
    }

    public function exportPatientExcel(Request $request)
    {
        $search = $request->get('search');
        $ageGroup = $request->get('age_group', 'all');
        $gender = strtolower((string) $request->get('gender', 'all'));
        $address = $request->get('address', 'all');

        $patientsQuery = ClinicRecord::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('clinic_records')
                ->groupBy('first_name', 'last_name', 'birthday');
        })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('address_purok', 'like', "%{$search}%");
                });
            })
            ->when($gender !== 'all', function ($query) use ($gender) {
                $query->whereRaw('LOWER(gender) = ?', [$gender]);
            })
            ->when($address !== 'all', function ($query) use ($address) {
                $query->where('address_purok', $address);
            })
            ->orderBy('consultation_date', 'desc');

        $patients = $this->applyAgeGroupFilter($patientsQuery, $ageGroup === 'all' ? null : $ageGroup)->get();

        $headers = [
            'Consultation Date',
            'Patient Name',
            'Birthday',
            'Age',
            'Gender',
            'Civil Status',
            'Address',
            'Diagnosis',
        ];

        $filenamePrefix = $ageGroup !== 'all' ? 'patient-report-' . $ageGroup : 'patient-report';
        $filename = $filenamePrefix . '-' . now()->format('Y-m-d-His') . '.xlsx';
        $tempPath = storage_path('app/' . $filename);

        $writer = new Writer();
        $writer->openToFile($tempPath);
        $writer->addRow(Row::fromValues($headers));

        foreach ($patients as $record) {
            $writer->addRow(Row::fromValues([
                $record->consultation_date,
                trim($record->first_name . ' ' . $record->middle_name . ' ' . $record->last_name),
                $record->birthday,
                $record->age,
                $record->gender,
                $record->civil_status,
                $record->address_purok,
                $record->diagnosis,
            ]));
        }

        $writer->close();

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function diagnosis(Request $request)
    {
        $search = $request->get('search');

        $diagnosisReports = ClinicRecord::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('diagnosis', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('consultation_date', 'desc')
            ->get();

        return view('reports.diagnosis', [
            'diagnosisReports' => $diagnosisReports,
            'search' => $search,
        ]);
    }

    public function exportDiagnosisExcel(Request $request)
    {
        $search = $request->get('search');

        $records = ClinicRecord::with('medicines')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('diagnosis', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('consultation_date', 'desc')
            ->get();

        $headers = [
            'Consultation Date',
            'Patient Name',
            'Birthday',
            'Age',
            'Gender',
            'Civil Status',
            'Address',
            'Diagnosis',
            'Subjective',
            'Objective',
            'Temp',
            'BP',
            'PR',
            'RR',
            'Weight',
            'Height',
            'BMI',
            'Medicines',
        ];

        $filenamePrefix = $search ? 'diagnosis-report-filtered' : 'diagnosis-report';
        $filename = $filenamePrefix . '-' . now()->format('Y-m-d-His') . '.xlsx';
        $tempPath = storage_path('app/' . $filename);

        $writer = new Writer();
        $writer->openToFile($tempPath);
        $writer->addRow(Row::fromValues($headers));

        foreach ($records as $record) {
            $medicines = $record->medicines->map(function ($medicine) {
                return $medicine->name . ' (x' . $medicine->pivot->quantity . ')';
            })->implode(', ');

            $writer->addRow(Row::fromValues([
                $record->consultation_date,
                trim($record->first_name . ' ' . $record->middle_name . ' ' . $record->last_name),
                $record->birthday,
                $record->age,
                $record->gender,
                $record->civil_status,
                $record->address_purok,
                $record->diagnosis,
                $record->subjective,
                $record->objective,
                $record->temp,
                $record->bp,
                $record->pr,
                $record->rr,
                $record->weight,
                $record->height,
                $record->bmi,
                $medicines ?: 'No medications prescribed',
            ]));
        }

        $writer->close();

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}

