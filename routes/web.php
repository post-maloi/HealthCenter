<?php

use App\Http\Controllers\ClinicRecordController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
});