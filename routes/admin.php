<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\FamilyMemberController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MediaBrowserController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MediaPickerController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PostInlineImageController;
use App\Http\Controllers\Admin\RouteController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\UserController;
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
Route::get('media', [MediaBrowserController::class, 'index'])->name('media.index');
Route::post('media/bulk-delete', [MediaBrowserController::class, 'bulkDelete'])->name('media.bulk-delete');
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

// Subscribers
Route::get('abonnees/export', [SubscriberController::class, 'export'])->name('subscribers.export');
Route::get('abonnees/import-template', [SubscriberController::class, 'importTemplate'])->name('subscribers.import-template');
Route::get('abonnees/import-fouten/{token}', [SubscriberController::class, 'downloadErrorReport'])
    ->name('subscribers.import-errors');
Route::post('abonnees/import', [SubscriberController::class, 'import'])->name('subscribers.import');
Route::post('abonnees/{subscriber}/stuur-bevestiging', [SubscriberController::class, 'sendConfirmation'])
    ->name('subscribers.send-confirmation');
Route::post('abonnees/stuur-bevestigingen', [SubscriberController::class, 'sendBulkConfirmations'])
    ->name('subscribers.send-bulk-confirmations');
Route::resource('abonnees', SubscriberController::class)
    ->parameters(['abonnees' => 'subscriber'])
    ->names('subscribers')
    ->except(['show']);

// Newsletters
Route::post('nieuwsbrieven/{newsletter}/stuur-test', [NewsletterController::class, 'sendTest'])
    ->name('newsletters.send-test');
Route::post('nieuwsbrieven/{newsletter}/verzenden', [NewsletterController::class, 'dispatchSend'])
    ->name('newsletters.dispatch');
Route::resource('nieuwsbrieven', NewsletterController::class)
    ->parameters(['nieuwsbrieven' => 'newsletter'])
    ->names('newsletters');

// Prullenbak (stap 4.12)
Route::get('prullenbak', [TrashController::class, 'index'])
    ->middleware('can:trash.manage')
    ->name('trash.index');
Route::post('prullenbak/{type}/{id}/herstel', [TrashController::class, 'restore'])
    ->middleware('can:trash.manage')
    ->where(['type' => 'post|destination|location|route|page', 'id' => '[0-9]+'])
    ->name('trash.restore');
Route::delete('prullenbak/{type}/{id}', [TrashController::class, 'forceDelete'])
    ->middleware('can:trash.manage')
    ->where(['type' => 'post|destination|location|route|page', 'id' => '[0-9]+'])
    ->name('trash.force-delete');
Route::post('prullenbak/bulk-herstel', [TrashController::class, 'bulkRestore'])
    ->middleware('can:trash.manage')
    ->name('trash.bulk-restore');

// Gebruikers (stap 4.13)
Route::middleware('can:users.manage')->group(function () {
    Route::resource('gebruikers', UserController::class)
        ->parameters(['gebruikers' => 'user'])
        ->names('users')
        ->except(['show', 'destroy']);
});
