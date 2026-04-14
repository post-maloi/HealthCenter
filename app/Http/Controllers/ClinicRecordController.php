<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClinicRecord; 
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class ClinicRecordController extends Controller
{
    /**
     * Display a listing of the records with Multi-Column Search and Age Filters.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $ageGroup = $request->input('age_group');

        // 1. Prepare the query, sorting by the latest date first
        $query = ClinicRecord::orderBy('consultation_date', 'desc');

        // 2. Handle Multi-Column Search (Name, Diagnosis, Age, or Medicines)
        if ($request->filled('search')) {
            $query->where(function($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('diagnosis', 'like', "%{$search}%")
                  ->orWhere('age', 'like', "%{$search}%")
                  ->orWhere('medicines_given', 'like', "%{$search}%");
            });
        }

        // 3. Handle Specific Age Group Filtering (Infants, Children, Seniors)
        if ($request->filled('age_group')) {
            $today = now();
            
            if ($ageGroup == 'infant') {
                // 0-11 Months: Birthday is within the last 11 months
                $query->where('birthday', '>=', $today->copy()->subMonths(11));
            } 
            elseif ($ageGroup == 'child') {
                // 12-59 Months: Birthday is between 1 year and 5 years ago
                $query->whereBetween('birthday', [
                    $today->copy()->subMonths(59), 
                    $today->copy()->subMonths(12)
                ]);
            } 
            elseif ($ageGroup == 'senior') {
                // Senior Citizen: Age 60 and above
                $query->where('birthday', '<=', $today->copy()->subYears(60));
            }
        }

        // 4. Execute and filter to show only the most recent entry per patient
        $records = $query->get()->unique('patient_name');

        return view('record.index', [
            'records'  => $records,
            'search'   => $search,
            'age_group' => $ageGroup
        ]);
    }

    /**
     * Show the form for creating a new record.
     */
    public function create(): View
    {
        return view('record.create');
    }

    /**
     * Store a newly created record in database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_name'      => 'required|string|max:255',
            'consultation_date' => 'required|date',
            'birthday'          => 'required|date',
            'gender'            => 'required|string',
            'diagnosis'         => 'nullable|string',
            'medicines_given'   => 'nullable|string',
        ]);

        // Calculate age automatically using Carbon based on birthday
        $validated['age'] = Carbon::parse($request->birthday)->age;

        ClinicRecord::create($validated);

        return redirect()->route('record.index')->with('success', 'Record saved successfully!');
    }

    /**
     * Display the specified record and all historical visits for this patient.
     */
    public function show(ClinicRecord $record): View
    {
        // Find every consultation ever logged for this specific patient name
        $history = ClinicRecord::where('patient_name', $record->patient_name)
            ->orderBy('consultation_date', 'desc')
            ->get();

        return view('record.show', [
            'record'  => $record,
            'history' => $history 
        ]);
    }
}