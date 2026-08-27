<?php

namespace App\Console\Commands;

use App\Models\Destination;
use App\Models\FamilyMember;
use App\Models\Location;
use App\Models\Page;
use App\Models\Post;
use App\Models\Route as TravelRoute;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Genereer public/sitemap.xml uit de gepubliceerde content';

    public function handle(): int
    {
        $sitemap = Sitemap::create();

        // Statische index-pagina's
        $sitemap->add(Url::create(url('/')));
        foreach ([
            'destinations.index', 'posts.index', 'reistips.index', 'reisroutes.index',
            'fotos.index', 'about', 'contact', 'newsletter.show',
        ] as $name) {
            $sitemap->add(Url::create(route($name)));
        }

        Destination::query()->get()->each(fn (Destination $d) => $sitemap->add(
            Url::create(route('destinations.show', $d))->setLastModificationDate($d->updated_at)
        ));

        Location::query()->with('destination')->get()->each(function (Location $l) use ($sitemap) {
            if ($l->destination) {
                $sitemap->add(
                    Url::create(route('locations.show', [$l->destination, $l]))->setLastModificationDate($l->updated_at)
                );
            }
        });

        Post::query()->published()->with(['categories', 'destination', 'location'])->get()
            ->each(fn (Post $p) => $sitemap->add(
                Url::create($p->url())->setLastModificationDate($p->updated_at)
            ));

        TravelRoute::query()->published()->get()->each(fn (TravelRoute $r) => $sitemap->add(
            Url::create(route('reisroutes.show', $r))->setLastModificationDate($r->updated_at)
        ));

        FamilyMember::query()->get()->each(fn (FamilyMember $m) => $sitemap->add(
            Url::create(route('authors.show', $m))
        ));

        Page::query()->published()->get()->each(fn (Page $page) => $sitemap->add(
            Url::create(route('pages.show', $page))->setLastModificationDate($page->updated_at)
        ));

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap gegenereerd: '.public_path('sitemap.xml'));

        return self::SUCCESS;
    }
}
