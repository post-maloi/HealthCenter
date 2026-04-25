<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ClinicRecord;
use App\Models\Medicine;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $role = strtolower((string) (Auth::user()->role ?? 'doctor'));
        if ($role !== 'doctor') {
            abort(403);
        }

        $totalPatients = ClinicRecord::select('first_name', 'last_name', 'birthday')
            ->groupBy('first_name', 'last_name', 'birthday')
            ->get()
            ->count();

        $todayConsultations = ClinicRecord::whereDate('consultation_date', today())->count();
        $lowStockCount = Medicine::where('stock', '<', 10)->count();

        $recentRecords = ClinicRecord::query()
            ->forDoctorNurseDashboard()
            ->get()
            ->unique(fn ($item) => $item->first_name . $item->last_name . $item->birthday)
            ->take(5);

        return view('doctor.dashboard.index', [
            'totalPatients' => $totalPatients,
            'todayConsultations' => $todayConsultations,
            'lowStockCount' => $lowStockCount,
            'recentRecords' => $recentRecords,
            'isDoctorAvailable' => (bool) (Auth::user()?->is_doctor_available),
        ]);
    }
}
