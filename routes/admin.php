<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\FamilyMemberController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('home');

Route::resource('categories', CategoryController::class)->except(['show']);
Route::resource('tags', TagController::class)->except(['show']);
Route::resource('family-members', FamilyMemberController::class)
    ->parameters(['family-members' => 'family_member'])
    ->except(['show'])
    ->names('family-members');
Route::resource('pages', PageController::class)->except(['show']);
Route::resource('bestemmingen', DestinationController::class)
    ->parameters(['bestemmingen' => 'destination'])
    ->except(['show'])
    ->names('destinations');
