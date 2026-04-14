<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClinicRecordController;


Route::redirect('/', '/dashboard');

Route::get('/dashboard', function () { 
    return view('dashboard'); 
})->name('dashboard');

// Index Page
Route::get('/records', [ClinicRecordController::class, 'index'])->name('record.index');

// Create Form Page
Route::get('/records/create', [ClinicRecordController::class, 'create'])->name('record.create');

// Store Action (The function that saves data)
Route::post('/records', [ClinicRecordController::class, 'store'])->name('record.store');

// Show Single Record
Route::get('/records/{record}', [ClinicRecordController::class, 'show'])->name('record.show');