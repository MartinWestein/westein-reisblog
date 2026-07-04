<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Trash\TrashBrowser;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index(Request $request, TrashBrowser $browser)
    {
        $type = $request->string('type')->toString();
        $type = in_array($type, array_keys(TrashBrowser::TYPES), true) ? $type : null;

        $items = $browser->browse(
            type: $type,
            perPage: 25,
            page: $request->integer('page', 1) ?: 1,
        );

        return view('admin.trash.index', [
            'items' => $items,
            'type' => $type,
            'types' => TrashBrowser::TYPES,
        ]);
    }
}
