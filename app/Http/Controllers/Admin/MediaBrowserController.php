<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaBrowserIndexRequest;
use App\Services\Media\MediaQueryBuilder;

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
}
