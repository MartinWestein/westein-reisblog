<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaPickerController extends Controller
{
    /**
     * Projectbrede image-picker voor de TipTap rich-editor (stap 4.6).
     *
     * Browse-scope: alleen content-foto's. Avatar (User) + portrait
     * (FamilyMember) blijven buiten beeld.
     */
    private const ALLOWED_COLLECTIONS = ['gallery', 'hero', 'featured', 'inline_images'];

    private const PER_PAGE = 24;

    public function index(Request $request): JsonResponse
    {
        // Wie de rich-editor mag bereiken, mag bladeren. PostPolicy.create
        // is true voor admin, editor en auteur.
        $this->authorize('create', Post::class);

        $request->validate([
            'search' => 'nullable|string|max:100',
            'collection' => ['nullable', 'string', Rule::in(self::ALLOWED_COLLECTIONS)],
            'cursor' => 'nullable|string',
        ]);

        $query = Media::query()
            ->whereIn('collection_name', self::ALLOWED_COLLECTIONS)
            ->with('model')
            ->orderByDesc('id');

        if ($collection = $request->input('collection')) {
            $query->where('collection_name', $collection);
        }

        if ($search = $request->input('search')) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $page = $query->cursorPaginate(self::PER_PAGE);

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
            'context' => $this->contextLabel($media),
        ];
    }

    private function contextLabel(Media $media): string
    {
        $owner = $media->model;

        if (! $owner) {
            return 'Zonder eigenaar';
        }

        return match (class_basename($owner)) {
            'Destination' => 'Bestemming: '.($owner->name ?? '?'),
            'Location' => 'Locatie: '.($owner->destination?->name ?? '?').' → '.($owner->name ?? '?'),
            'Post' => 'Post: '.($owner->title ?? '?'),
            default => class_basename($owner),
        };
    }
}
