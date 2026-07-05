<?php

namespace App\Actions\Trash;

use App\Models\Destination;
use App\Models\Location;
use App\Models\Page;
use App\Models\Post;
use App\Models\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;

class ForceDeleteTrashItemAction
{
    public function execute(string $type, int $id): void
    {
        $model = $this->findTrashed($type, $id);

        if ($blocked = $this->blockingReason($type, $model)) {
            throw new RuntimeException($blocked);
        }

        $model->forceDelete();
    }

    private function findTrashed(string $type, int $id): Model
    {
        $model = match ($type) {
            'post' => Post::onlyTrashed()->find($id),
            'destination' => Destination::onlyTrashed()->find($id),
            'location' => Location::onlyTrashed()->find($id),
            'route' => Route::onlyTrashed()->find($id),
            'page' => Page::onlyTrashed()->find($id),
            default => null,
        };

        if (! $model) {
            throw new ModelNotFoundException("Trash-item niet gevonden: {$type} #{$id}");
        }

        return $model;
    }

    /**
     * Geeft de reden waarom force-delete geblokkeerd wordt, of null als toegestaan.
     * T4-B: alleen Destination → Locations blokkeert; overige types kennen geen
     * zelfstandige-content-children (pivot/media/nullOnDelete-relaties tellen niet).
     */
    private function blockingReason(string $type, Model $model): ?string
    {
        if ($type !== 'destination') {
            return null;
        }

        $count = Location::withTrashed()->where('destination_id', $model->getKey())->count();

        if ($count === 0) {
            return null;
        }

        return sprintf(
            'Kan niet definitief verwijderen: %d %s hangt hieronder. Verwijder of herstel die eerst.',
            $count,
            $count === 1 ? 'locatie' : 'locaties'
        );
    }
}
