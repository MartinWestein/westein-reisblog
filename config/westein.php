<?php

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

];
