<?php

namespace App\Actions\Trash;

use App\Models\Destination;
use App\Models\Location;
use App\Models\Page;
use App\Models\Post;
use App\Models\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class RestoreTrashItemAction
{
    public function execute(string $type, int $id): RestoreResult
    {
        $model = $this->findTrashed($type, $id);

        $chain = [];
        $this->collectAncestors($model, $chain);
        $chain[] = $this->toChainEntry($type, $model);

        return DB::transaction(function () use ($chain) {
            $restored = [];
            foreach ($chain as $entry) {
                $entry['model']->restore();
                $restored[] = [
                    'type' => $entry['type'],
                    'type_label' => $entry['type_label'],
                    'title' => $entry['title'],
                ];
            }

            return new RestoreResult($restored);
        });
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
     * Verzamel soft-deleted ancestors van $model in $chain (top-down volgorde).
     * Post → Location → Destination.
     */
    private function collectAncestors(Model $model, array &$chain): void
    {
        if ($model instanceof Post) {
            if ($model->location_id) {
                $location = Location::withTrashed()->find($model->location_id);
                if ($location && $location->trashed()) {
                    $this->collectAncestors($location, $chain);
                    $chain[] = $this->toChainEntry('location', $location);
                }
            }
            if ($model->destination_id) {
                $destination = Destination::withTrashed()->find($model->destination_id);
                if ($destination && $destination->trashed() && ! $this->alreadyInChain($destination, $chain)) {
                    $chain[] = $this->toChainEntry('destination', $destination);
                }
            }

            return;
        }

        if ($model instanceof Location) {
            if ($model->destination_id) {
                $destination = Destination::withTrashed()->find($model->destination_id);
                if ($destination && $destination->trashed()) {
                    $chain[] = $this->toChainEntry('destination', $destination);
                }
            }
        }
    }

    private function alreadyInChain(Model $model, array $chain): bool
    {
        foreach ($chain as $entry) {
            if ($entry['model']::class === $model::class && $entry['model']->getKey() === $model->getKey()) {
                return true;
            }
        }

        return false;
    }

    private function toChainEntry(string $type, Model $model): array
    {
        return [
            'type' => $type,
            'type_label' => $this->typeLabel($type),
            'title' => $this->titleFor($model),
            'model' => $model,
        ];
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'post' => 'Post',
            'destination' => 'Bestemming',
            'location' => 'Locatie',
            'route' => 'Route',
            'page' => 'Pagina',
        };
    }

    private function titleFor(Model $model): string
    {
        return match (true) {
            $model instanceof Post, $model instanceof Page => $model->title,
            $model instanceof Destination, $model instanceof Location, $model instanceof Route => $model->name,
        };
    }
}
