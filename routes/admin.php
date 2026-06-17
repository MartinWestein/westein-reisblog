<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\FamilyMemberController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MediaPickerController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PostInlineImageController;
use App\Http\Controllers\Admin\RouteController;
use App\Http\Controllers\Admin\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('home');

Route::resource('categories', CategoryController::class)->except(['show']);
Route::resource('tags', TagController::class)->except(['show']);
Route::resource('posts', PostController::class)->except(['show']);
Route::resource('familieleden', FamilyMemberController::class)
    ->parameters(['familieleden' => 'family_member'])
    ->except(['show'])
    ->names('family-members');
Route::resource('pages', PageController::class)->except(['show']);
Route::resource('bestemmingen', DestinationController::class)
    ->parameters(['bestemmingen' => 'destination'])
    ->except(['show'])
    ->names('destinations');
Route::post('media/upload', [MediaController::class, 'upload'])->name('media.upload');
Route::patch('media/reorder', [MediaController::class, 'reorder'])->name('media.reorder');
Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
Route::resource('bestemmingen.locaties', LocationController::class)
    ->parameters(['bestemmingen' => 'destination', 'locaties' => 'location'])
    ->scoped(['location' => 'slug'])
    ->except(['show'])
    ->names('destinations.locations');
// Image-picker voor TipTap rich-editor (stap 4.6)
Route::get('media-picker', [MediaPickerController::class, 'index'])
    ->name('media-picker.index');

Route::post('posts/{post}/inline-images', [PostInlineImageController::class, 'store'])
    ->name('posts.inline-images.store');

Route::resource('reisroutes', RouteController::class)
    ->except(['show'])
    ->parameters(['reisroutes' => 'route'])
    ->names('reisroutes');

Route::get('reacties', [CommentController::class, 'index'])->name('comments.index');
Route::patch('reacties/{comment}/goedkeuren', [CommentController::class, 'approve'])->name('comments.approve');
Route::patch('reacties/{comment}/afkeuren', [CommentController::class, 'reject'])->name('comments.reject');
Route::patch('reacties/{comment}/spam', [CommentController::class, 'spam'])->name('comments.spam');
Route::delete('reacties/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
