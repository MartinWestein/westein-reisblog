<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Trash\TrashBrowser;
use App\Actions\Trash\RestoreTrashItemAction;
use App\Actions\Trash\ForceDeleteTrashItemAction;
use App\Actions\Trash\BulkRestoreTrashItemsAction;
use App\Http\Requests\Admin\Trash\BulkRestoreRequest;
use RuntimeException;
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

    public function restore(string $type, int $id, RestoreTrashItemAction $action)
    {
        try {
            $result = $action->execute($type, $id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            abort(404);
        }

        session()->flash('success', $result->flashMessage());

        return redirect()->route('admin.trash.index');
    }

    public function forceDelete(string $type, int $id, ForceDeleteTrashItemAction $action)
    {
        try {
            $action->execute($type, $id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            abort(404);
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->route('admin.trash.index');
        }

        session()->flash('success', __('Item definitief verwijderd.'));

        return redirect()->route('admin.trash.index');
    }

    public function bulkRestore(BulkRestoreRequest $request, BulkRestoreTrashItemsAction $action)
    {
        $result = $action->execute($request->validated('items'));

        session()->flash('success', $result->flashMessage());

        return redirect()->route('admin.trash.index');
    }

}
