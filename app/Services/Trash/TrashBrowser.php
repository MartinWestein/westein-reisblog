<?php

namespace App\Services\Trash;

use App\Models\Destination;
use App\Models\Location;
use App\Models\Page;
use App\Models\Post;
use App\Models\Route;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TrashBrowser
{
    public const TYPES = [
        'post' => 'Post',
        'destination' => 'Bestemming',
        'location' => 'Locatie',
        'route' => 'Route',
        'page' => 'Pagina',
    ];

    public function browse(?string $type, int $perPage, int $page): LengthAwarePaginator
    {
        $items = collect();

        if ($type === null || $type === 'post') {
            $items = $items->concat($this->fetchPosts());
        }
        if ($type === null || $type === 'destination') {
            $items = $items->concat($this->fetchDestinations());
        }
        if ($type === null || $type === 'location') {
            $items = $items->concat($this->fetchLocations());
        }
        if ($type === null || $type === 'route') {
            $items = $items->concat($this->fetchRoutes());
        }
        if ($type === null || $type === 'page') {
            $items = $items->concat($this->fetchPages());
        }

        $sorted = $items
            ->sortByDesc(fn ($item) => $item->deleted_at->timestamp)
            ->values();

        $total = $sorted->count();
        $offset = ($page - 1) * $perPage;
        $slice = $sorted->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            items: $slice,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }

    private function fetchPosts(): Collection
    {
        return Post::onlyTrashed()
            ->with('author:id,name')
            ->get()
            ->map(fn (Post $p) => (object) [
                'id' => $p->id,
                'type' => 'post',
                'type_label' => 'Post',
                'title' => $p->title,
                'context' => $p->author?->name ? "door {$p->author->name}" : null,
                'deleted_at' => $p->deleted_at,
            ]);
    }

    private function fetchDestinations(): Collection
    {
        return Destination::onlyTrashed()
            ->withCount(['locations' => fn ($q) => $q->withTrashed()])
            ->get()
            ->map(fn (Destination $d) => (object) [
                'id' => $d->id,
                'type' => 'destination',
                'type_label' => 'Bestemming',
                'title' => $d->name,
                'context' => $d->locations_count > 0
                    ? "{$d->locations_count} " . ($d->locations_count === 1 ? 'locatie' : 'locaties')
                    : null,
                'deleted_at' => $d->deleted_at,
            ]);
    }

    private function fetchLocations(): Collection
    {
        return Location::onlyTrashed()
            ->with(['destination' => fn ($q) => $q->withTrashed()])
            ->get()
            ->map(fn (Location $l) => (object) [
                'id' => $l->id,
                'type' => 'location',
                'type_label' => 'Locatie',
                'title' => $l->name,
                'context' => $l->destination?->name,
                'deleted_at' => $l->deleted_at,
            ]);
    }

    private function fetchRoutes(): Collection
    {
        return Route::onlyTrashed()
            ->get()
            ->map(fn (Route $r) => (object) [
                'id' => $r->id,
                'type' => 'route',
                'type_label' => 'Route',
                'title' => $r->name,
                'context' => null,
                'deleted_at' => $r->deleted_at,
            ]);
    }

    private function fetchPages(): Collection
    {
        return Page::onlyTrashed()
            ->get()
            ->map(fn (Page $p) => (object) [
                'id' => $p->id,
                'type' => 'page',
                'type_label' => 'Pagina',
                'title' => $p->title,
                'context' => null,
                'deleted_at' => $p->deleted_at,
            ]);
    }
}
