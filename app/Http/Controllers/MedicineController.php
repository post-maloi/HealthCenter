<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    /**
     * Display the main inventory list.
     */
    public function index() 
    {
        // Now sorting by the custom arrival_date so backdated stock 
        // appears in the correct chronological order.
        $medicines = Medicine::orderBy('arrival_date', 'desc')->get();
        return view('medicines.index', ['medicines' => $medicines]);
    }

    public function create()
    {
        return view('medicines.create'); 
    }

    /**
     * Store a new medicine batch/lot.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'batch_number' => 'nullable|string|max:100',
            'stock' => 'required|integer|min:0',
            'expiration_date' => 'required|date',
            // Changed from created_at to arrival_date
            'arrival_date' => 'required|date', 
        ]);

        Medicine::create($validated);

        return redirect()->route('medicines.index')
            ->with('success', 'New batch added successfully!');
    }

    public function edit(Medicine $medicine)
    {
        return view('medicines.edit', ['medicine' => $medicine]);
    }

    /**
     * Update the specified medicine batch.
     */
    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'batch_number' => 'nullable|string|max:100',
            'stock' => 'required|integer|min:0',
            'expiration_date' => 'required|date',
            // Ensure the update also handles the custom arrival date
            'arrival_date' => 'required|date',
        ]);

        $medicine->update($validated);

        return redirect()->route('medicines.index')
            ->with('success', 'Medicine updated successfully!');
    }

    /**
     * Remove a specific single lot/batch.
     */
    public function destroy(Medicine $medicine)
    {
        $medicine->delete();
        return redirect()->route('medicines.index')
            ->with('success', 'Specific batch removed.');
    }

    /**
     * Remove ALL batches/lots belonging to a specific medicine name.
     */
    public function destroyGroup(Request $request)
    {
        // Deletes the entire medicine category (e.g., all Paracetamol lots)
        Medicine::where('name', $request->name)->delete();
        
        return redirect()->route('medicines.index')
            ->with('success', 'All records for ' . $request->name . ' have been deleted.');
    }
}