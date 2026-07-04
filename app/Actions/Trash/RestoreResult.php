<?php

namespace App\Actions\Trash;

class RestoreResult
{
    /**
     * @param array<int, array{type: string, type_label: string, title: string}> $restored
     *   Volgorde: ancestors eerst, primary (aangeklikt item) laatst.
     */
    public function __construct(public readonly array $restored)
    {
    }

    public function primary(): array
    {
        return $this->restored[array_key_last($this->restored)];
    }

    /** @return array<int, array{type: string, type_label: string, title: string}> */
    public function ancestors(): array
    {
        return array_slice($this->restored, 0, -1);
    }

    public function flashMessage(): string
    {
        $primary = $this->primary();

        if (count($this->restored) === 1) {
            return sprintf("'%s' hersteld.", $primary['title']);
        }

        $ancestors = collect($this->ancestors())
            ->map(fn ($a) => sprintf("'%s' (%s)", $a['title'], mb_strtolower($a['type_label'])))
            ->implode(', ');

        return sprintf(
            "'%s' hersteld. Ook %s teruggezet omdat die verwijderd waren.",
            $primary['title'],
            $ancestors
        );
    }
}
