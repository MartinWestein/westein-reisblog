<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\Media\MediaQueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaPickerController extends Controller
{
    private const PER_PAGE = 24;

    public function index(Request $request): JsonResponse
    {
        // Wie de rich-editor mag bereiken, mag bladeren. PostPolicy.create
        // is true voor admin, editor en auteur.
        $this->authorize('create', Post::class);

        $request->validate([
            'search' => 'nullable|string|max:100',
            'collection' => ['nullable', 'string', Rule::in(MediaQueryBuilder::ALLOWED_COLLECTIONS)],
            'cursor' => 'nullable|string',
        ]);

        $builder = (new MediaQueryBuilder)
            ->filterCollection($request->input('collection'))
            ->search($request->input('search'));

        // Picker behoudt 4.6-gedrag: nieuwste eerst via id-desc + cursor.
        // (Geen sortable UI; sort()-helper niet nodig hier.)
        $page = $builder->builder()
            ->orderByDesc('id')
            ->cursorPaginate(self::PER_PAGE);

        return response()->json([
            'items' => collect($page->items())->map(fn (Media $m) => $this->formatItem($m))->values(),
            'next_cursor' => $page->nextCursor()?->encode(),
        ]);
    }

    private function formatItem(Media $media): array
    {
        return [
            'id' => $media->id,
            'url' => $media->hasGeneratedConversion('medium')
                ? $media->getUrl('medium')
                : $media->getUrl(),
            'thumb_url' => $media->hasGeneratedConversion('thumb')
                ? $media->getUrl('thumb')
                : $media->getUrl(),
            'alt' => (string) $media->getCustomProperty('alt', ''),
            'context' => MediaQueryBuilder::contextLabel($media),
        ];
    }
}
