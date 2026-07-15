<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mijn-account', [AccountController::class, 'show'])->name('account.show');
    Route::put('/mijn-account/gegevens', [AccountController::class, 'updateProfile'])->name('account.update-profile');

    // Legacy redirect voor bookmarks — /profiel/2fa is verhuisd naar /mijn-account#2fa.
    Route::redirect('/profiel/2fa', '/mijn-account#2fa', 301)->name('profile.two-factor');
});
// EOF
