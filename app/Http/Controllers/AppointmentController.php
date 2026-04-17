<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\ClinicRecord; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log; 
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index()
    {
        $allMedicines = Medicine::where('stock', '>', 0)
            ->where('expiration_date', '>', now())
            ->orderBy('expiration_date', 'asc')
            ->get();

        $records = ClinicRecord::latest()->get();

        return view('record.index', [
            'allMedicines' => $allMedicines,
            'records'      => $records
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
        // 1. Validate ALL fields coming from your form
        $validated = $request->validate([
            'first_name'        => 'required|string|max:255',
            'middle_name'       => 'nullable|string|max:255',
            'last_name'         => 'required|string|max:255',
            'consultation_date' => 'required|date',
            'birthday'          => 'required|date',
            'gender'            => 'required|string',
            'civil_status'      => 'required|string',
            'contact_number'    => 'nullable|string',
            'address_purok'     => 'required|string',
            'diagnosis'         => 'required|string',
            'medicines'         => 'nullable|array',
        ]);

        try {
            DB::transaction(function () use ($request, &$validated) {
                $medicineDescriptions = [];

                // 2. Process Medicines and Stock
                if ($request->has('medicines')) {
                    foreach ($request->medicines as $item) {
                        if (!empty($item['id']) && !empty($item['quantity'])) {
                            
                            $medicine = Medicine::where('id', $item['id'])->lockForUpdate()->first();
                            $qtyToGive = (int)$item['quantity'];

                            if ($medicine && $medicine->stock >= $qtyToGive) {
                                $medicine->decrement('stock', $qtyToGive);
                                $medicineDescriptions[] = "{$medicine->name} (x{$qtyToGive})";
                            } else {
                                throw new \Exception("Insufficient stock for " . ($medicine->name ?? 'selected medicine'));
                            }
                        }
                    }
                }

                // 3. Prepare additional data
                $validated['medicines_given'] = implode(', ', $medicineDescriptions);
                
                // Calculate Age server-side just in case
                $birthDate = Carbon::parse($request->birthday);
                $validated['age'] = $birthDate->diffInYears(Carbon::now()) . " yrs";

                // 4. Final Save (Only call this ONCE)
                ClinicRecord::create($validated);
            });

            return redirect()->route('record.index')->with('success', 'Consultation saved successfully!');

        } catch (\Exception $e) {
            // Log the actual error so you can find it in storage/logs/laravel.log
            Log::error("Save Record Error: " . $e->getMessage());
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}