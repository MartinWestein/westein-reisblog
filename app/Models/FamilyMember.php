<?php

namespace App\Models;

use Database\Factories\FamilyMemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class FamilyMember extends Model implements HasMedia
{
    /** @use HasFactory<FamilyMemberFactory> */
    use HasFactory;

    use HasSlug;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'role',
        'bio',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('portrait')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp-600')
            ->format('webp')
            ->quality(82)
            ->fit(Fit::Max, 600, 600)
            ->performOnCollections('portrait')
            ->queued();

        $this->addMediaConversion('webp-300')
            ->format('webp')
            ->quality(82)
            ->fit(Fit::Max, 300, 300)
            ->performOnCollections('portrait')
            ->queued();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Deterministische accent-kleur op basis van id, voor initialen-avatar.
     * Rouleert: perzik → salie → rosé.
     */
    public function accentColor(): string
    {
        $palette = ['#E8A87C', '#41B3A3', '#C38D9E'];

        return $palette[crc32((string) $this->id) % count($palette)];
    }

    /**
     * Initialen voor avatar-fallback (max 2 letters).
     */
    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));

        if (empty($parts)) {
            return '?';
        }

        if (count($parts) === 1) {
            return mb_strtoupper(mb_substr($parts[0], 0, 2));
        }

        return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr(end($parts), 0, 1));
    }
}
