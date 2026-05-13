<?php

namespace App\Models;

use App\Models\Concerns\RegistersMediaConversions;
use Database\Factories\DestinationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Destination extends Model implements HasMedia
{
    /** @use HasFactory<DestinationFactory> */
    use HasFactory;

    use HasSlug;
    use InteractsWithMedia;
    use RegistersMediaConversions;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'country_code',
    ];

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

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Hero
        $this->registerWebpConversion('medium', 1200, $media, 'hero');
        $this->registerWebpConversion('large', 2400, $media, 'hero');

        // Gallery
        $this->registerWebpConversion('thumb', 400, $media, 'gallery');
        $this->registerWebpConversion('medium', 1200, $media, 'gallery');
        $this->registerWebpConversion('large', 2400, $media, 'gallery');
    }
}
