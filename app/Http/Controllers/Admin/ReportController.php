<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicRecord;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
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

        return view('admin.reports.index', compact('consultationReport', 'medicineUsage'));
    }

    public function exportConsultationExcel()
    {
        $consultationReport = ClinicRecord::query()
            ->selectRaw('DATE(consultation_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderByDesc('day')
            ->limit(30)
            ->get();

        $filename = 'admin-consultation-report-' . now()->format('Y-m-d-His') . '.xlsx';
        $tempPath = storage_path('app/' . $filename);

        $writer = new Writer();
        $writer->openToFile($tempPath);
        $writer->addRow(Row::fromValues(['Consultation Report (30 days)']));
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['Date', 'Total Consultations']));

        foreach ($consultationReport as $row) {
            $writer->addRow(Row::fromValues([$row->day, $row->total]));
        }

        $writer->close();

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function exportMedicineUsageExcel()
    {
        $medicineUsage = InventoryLog::query()
            ->where('transaction_type', 'stock_out')
            ->selectRaw('medicine_id, SUM(ABS(quantity)) as used_quantity')
            ->groupBy('medicine_id')
            ->with('medicine')
            ->orderByDesc('used_quantity')
            ->limit(25)
            ->get();

        $filename = 'admin-medicine-usage-' . now()->format('Y-m-d-His') . '.xlsx';
        $tempPath = storage_path('app/' . $filename);

        $writer = new Writer();
        $writer->openToFile($tempPath);
        $writer->addRow(Row::fromValues(['Medicine Usage']));
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['Medicine', 'Used Quantity']));

        foreach ($medicineUsage as $usage) {
            $writer->addRow(Row::fromValues([
                $usage->medicine?->name ?? 'Unknown',
                $usage->used_quantity,
            ]));
        }

        $writer->close();

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
