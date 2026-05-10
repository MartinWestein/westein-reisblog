<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/profiel/2fa', 'profile.two-factor')
        ->middleware('password.confirm')
        ->name('profile.two-factor');
});
