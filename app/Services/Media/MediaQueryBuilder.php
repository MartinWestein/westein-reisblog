<?php

namespace App\Services\Media;

use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Centrale query-laag voor projectbrede media-browsing.
 *
 * Bedient twee consumenten:
 *   - MediaPickerController (Stap 4.6) — JSON-cursor-paginatie voor TipTap
 *   - Admin\MediaBrowserController (Stap 4.11) — HTML-grid met sortable headers
 *
 * Scope is opzettelijk beperkt tot content-collecties; avatars (User) en
 * portraits (FamilyMember) blijven buiten beeld (F4-12).
 */
class MediaQueryBuilder
{
    /**
     * Collecties die binnen browse-scope vallen. Single source of truth —
     * MediaPickerController gebruikt deze const ook voor Rule::in.
     */
    public const ALLOWED_COLLECTIONS = ['gallery', 'hero', 'featured', 'inline_images'];

    /**
     * Toegestane sort-kolommen. Whitelist om SQL-injectie via querystring uit te sluiten.
     */
    public const ALLOWED_SORT_COLUMNS = ['created_at', 'name', 'size'];

    private Builder $query;

    public function __construct()
    {
        $this->query = Media::query()
            ->whereIn('collection_name', self::ALLOWED_COLLECTIONS)
            ->with('model');
    }

    public function filterCollection(?string $name): self
    {
        if ($name !== null && in_array($name, self::ALLOWED_COLLECTIONS, true)) {
            $this->query->where('collection_name', $name);
        }

        return $this;
    }

    /**
     * Filter op eigenaar-modeltype. Resolve via config-whitelist —
     * nooit rauwe class-strings van client vertrouwen.
     */
    public function filterOwnerType(?string $key): self
    {
        if ($key === null) {
            return $this;
        }

        $class = config("westein.browsable_media_owners.{$key}");

        if ($class !== null) {
            $this->query->where('model_type', $class);
        }

        return $this;
    }

    public function search(?string $term): self
    {
        if ($term !== null && $term !== '') {
            $this->query->where('name', 'like', '%'.$term.'%');
        }

        return $this;
    }

    public function sort(string $column, string $direction = 'desc'): self
    {
        if (! in_array($column, self::ALLOWED_SORT_COLUMNS, true)) {
            $column = 'created_at';
        }

        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        $this->query->orderBy($column, $direction);

        return $this;
    }

    public function builder(): Builder
    {
        return $this->query;
    }

    /**
     * Eigenaar-context-label voor een Media-item.
     * Gedeelde formatter — gebruikt door beide consumenten.
     *
     * Stap 4.11 voegde Route + Newsletter toe (waren ontbrekend in de 4.6-versie).
     */
    public static function contextLabel(Media $media): string
    {
        $owner = $media->model;

        if (! $owner) {
            return 'Zonder eigenaar';
        }

        return match (class_basename($owner)) {
            'Destination' => 'Bestemming: '.($owner->name ?? '?'),
            'Location' => 'Locatie: '.($owner->destination?->name ?? '?').' → '.($owner->name ?? '?'),
            'Post' => 'Post: '.($owner->title ?? '?'),
            'Route' => 'Route: '.($owner->name ?? '?'),
            'Newsletter' => 'Nieuwsbrief: '.($owner->subject ?? '?'),
            default => class_basename($owner),
        };
    }
}
