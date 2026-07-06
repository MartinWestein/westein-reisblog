<?php

namespace App\Actions\Trash;

class BulkRestoreResult
{
    /**
     * @param  int  $primaryCount  Aantal expliciet-geselecteerde items dat is hersteld
     * @param  int  $ancestorCount  Aantal ancestors dat cascade-omhoog is meegenomen (uniek)
     * @param  int  $failedCount  Aantal items dat niet meer gevonden werd (silent skip)
     */
    public function __construct(
        public readonly int $primaryCount,
        public readonly int $ancestorCount,
        public readonly int $failedCount,
    ) {}

    public function flashMessage(): string
    {
        $noun = $this->primaryCount === 1 ? 'item' : 'items';
        $primaryPart = sprintf('%d %s hersteld', $this->primaryCount, $noun);

        $parts = [$primaryPart];

        if ($this->ancestorCount > 0) {
            $ancestorNoun = $this->ancestorCount === 1 ? 'ouder' : 'ouders';
            $parts[] = sprintf('%d %s meegenomen', $this->ancestorCount, $ancestorNoun);
        }

        if ($this->failedCount > 0) {
            $failedNoun = $this->failedCount === 1 ? 'item' : 'items';
            $parts[] = sprintf('%d %s overgeslagen (niet meer gevonden)', $this->failedCount, $failedNoun);
        }

        return implode('. ', $parts).'.';
    }
}
