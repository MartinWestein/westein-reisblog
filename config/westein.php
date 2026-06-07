<?php

use App\Models\Destination;
use App\Models\Location;

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

];
