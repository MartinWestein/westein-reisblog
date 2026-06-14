<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostInlineImageController extends Controller
{
    /**
     * Upload een afbeelding naar de inline_images-collectie van een Post.
     *
     * Aangeroepen door de image-picker modal (stap 4.6) wanneer de gebruiker
     * de upload-tab gebruikt op een bestaande post. Op create is deze route
     * niet bereikbaar — de modal disabled de upload-tab tot de post is opgeslagen.
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,webp|max:16384', // 16 MB
            'alt' => 'nullable|string|max:255',
        ]);

        $alt = $validated['alt'] ?? '';

        $media = $post
            ->addMediaFromRequest('image')
            ->withCustomProperties(['alt' => $alt])
            ->toMediaCollection('inline_images');

        // Conversies zijn queued. Voor onmiddellijke render in de editor
        // gebruiken we de originele URL — Fase 5 schakelt naar 'medium' zodra
        // de conversie klaar is.
        return response()->json([
            'id' => $media->id,
            'url' => $media->getUrl(),
            'thumb_url' => $media->getUrl(),
            'alt' => $alt,
        ], 201);
    }
}
