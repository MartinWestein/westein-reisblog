<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/bestemmingen', [DestinationController::class, 'index'])
    ->name('destinations.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mijn-account', [AccountController::class, 'show'])->name('account.show');
    Route::put('/mijn-account/gegevens', [AccountController::class, 'updateProfile'])->name('account.update-profile');

    Route::redirect('/profiel/2fa', '/mijn-account#2fa', 301)->name('profile.two-factor');
});

// EOF
