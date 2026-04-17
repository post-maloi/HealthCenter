<?php

use App\Http\Controllers\ClinicRecordController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AppointmentController;

Route::prefix('record')->name('record.')->group(function () {
    // This handles showing the consultation form with the medicine list
    Route::get('/create', [AppointmentController::class, 'create'])->name('create');
    
    // This handles the form submission and inventory deduction
    Route::post('/store', [AppointmentController::class, 'store'])->name('store');
    
    // Your existing index and show routes...
    Route::get('/', [AppointmentController::class, 'index'])->name('index');
});

// 1. Root Route - Smart Redirect
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

// 3. AUTH ROUTES
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [ClinicRecordController::class, 'dashboard'])->name('dashboard');
    
    // Medicines - Custom Group Delete (Must be above resource)
    Route::delete('/medicines/destroy-group', [MedicineController::class, 'destroyGroup'])->name('medicines.destroy_group');
    
    // Resources
    Route::resource('record', ClinicRecordController::class);
    Route::resource('medicines', MedicineController::class);
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/records/quick-add', [ClinicRecordController::class, 'quickStore'])->name('record.quickStore');
// routes/web.php

// Show the form with the medicine list
Route::get('/appointment/create', [AppointmentController::class, 'create'])->name('appointment.create');

// Process the form submission and deduct stock
Route::post('/appointment/store', [AppointmentController::class, 'store'])->name('appointment.store');
    });
