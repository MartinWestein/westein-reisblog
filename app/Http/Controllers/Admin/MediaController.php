<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    /**
     * Resolve het eigenaarsmodel uit een client-type-string via de whitelist.
     */
    protected function resolveModel(string $type, int|string $id): HasMedia
    {
        $map = config('westein.gallery_models', []);

        if (! array_key_exists($type, $map)) {
            throw ValidationException::withMessages([
                'model_type' => 'Onbekend modeltype.',
            ]);
        }

        /** @var class-string<Model&HasMedia> $class */
        $class = $map[$type];

        return $class::findOrFail($id);
    }

    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model_type' => ['required', 'string'],
            'model_id' => ['required', 'integer'],
            'collection' => ['required', 'string', 'in:gallery'],
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['image', 'mimes:jpeg,png,webp', 'max:16384'],
        ]);

        $model = $this->resolveModel($validated['model_type'], $validated['model_id']);

        $this->authorize('update', $model);

        $added = [];
        foreach ($request->file('files') as $file) {
            $media = $model->addMedia($file)->toMediaCollection($validated['collection']);
            $added[] = $this->transformMedia($media);
        }

        return response()->json(['media' => $added], 201);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $mediaItems = Media::whereIn('id', $validated['ids'])->get();

        if ($mediaItems->count() !== count($validated['ids'])) {
            throw ValidationException::withMessages(['ids' => 'Onbekende media.']);
        }

        // Alle media moeten bij hetzelfde eigenaarsmodel horen.
        $modelTypes = $mediaItems->pluck('model_type')->unique();
        $modelIds = $mediaItems->pluck('model_id')->unique();

        if ($modelTypes->count() !== 1 || $modelIds->count() !== 1) {
            throw ValidationException::withMessages(['ids' => 'Media horen niet bij hetzelfde item.']);
        }

        $owner = $mediaItems->first()->model;

        $this->authorize('update', $owner);

        Media::setNewOrder($validated['ids']);

        return response()->json(['status' => 'ok']);
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->authorize('update', $media->model);

        $media->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Compacte media-representatie voor de client.
     */
    protected function transformMedia(Media $media): array
    {
        return [
            'id' => $media->id,
            'url' => $media->hasGeneratedConversion('thumb')
                ? $media->getUrl('thumb')
                : $media->getUrl(),
            'name' => $media->file_name,
        ];
    }
}
