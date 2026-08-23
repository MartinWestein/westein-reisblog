<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Generieke statische pagina via de catch-all /{page:slug} (F5-111).
     * De catch-all sluit reserved_slugs uit via een route-constraint, zodat 'ie
     * geen echte routes kaapt (o.a. /admin, dat via routes/admin.php NA web.php laadt).
     * [^/]+ houdt 'm single-segment: multi-segment POSTs (bv. /admin/trash/...) raken
     * 'm niet, dus die blijven 404 i.p.v. 405. 404 als de pagina niet gepubliceerd is.
     */
    public function show(Page $page): View
    {
        abort_unless($page->isPublished(), 404);

        return view('pages.show', ['page' => $page]);
    }
}
