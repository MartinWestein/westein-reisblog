<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Prefix: /admin
| Naam-prefix: admin.*
| Middleware: web, auth, verified, role:admin|editor|auteur
|
| Volledig CRUD-beheer komt in Fase 4. Voor nu een placeholder die
| bevestigt dat de middleware-stack klopt.
*/

Route::get('/', function () {
    return response()->view('admin.placeholder');
})->name('home');
