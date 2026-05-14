<?php

namespace App\Models;

use Database\Factories\RouteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Route extends Model
{
    /** @use HasFactory<RouteFactory> */
    use HasFactory;

    use HasSlug;

    protected $fillable = [
        'destination_id',
        'name',
        'slug',
        'description',
        'travel_date',
    ];

    protected function casts(): array
    {
        return [
            'travel_date' => 'date',
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

    public function scopeOrderedByTravelDate(Builder $query): Builder
    {
        return $query->orderByDesc('travel_date');
    }
}
