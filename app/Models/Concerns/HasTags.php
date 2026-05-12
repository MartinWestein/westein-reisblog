<?php

namespace App\Models\Concerns;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @mixin Model
 */
trait HasTags
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps();
    }

    /**
     * Sync tags by name. Creates missing tags on the fly.
     * Names worden lowercase + getrimd vóór opslag.
     *
     * @param  array<int, string>  $names
     */
    public function syncTagsByName(array $names): void
    {
        $ids = collect($names)
            ->map(fn (string $name) => mb_strtolower(trim($name)))
            ->filter()
            ->unique()
            ->map(fn (string $name) => Tag::firstOrCreate(['name' => $name])->id)
            ->all();

        $this->tags()->sync($ids);
    }
}
