<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicRecord;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->date('date')?->format('Y-m-d') ?? now()->toDateString();

        $dailyPatients = ClinicRecord::query()
            ->whereDate('consultation_date', $date)
            ->orderBy('consultation_date', 'desc')
            ->get();

        $consultationReport = ClinicRecord::query()
            ->selectRaw('DATE(consultation_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderByDesc('day')
            ->limit(30)
            ->get();

        $medicineUsage = InventoryLog::query()
            ->where('transaction_type', 'stock_out')
            ->selectRaw('medicine_id, SUM(ABS(quantity)) as used_quantity')
            ->groupBy('medicine_id')
            ->with('medicine')
            ->orderByDesc('used_quantity')
            ->limit(25)
            ->get();

        return view('admin.reports.index', compact('date', 'dailyPatients', 'consultationReport', 'medicineUsage'));
    }
}
