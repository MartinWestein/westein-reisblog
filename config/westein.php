<?php

use App\Models\Destination;
use App\Models\Location;
use App\Models\Newsletter;
use App\Models\Post;
use App\Models\Route;

return [

    /*
    |--------------------------------------------------------------------------
    | Gereserveerde slugs
    |--------------------------------------------------------------------------
    | Top-level URL-segmenten die door echte routes worden gebruikt
    | (zie masterplan §3.5). Een Page mag deze niet als slug krijgen,
    | anders wordt 'ie nooit bereikt door de catch-all /{slug}-route.
    */

    'reserved_slugs' => [
        'admin', 'login', 'register', 'logout', 'dashboard', 'profiel',
        'bestemmingen', 'reistips', 'categorie', 'tag', 'auteurs',
        'reisroutes', 'fotos', 'blog', 'nieuwsbrief',
        'email', 'password', 'two-factor-challenge', 'up',
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery media — toegestane eigenaarsmodellen
    |--------------------------------------------------------------------------
    | Mapt de client-side type-string op de echte modelklasse. NOOIT de rauwe
    | class-string van de client vertrouwen — alleen wat hier staat mag doel
    | zijn van upload/reorder/delete via de generieke media-endpoints.
    */
    'gallery_models' => [
        'destination' => Destination::class,
        'location' => Location::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Browsable media — toegestane eigenaarsmodellen
    |--------------------------------------------------------------------------
    | Mapt de owner_type-querystring-key in /admin/media (Stap 4.11) op de
    | echte modelklasse. Bron-van-waarheid voor de owner_type-dropdown én
    | de server-side validatie. Bewust losgekoppeld van 'gallery_models' —
    | die laatste beheert upload-doelen, dit beheert browse-bron.
    | NOOIT rauwe class-strings van de client vertrouwen.
    */
    'browsable_media_owners' => [
        'destination' => Destination::class,
        'location' => Location::class,
        'post' => Post::class,
        'route' => Route::class,
        'newsletter' => Newsletter::class,
    ],

    // Slug van de algemene 'Tips'-categorie — heft de bestemming-verplichting op bij Posts (masterplan §3.4)
    'general_tips_category_slug' => 'tips',

    /*
    |--------------------------------------------------------------------------
    | Nieuwsbrief
    |--------------------------------------------------------------------------
    | Knoppen voor de nieuwsbrief-rendering en -dispatch. Houd hier ook
    | toekomstige batch-/throttle-parameters voor blok f.
    */
    'newsletter' => [
        // Aantal recente posts in de digest-template.
        'digest_post_count' => 5,
    ],

];
