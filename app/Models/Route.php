<?php

namespace App\Models;

use Database\Factories\RouteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Route extends Model implements HasMedia
{
    /** @use HasFactory<RouteFactory> */
    use HasFactory;

    use HasSlug;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'destination_id',
        'name',
        'slug',
        'description',
        'travel_date',
        'is_published',
        'published_at',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'travel_date' => 'date',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function waypoints(): HasMany
    {
        return $this->hasMany(RouteWaypoint::class)->orderBy('order');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'route_waypoints')
            ->withPivot(['order', 'notes'])
            ->withTimestamps()
            ->orderByPivot('order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('large')
            ->fit(Fit::Max, 2400, 1350)
            ->format('webp')
            ->quality(82)
            ->performOnCollections('hero');

        $this->addMediaConversion('medium')
            ->fit(Fit::Max, 1200, 675)
            ->format('webp')
            ->quality(82)
            ->performOnCollections('hero');

        $this->addMediaConversion('thumb')
            ->fit(Fit::Max, 400, 225)
            ->format('webp')
            ->quality(82)
            ->performOnCollections('hero');
    }

    /**
     * Returnt de hero-URL voor een route. Probeert eerst de eigen `hero`-collectie,
     * valt anders terug op de eerste-waypoint-galleryfoto. Returnt null als beide ontbreken
     * (caller toont placeholder).
     *
     * Sinds 5.3.a delen Route.hero en Location.gallery dezelfde conversienamen
     * (thumb/medium/large), dus de fallback levert nu ook een geschaalde WebP
     * i.p.v. het origineel.
     */
    public function displayHeroUrl(string $conversion = 'medium'): ?string
    {
        $own = $this->getFirstMediaUrl('hero', $conversion);
        if ($own !== '') {
            return $own;
        }

        $firstLocation = $this->locations()->first();
        if ($firstLocation) {
            $fallback = $firstLocation->getFirstMediaUrl('gallery', $conversion);
            if ($fallback !== '') {
                return $fallback;
            }
        }

        return null;
    }

    public function scopeOrderedByTravelDate(Builder $query): Builder
    {
        return $query->orderByDesc('travel_date');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Single-record-variant van scopePublished() (F5-77-patroon).
     * Zelfde drie condities, voor de 404-check op de publieke detail-route.
     */
    public function isPublished(): bool
    {
        return $this->is_published
            && $this->published_at !== null
            && $this->published_at <= now();
    }
}
