<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FamilyMemberController;
use App\Http\Controllers\Admin\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('home');

Route::resource('categories', CategoryController::class)->except(['show']);
Route::resource('tags', TagController::class)->except(['show']);
Route::resource('family-members', FamilyMemberController::class)
    ->parameters(['family-members' => 'family_member'])
    ->except(['show'])
    ->names('family-members');
