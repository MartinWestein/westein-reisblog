<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('home');

Route::resource('categories', CategoryController::class)->except(['show']);
