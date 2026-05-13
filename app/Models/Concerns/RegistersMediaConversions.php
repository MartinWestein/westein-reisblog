<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Centrale helper voor WebP-conversies op modellen met Spatie Media Library.
 *
 * Vereist dat de gebruikende class:
 *   - implements \Spatie\MediaLibrary\HasMedia
 *   - use \Spatie\MediaLibrary\InteractsWithMedia
 *
 * @mixin InteractsWithMedia
 * @mixin HasMedia
 */
trait RegistersMediaConversions
{
    /**
     * Registreer één WebP-conversie met opgegeven naam en breedte.
     * Hoogte schaalt automatisch mee (Fit::Max behoudt verhoudingen).
     */
    protected function registerWebpConversion(
        string $name,
        int $width,
        ?Media $media = null,
        ?string $collectionName = null,
    ): void {
        $conversion = $this->addMediaConversion($name)
            ->format('webp')
            ->fit(Fit::Max, $width, $width * 2)  // Hoogte max 2x breedte; voorkomt extreme verticale crops
            ->quality(82)
            ->queued();

        if ($collectionName !== null) {
            $conversion->performOnCollections($collectionName);
        }
    }
}
