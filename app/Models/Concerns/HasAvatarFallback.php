<?php

namespace App\Models\Concerns;

trait HasAvatarFallback
{
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
