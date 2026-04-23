<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicRecord;
use App\Models\InventoryLog;
use App\Models\Medicine;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function ledger(): View
    {
        $logs = InventoryLog::with(['medicine', 'user'])->latest()->paginate(10);
        $lowStockMedicines = Medicine::query()
            ->selectRaw('name, SUM(stock) as total_stock')
            ->groupBy('name')
            ->havingRaw('SUM(stock) < ?', [10])
            ->orderBy('total_stock')
            ->get();
        $consultationIds = $logs->getCollection()
            ->pluck('reference')
            ->filter()
            ->map(function (string $reference) {
                if (preg_match('/Dispensed for consultation #(\d+)/i', $reference, $matches)) {
                    return (int) $matches[1];
                }
                return null;
            })
            ->filter()
            ->unique()
            ->values();

        $consultationNames = ClinicRecord::query()
            ->whereIn('id', $consultationIds)
            ->get()
            ->mapWithKeys(function (ClinicRecord $record) {
                $fullName = trim($record->first_name . ' ' . ($record->middle_name ? $record->middle_name . ' ' : '') . $record->last_name);
                return [$record->id => $fullName];
            });

        return view('admin.inventory.ledger', compact('logs', 'lowStockMedicines', 'consultationNames'));
    }
}
