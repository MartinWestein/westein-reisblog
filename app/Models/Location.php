<?php

namespace App\Models;

use App\Models\Concerns\RegistersMediaConversions;
use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Location extends Model implements HasMedia
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    use HasSlug;
    use InteractsWithMedia;
    use RegistersMediaConversions;

    protected $fillable = [
        'destination_id',
        'name',
        'slug',
        'description',
        'latitude',
        'longitude',
        'country_code',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
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

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function routeWaypoints(): HasMany
    {
        return $this->hasMany(RouteWaypoint::class);
    }

    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(Route::class, 'route_waypoints')
            ->withPivot(['order', 'notes'])
            ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerWebpConversion('thumb', 400, $media, 'gallery');
        $this->registerWebpConversion('medium', 1200, $media, 'gallery');
        $this->registerWebpConversion('large', 2400, $media, 'gallery');
    }
}
