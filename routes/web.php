<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RouteController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/bestemmingen', [DestinationController::class, 'index'])
    ->name('destinations.index');
Route::get('/bestemmingen/{destination:slug}', [DestinationController::class, 'show'])
    ->name('destinations.show');

Route::get('/bestemmingen/{destination:slug}/{location:slug}', [LocationController::class, 'show'])
    ->scopeBindings()
    ->name('locations.show');
// --- Publieke posts (5.2.a) ---
// Location-post: 3-segment, scopeBindings dwingt dest->loc->post-hiërarchie af (F5-74).
Route::get('/bestemmingen/{destination:slug}/{location:slug}/{post:slug}', [PostController::class, 'show'])
    ->scopeBindings()
    ->name('posts.show');

// Reistips-index (5.2.c): named één-segment-route, vóór reistips.show én
// vóór een toekomstige /{page:slug}-catch-all (zie loose-ends).
Route::get('/reistips', [PostController::class, 'indexTips'])
    ->name('reistips.index');
// Reistips: categorie-leidende tip-URL (F5-72).
Route::get('/reistips/{post:slug}', [PostController::class, 'showTip'])
    ->name('reistips.show');

// Blog-index (F5-70).
Route::get('/verhalen', [PostController::class, 'index'])
    ->name('posts.index');

// --- Publieke reisroutes (5.3.a) ---
// reisroutes.index als named één-segment-route: vóór reisroutes.show én vóór
// een toekomstige /{page:slug}-catch-all (zie loose-ends).
Route::get('/reisroutes', [RouteController::class, 'index'])
    ->name('reisroutes.index');

// Route-detail (5.3.a kaal, 5.3.b compleet met Leaflet + notes + cross-links).
Route::get('/reisroutes/{route:slug}', [RouteController::class, 'show'])
    ->name('reisroutes.show');

// --- Publieke fotogalerij (5.3.c) ---
// Named één-segment-route, vóór een toekomstige /{page:slug}-catch-all.
Route::get('/fotos', [PhotoController::class, 'index'])
    ->name('fotos.index');

// --- Auteurs + Over ons (5.4.a) ---
// Named routes, vóór een toekomstige /{page:slug}-catch-all (5.4.b).
Route::get('/over-ons', [AuthorController::class, 'overview'])->name('about');
Route::get('/auteurs/{familyMember:slug}', [AuthorController::class, 'show'])->name('authors.show');

// --- Contact (5.4.b-ii) ---
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])
    ->middleware(['throttle:6,1', ProtectAgainstSpam::class])
    ->name('contact.send');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mijn-account', [AccountController::class, 'show'])->name('account.show');
    Route::put('/mijn-account/gegevens', [AccountController::class, 'updateProfile'])->name('account.update-profile');
    Route::redirect('/profiel/2fa', '/mijn-account#2fa', 301)->name('profile.two-factor');

    // Publieke reactie plaatsen (F5-90): post via slug-binding, honeypot-beschermd.
    Route::post('/reacties/{post:slug}', [CommentController::class, 'store'])
        ->middleware(ProtectAgainstSpam::class)
        ->name('comments.store');
});

// --- Statische pagina's via catch-all (5.4.b, F5-111) ---
// Single-segment ([^/]+) GET-route die de reserved_slugs uitsluit via een negatieve
// lookahead. Zo kaapt 'ie geen echte routes (o.a. /admin uit routes/admin.php, dat
// NA web.php laadt) en raakt 'ie geen multi-segment URLs (POSTs blijven 404, niet 405).
$reservedSlugs = implode('|', config('westein.reserved_slugs'));
Route::get('/{page:slug}', [PageController::class, 'show'])
    ->where('page', '(?!('.$reservedSlugs.')$)[^/]+')
    ->name('pages.show');

// EOF
