<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClinicRecord; 
use App\Models\Medicine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClinicRecordController extends Controller
{
    private function formatAgeFromBirthday(string $birthday): string
    {
        $birth = Carbon::parse($birthday);
        $diff = $birth->diff(Carbon::now());

        return ($diff->y === 0) ? $diff->m . ' Mon' : $diff->y . ' yrs';
    }

    public function index(Request $request)
    {
        $search = $request->get('search');

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

        return view('record.index', [
            'records' => $records,
            'allMedicines' => Medicine::where('stock', '>', 0)->get()
        ]);
    }

    public function create()
    {
        return view('record.create', [
            'allMedicines' => Medicine::where('stock', '>', 0)->get()
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'last_name'         => 'required|string|max:255',
            'first_name'        => 'required|string|max:255',
            'middle_name'       => 'nullable|string|max:255',
            'birthday'          => 'required|date',
            'consultation_date' => 'required|date',
            'gender'            => 'required|string',
            'civil_status'      => 'required|string',
            'address_purok'     => 'required|string',
            'diagnosis'         => 'required|string',
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

        // Always keep a clean, consistent age format for both full form and quick add modal.
        if (empty($validated['age'])) {
            $validated['age'] = $this->formatAgeFromBirthday($validated['birthday']);
        } elseif (is_numeric($validated['age'])) {
            $validated['age'] = round($validated['age']) . ' yrs';
        }

        // Ensure vital signs are saved even if empty (preventing NULL issues)
        $record = ClinicRecord::create($validated);

        if ($request->has('medicines')) {
            foreach ($request->medicines as $med) {
                if (!empty($med['id']) && !empty($med['quantity'])) {
                    $record->medicines()->attach($med['id'], ['quantity' => $med['quantity']]);
                    Medicine::where('id', $med['id'])->decrement('stock', $med['quantity']);
                }
            }
        }
        

        return redirect()->route('record.index')->with('success', 'Record saved!');
    }

    public function show($id)
    {
        $record = ClinicRecord::with('medicines')->findOrFail($id);

        $history = ClinicRecord::where('first_name', $record->first_name)
            ->where('last_name', $record->last_name)
            ->where('birthday', $record->birthday)
            ->orderBy('consultation_date', 'desc')
            ->get();

        // Revised to use compact() for cleaner code
        return view('record.show', [
    'record' => $record,
    'history' => $history
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
        
        $validated = $request->validate([
            'first_name'        => 'required|string|max:255',
            'middle_name'       => 'nullable|string|max:255',
            'last_name'         => 'required|string|max:255',
            'birthday'          => 'required|date',
            'consultation_date' => 'required|date',
            'gender'            => 'required|string',
            'civil_status'      => 'required|string',
            'address_purok'     => 'required|string',
            'diagnosis'         => 'required|string',
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

            if ($request->has('medicines')) {
                $syncData = [];
                foreach ($request->medicines as $item) {
                    if (!empty($item['id'])) {
                        $syncData[$item['id']] = ['quantity' => $item['quantity'] ?? 1];
                    }
                }
                $record->medicines()->sync($syncData);
            }

            $record->update($validated);
        });

        return redirect()->route('record.show', $id)->with('success', 'Record updated successfully!');
    }
}