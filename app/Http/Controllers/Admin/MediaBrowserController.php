<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkDeleteMediaRequest;
use App\Http\Requests\Admin\MediaBrowserIndexRequest;
use App\Services\Media\MediaQueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaBrowserController extends Controller
{
    private const PER_PAGE = 24;

    public function index(MediaBrowserIndexRequest $request)
    {
        $media = (new MediaQueryBuilder)
            ->filterCollection($request->input('collection'))
            ->filterOwnerType($request->input('owner_type'))
            ->search($request->input('q'))
            ->sort($request->sortColumn(), $request->sortDirection())
            ->builder()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.media.index', [
            'media' => $media,
            'collection' => $request->input('collection'),
            'ownerType' => $request->input('owner_type'),
            'q' => $request->input('q'),
            'sort' => $request->sortColumn(),
            'direction' => $request->sortDirection(),
            'ownerTypes' => array_keys(config('westein.browsable_media_owners')),
        ]);
    }

    public function bulkDelete(BulkDeleteMediaRequest $request)
    {
        $mediaItems = Media::whereIn('id', $request->input('ids'))->with('model')->get();

        DB::transaction(function () use ($mediaItems) {
            foreach ($mediaItems as $media) {
                $this->authorize('update', $media->model);
                $media->delete();
            }
        });

        return redirect()
            ->route('admin.media.index', $request->only(['collection', 'owner_type', 'q', 'sort', 'direction', 'page']))
            ->with('success', $mediaItems->count().' '.Str::plural('item', $mediaItems->count()).' verwijderd.');
    }
}
