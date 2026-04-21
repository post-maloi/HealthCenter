<?php

use App\Http\Controllers\ClinicRecordController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\ClinicRecord;
use App\Models\Medicine;

// 1. ROOT REDIRECT
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// 2. GUEST ROUTES
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// 3. AUTH PROTECTED ROUTES
Route::middleware('auth')->group(function () {
    
    // --- DASHBOARD (Fixed with Name & Unique Count Logic) ---
    Route::get('/dashboard', function () {
        // Count unique patients by name and birthday to avoid double-counting
        $totalPatients = ClinicRecord::select('first_name', 'last_name', 'birthday')
            ->groupBy('first_name', 'last_name', 'birthday')
            ->get()
            ->count();

        // Count every consultation entry created today
        $todayConsultations = ClinicRecord::whereDate('consultation_date', today())->count();

        // Check for low stock items for the alert card
        $lowStockCount = Medicine::where('stock', '<', 10)->count();

        // Fetch 5 most recent unique patient interactions
        $recentRecords = ClinicRecord::latest('consultation_date')
            ->get()
            ->unique(fn($item) => $item->first_name . $item->last_name . $item->birthday)
            ->take(5);

        return view('dashboard', [
            'totalPatients'      => $totalPatients,
            'todayConsultations' => $todayConsultations,
            'lowStockCount'      => $lowStockCount,
            'recentRecords'      => $recentRecords,
        ]);
    })->name('dashboard'); // This fixes your RouteNotFoundException

    // --- CLINIC RECORDS & APPOINTMENTS ---
    Route::prefix('record')->name('record.')->group(function () {
        Route::get('/create', [ClinicRecordController::class, 'create'])->name('create');
        Route::post('/store', [ClinicRecordController::class, 'store'])->name('store');
        Route::post('/quick-add', [ClinicRecordController::class, 'quickStore'])->name('quickStore');
        Route::get('/{id}/edit', [ClinicRecordController::class, 'edit'])->name('edit');
    });

    // Resources
    Route::resource('record', ClinicRecordController::class)->except(['create', 'store', 'edit']);
    Route::resource('medicines', MedicineController::class);
    
    // Medicines - Custom Group Delete
    Route::delete('/medicines-destroy-group', [MedicineController::class, 'destroyGroup'])->name('medicines.destroy_group');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/patients', [ReportController::class, 'patient'])->name('patients');
        Route::get('/patients/export', [ReportController::class, 'exportPatientExcel'])->name('patients.export');
        Route::get('/diagnosis', [ReportController::class, 'diagnosis'])->name('diagnosis');
        Route::get('/diagnosis/export', [ReportController::class, 'exportDiagnosisExcel'])->name('diagnosis.export');
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/record/{id}/print', [ClinicRecordController::class, 'print'])->name('record.print');
});