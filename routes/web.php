<?php

use App\Http\Controllers\ClinicRecordController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DoctorClinicRecordController;
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
    
    // --- DASHBOARD ---
    // Keep /dashboard as a single entry point and redirect based on role.
    Route::get('/dashboard', function () {
        $role = Auth::user()->role ?? 'bhw';
        if (in_array($role, ['doctor', 'nurse'], true)) {
            return redirect()->route('doctor.dashboard');
        }

        $totalPatients = ClinicRecord::select('first_name', 'last_name', 'birthday')
            ->groupBy('first_name', 'last_name', 'birthday')
            ->get()
            ->count();

        $todayConsultations = ClinicRecord::whereDate('consultation_date', today())->count();
        $lowStockCount = Medicine::where('stock', '<', 10)->count();

        $recentRecords = ClinicRecord::latest('consultation_date')
            ->get()
            ->unique(fn ($item) => $item->first_name . $item->last_name . $item->birthday)
            ->take(5);

        return view('dashboard', [
            'totalPatients'      => $totalPatients,
            'todayConsultations' => $todayConsultations,
            'lowStockCount'      => $lowStockCount,
            'recentRecords'      => $recentRecords,
        ]);
    })->name('dashboard');

    // --- CLINIC RECORDS & APPOINTMENTS ---
    Route::middleware('role:bhw,nurse,doctor')->group(function () {
        Route::prefix('record')->name('record.')->group(function () {
            Route::get('/create', [ClinicRecordController::class, 'create'])->name('create')->middleware('role:bhw');
            Route::post('/store', [ClinicRecordController::class, 'store'])->name('store')->middleware('role:bhw');
            Route::post('/quick-add', [ClinicRecordController::class, 'quickStore'])->name('quickStore')->middleware('role:bhw');
            Route::get('/{id}/edit', [ClinicRecordController::class, 'edit'])->name('edit');
        });

        // Resources
        Route::resource('record', ClinicRecordController::class)->except(['create', 'store', 'edit']);
        Route::resource('medicines', MedicineController::class)->middleware('role:bhw,doctor,nurse');
    
        // Medicines - Custom Group Delete
        Route::delete('/medicines-destroy-group', [MedicineController::class, 'destroyGroup'])->name('medicines.destroy_group')->middleware('role:bhw,doctor,nurse');

        // Reports
        Route::prefix('reports')->name('reports.')->middleware('role:bhw')->group(function () {
            Route::get('/patients', [ReportController::class, 'patient'])->name('patients');
            Route::get('/patients/export', [ReportController::class, 'exportPatientExcel'])->name('patients.export');
            Route::get('/diagnosis', [ReportController::class, 'diagnosis'])->name('diagnosis');
            Route::get('/diagnosis/export', [ReportController::class, 'exportDiagnosisExcel'])->name('diagnosis.export');
        });

        Route::get('/record/{id}/print', [ClinicRecordController::class, 'print'])->name('record.print');
    });

    // --- DOCTOR AREA ---
    Route::prefix('doctor')->name('doctor.')->middleware('role:doctor,nurse')->group(function () {
        Route::get('/dashboard', [DoctorClinicRecordController::class, 'dashboard'])->name('dashboard');

        Route::get('/patient/{id}', [DoctorClinicRecordController::class, 'patientInfo'])->name('patient.info');

        Route::prefix('record')->name('record.')->group(function () {
            Route::get('/', [DoctorClinicRecordController::class, 'index'])->name('index');
            Route::get('/create', [DoctorClinicRecordController::class, 'create'])->name('create');
            Route::post('/store', [DoctorClinicRecordController::class, 'store'])->name('store');
            Route::get('/{id}', [DoctorClinicRecordController::class, 'show'])->name('show');
        });
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});