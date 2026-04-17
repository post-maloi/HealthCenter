<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClinicRecord; 
use App\Models\Medicine;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClinicRecordController extends Controller
{
   public function index(Request $request)
{
    $search = $request->get('search');

    $records = ClinicRecord::whereIn('id', function ($query) use ($search) {
        $query->select(DB::raw('MAX(id)'))
            ->from('clinic_records')
            ->groupBy('first_name', 'middle_name', 'last_name', 'birthday');

        if ($search) {
            $query->where(function($q) use ($search) {
                // We search individual name columns since 'patient_name' doesn't exist in the DB
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }
    })
    ->orderBy('consultation_date', 'desc')
    ->get();

    $allMedicines = Medicine::where('stock', '>', 0)->get();

    return view('record.index', [
        'records' => $records,
        'allMedicines' => $allMedicines
    ]);
}
    public function create()
    {
        $allMedicines = Medicine::where('stock', '>', 0)->get();

        return view('record.create', [
            'allMedicines' => $allMedicines
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'        => 'nullable|string|max:255',
            'middle_name'       => 'nullable|string|max:255',
            'last_name'         => 'nullable|string|max:255',
            'patient_name'      => 'nullable|string|max:255',
            'consultation_date' => 'required|date',
            'birthday'          => 'required|date',
            'gender'            => 'required|string',
            'civil_status'      => 'required|string',
            'contact_number'    => 'nullable|string',
            'address_purok'     => 'required|string',
            'diagnosis'         => 'nullable|string',
            'medicines'         => 'nullable|array',
        ]);

        if (!$request->patient_name) {
            $middle = $request->middle_name ? " {$request->middle_name} " : " ";
            $validated['patient_name'] = trim("{$request->first_name}{$middle}{$request->last_name}");
        } else {
            $validated['patient_name'] = $request->patient_name;
        }

        DB::transaction(function () use ($request, &$validated) {
            $medicineDescriptions = [];

            if ($request->has('medicines')) {
                foreach ($request->medicines as $item) {
                    if (isset($item['id']) && isset($item['quantity'])) {
                        $medicine = Medicine::find($item['id']);
                        $qty = $item['quantity'];

                        if ($medicine && $medicine->stock >= $qty) {
                            $medicine->decrement('stock', $qty);
                            $medicineDescriptions[] = "{$medicine->name} (x{$qty})";
                        }
                    }
                }
            }

            $validated['medicines_given'] = implode(', ', $medicineDescriptions);

            // Calculation fix: ensure age is stored as a clean whole number
            $birth = Carbon::parse($request->birthday);
            $diff = $birth->diff(Carbon::now());
            $validated['age'] = ($diff->y === 0) ? $diff->m . ' Mon' : $diff->y . ' yrs';

            ClinicRecord::create($validated);
        });

        return redirect()->route('record.index')->with('success', 'Record saved successfully!');
    }

    public function show(ClinicRecord $record)
    {
        // history filters by Name, Birthday, and Contact to keep patients unique
        $history = ClinicRecord::where('first_name', $record->first_name)
            ->where('middle_name', $record->middle_name)
            ->where('last_name', $record->last_name)
            ->where('birthday', $record->birthday) 
            ->where('contact_number', $record->contact_number)
            ->orderBy('consultation_date', 'desc')
            ->get();

        return view('record.show', [
            'record'  => $record,
            'history' => $history 
        ]);
    }

    public function dashboard()
    {
        $totalPatients = ClinicRecord::count();
        $lowStock = Medicine::where('stock', '<=', 10)->count();

        return view('dashboard', [
            'totalPatients' => $totalPatients,
            'lowStock' => $lowStock
        ]);
    }

    public function quickStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_name'      => 'required|string',
            'consultation_date' => 'required|date',
            'birthday'          => 'required|date',
            'gender'            => 'required|string',
            'civil_status'      => 'required|string',
            'address_purok'     => 'required|string',
            'contact_number'    => 'nullable|string',
            'diagnosis'         => 'required|string',
            'medicines'         => 'nullable|array', 
        ]);

        DB::transaction(function () use ($request, &$validated) {
            $medicineDescriptions = [];

            if ($request->has('medicines')) {
                foreach ($request->medicines as $item) {
                    if (isset($item['id']) && isset($item['quantity'])) {
                        $medicine = Medicine::find($item['id']);
                        $qty = $item['quantity'];

                        if ($medicine && $medicine->stock >= $qty) {
                            $medicine->decrement('stock', $qty);
                            $medicineDescriptions[] = "{$medicine->name} (x{$qty})";
                        }
                    }
                }
            }

            $validated['medicines_given'] = implode(', ', $medicineDescriptions);

            // Storing age as whole number
            $birth = Carbon::parse($request->birthday);
            $diff = $birth->diff(Carbon::now());
            $validated['age'] = ($diff->y === 0) ? $diff->m . ' Mon' : $diff->y . ' yrs';

            ClinicRecord::create($validated);
        });

        return redirect()->route('record.index')->with('success', 'New consultation added for ' . $request->patient_name);
    }
}