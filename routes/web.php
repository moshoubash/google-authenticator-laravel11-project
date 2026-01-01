<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified', '2fa'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', '2fa'])->group(function () {
    Route::view('profile', 'profile')->name('profile');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/2fa/enable', [App\Http\Controllers\Google2FAController::class, 'enableTwoFactor'])->name('2fa.enable');
    Route::post('/2fa/disable', [App\Http\Controllers\Google2FAController::class, 'disableTwoFactor'])->name('2fa.disable');

    Route::post('/2fa/verify', function () {
        return redirect(route('dashboard'));
    })->name('2fa.verify')->middleware('2fa');
});

require __DIR__.'/auth.php';
