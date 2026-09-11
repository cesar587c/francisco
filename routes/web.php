<?php

use App\Http\Controllers\Api\DashboardDataController;
use App\Http\Controllers\Api\RespondentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

    // Dados que alimentam o dashboard (front-end em resources/views/dashboard.blade.php)
    Route::get('/dashboard-data', [DashboardDataController::class, 'index'])->name('dashboard-data.index');
    Route::post('/dashboard-data', [DashboardDataController::class, 'store'])->name('dashboard-data.store');
    Route::delete('/respondents/{respondent}', [RespondentController::class, 'destroy'])->name('respondents.destroy');
});
