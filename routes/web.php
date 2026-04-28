<?php

use App\Http\Controllers\ClinicRecordController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DoctorClinicRecordController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Nurse\DashboardController as NurseDashboardController;
use App\Http\Controllers\Bhw\DashboardController as BhwDashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    Route::get('/BHW/dashboard', function () {
        return redirect()->route('bhw.dashboard');
    });

    Route::get('/Nurse/dashboard', function () {
        return redirect()->route('nurse.dashboard');
    });

    Route::get('/bhw/dashboard', [BhwDashboardController::class, 'index'])
        ->name('bhw.dashboard')
        ->middleware('role:bhw');

    Route::get('/nurse/dashboard', [NurseDashboardController::class, 'index'])
        ->middleware('role:nurse')
        ->name('nurse.dashboard');
    
    // --- DASHBOARD ---
    // Keep /dashboard as a single entry point and redirect based on role.
    Route::get('/dashboard', function () {
        $role = strtolower((string) (Auth::user()->role ?? 'bhw'));
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($role === 'nurse') {
            return redirect()->route('nurse.dashboard');
        }

        if ($role === 'doctor') return redirect()->route('doctor.dashboard');
        if ($role === 'bhw') return redirect()->route('bhw.dashboard');
        return redirect()->route('bhw.dashboard');
    })->name('dashboard');

    // --- CLINIC RECORDS & APPOINTMENTS (LEGACY NON-PREFIXED) ---
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

    // --- BHW AREA ---
    Route::prefix('bhw')->name('bhw.')->middleware('role:bhw')->group(function () {
        Route::prefix('record')->name('record.')->group(function () {
            Route::get('/create', [ClinicRecordController::class, 'create'])->name('create');
            Route::post('/store', [ClinicRecordController::class, 'store'])->name('store');
            Route::post('/quick-add', [ClinicRecordController::class, 'quickStore'])->name('quickStore');
            Route::get('/{id}/edit', [ClinicRecordController::class, 'edit'])->name('edit');
            Route::get('/{id}/print', [ClinicRecordController::class, 'print'])->name('print');
        });
        Route::get('/record', [ClinicRecordController::class, 'index'])->name('record.index');
        Route::get('/record/{record}', [ClinicRecordController::class, 'show'])->name('record.show');
        Route::put('/record/{record}', [ClinicRecordController::class, 'update'])->name('record.update');
        Route::delete('/record/{record}', [ClinicRecordController::class, 'destroy'])->name('record.destroy');

        Route::resource('medicines', MedicineController::class)->names('medicines');
        Route::delete('/medicines-destroy-group', [MedicineController::class, 'destroyGroup'])->name('medicines.destroy_group');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/patients', [ReportController::class, 'patient'])->name('patients');
            Route::get('/patients/export', [ReportController::class, 'exportPatientExcel'])->name('patients.export');
            Route::get('/diagnosis', [ReportController::class, 'diagnosis'])->name('diagnosis');
            Route::get('/diagnosis/export', [ReportController::class, 'exportDiagnosisExcel'])->name('diagnosis.export');
        });
    });

    // --- DOCTOR AREA ---
    Route::prefix('doctor')->name('doctor.')->middleware('role:doctor')->group(function () {
        Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
        Route::post('/availability/toggle', [DoctorClinicRecordController::class, 'toggleAvailability'])->name('availability.toggle')->middleware('role:doctor');
        Route::get('/pending-patients', [DoctorClinicRecordController::class, 'pendingPatients'])->name('pending.index');

        Route::get('/patient/{id}', [DoctorClinicRecordController::class, 'patientInfo'])->name('patient.info');

        Route::prefix('record')->name('record.')->group(function () {
            Route::get('/', [DoctorClinicRecordController::class, 'index'])->name('index');
            Route::get('/create', [DoctorClinicRecordController::class, 'create'])->name('create');
            Route::post('/store', [DoctorClinicRecordController::class, 'store'])->name('store');
            Route::get('/{id}/print', [ClinicRecordController::class, 'print'])->name('print');
            Route::get('/{id}', [DoctorClinicRecordController::class, 'show'])->name('show');
        });
    });

    // --- NURSE AREA ---
    Route::prefix('nurse')->name('nurse.')->middleware('role:nurse')->group(function () {
        Route::get('/dashboard', [NurseDashboardController::class, 'index'])->name('dashboard');
        Route::get('/pending-patients', [DoctorClinicRecordController::class, 'pendingPatients'])->name('pending.index');
        Route::get('/patient/{id}', [DoctorClinicRecordController::class, 'patientInfo'])->name('patient.info');

        Route::prefix('record')->name('record.')->group(function () {
            Route::get('/', [DoctorClinicRecordController::class, 'index'])->name('index');
            Route::get('/create', [DoctorClinicRecordController::class, 'create'])->name('create');
            Route::post('/store', [DoctorClinicRecordController::class, 'store'])->name('store');
            Route::get('/{id}/print', [ClinicRecordController::class, 'print'])->name('print');
            Route::get('/{id}', [DoctorClinicRecordController::class, 'show'])->name('show');
        });
    });

    // Logout (GET fallback avoids 419 on stale CSRF token)
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', function () {
        return view('profile.show', ['user' => Auth::user()]);
    })->name('profile.show');
    Route::post('/profile/update', function (Request $request) {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:20',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_profile_photo' => 'nullable|boolean',
        ]);

        $changes = [];
        if (trim((string) $user->first_name) !== trim((string) ($validated['first_name'] ?? ''))) $changes[] = 'first name';
        if (trim((string) $user->middle_name) !== trim((string) ($validated['middle_name'] ?? ''))) $changes[] = 'middle name';
        if (trim((string) $user->last_name) !== trim((string) ($validated['last_name'] ?? ''))) $changes[] = 'last name';
        if (trim((string) $user->suffix) !== trim((string) ($validated['suffix'] ?? ''))) $changes[] = 'suffix';
        if (trim((string) $user->email) !== trim((string) ($validated['email'] ?? ''))) $changes[] = 'email';

        $user->update([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'] ?? null,
            'email' => $validated['email'],
        ]);

        if ($request->boolean('remove_profile_photo') && !empty($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
            $changes[] = 'removed profile photo';
        }

        if ($request->hasFile('profile_photo')) {
            if (!empty($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->update(['profile_photo_path' => $path]);
            $changes[] = 'updated profile photo';
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'profile_updated',
            'description' => 'Updated own profile (' . (!empty($changes) ? implode(', ', $changes) : 'no field changes') . ')',
            'subject_type' => $user::class,
            'subject_id' => $user->id,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Profile updated successfully.');
    })->name('profile.update');

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
        Route::get('/reports/consultation/export', [AdminReportController::class, 'exportConsultationExcel'])->name('reports.consultation.export');
        Route::get('/reports/medicine-usage/export', [AdminReportController::class, 'exportMedicineUsageExcel'])->name('reports.medicine-usage.export');
    });
});