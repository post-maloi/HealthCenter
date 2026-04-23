<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ClinicRecord;
use App\Models\Medicine;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalPatients = ClinicRecord::query()
            ->select('first_name', 'last_name', 'birthday')
            ->groupBy('first_name', 'last_name', 'birthday')
            ->get()
            ->count();

        $todaysPatients = ClinicRecord::query()->whereDate('consultation_date', today())->count();
        $totalConsultations = ClinicRecord::count();
        $pendingConsultations = ClinicRecord::all()->where('workflow_status', 'waiting_for_doctor')->count();
        $lowStockMedicines = Medicine::query()->where('stock', '<', 10)->orderBy('stock')->limit(8)->get();
        $recentLogs = ActivityLog::with('user')->latest()->limit(200)->get();

        $weeklyPatients = ClinicRecord::query()
            ->whereDate('consultation_date', '>=', now()->subDays(6))
            ->selectRaw('DATE(consultation_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return view('admin.dashboard', compact(
            'totalPatients',
            'todaysPatients',
            'totalConsultations',
            'pendingConsultations',
            'lowStockMedicines',
            'recentLogs',
            'weeklyPatients'
        ));
    }
}
