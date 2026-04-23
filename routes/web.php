<?php

use App\Http\Controllers\ClinicRecordController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DoctorClinicRecordController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
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
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

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
    Route::middleware('role:admin,bhw,nurse,doctor')->group(function () {
        Route::prefix('record')->name('record.')->group(function () {
            Route::get('/create', [ClinicRecordController::class, 'create'])->name('create')->middleware('role:admin,bhw');
            Route::post('/store', [ClinicRecordController::class, 'store'])->name('store')->middleware('role:admin,bhw');
            Route::post('/quick-add', [ClinicRecordController::class, 'quickStore'])->name('quickStore')->middleware('role:admin,bhw');
            Route::get('/{id}/edit', [ClinicRecordController::class, 'edit'])->name('edit');
        });

        // Resources
        Route::resource('record', ClinicRecordController::class)->except(['create', 'store', 'edit']);
        Route::resource('medicines', MedicineController::class)->middleware('role:admin,bhw,doctor,nurse');
    
        // Medicines - Custom Group Delete
        Route::delete('/medicines-destroy-group', [MedicineController::class, 'destroyGroup'])->name('medicines.destroy_group')->middleware('role:admin,bhw,doctor,nurse');

        // Reports
        Route::prefix('reports')->name('reports.')->middleware('role:admin,bhw')->group(function () {
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
        Route::post('/availability/toggle', [DoctorClinicRecordController::class, 'toggleAvailability'])->name('availability.toggle')->middleware('role:doctor');

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

    // --- ADMIN CONTROL CENTER ---
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/status', [UserManagementController::class, 'toggleStatus'])->name('users.status');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/inventory/ledger', [AdminInventoryController::class, 'ledger'])->name('inventory.ledger');
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    });
});