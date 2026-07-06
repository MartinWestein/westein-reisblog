<?php

namespace App\Actions\Trash;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class BulkRestoreTrashItemsAction
{
    public function __construct(private readonly RestoreTrashItemAction $singleAction) {}

    /**
     * @param  array<int, array{type: string, id: int}>  $items
     */
    public function execute(array $items): BulkRestoreResult
    {
        return DB::transaction(function () use ($items) {
            $primaryCount = 0;
            $ancestorCount = 0;
            $failedCount = 0;
            $alreadyRestored = [];

            foreach ($items as $item) {
                try {
                    $result = $this->singleAction->execute($item['type'], $item['id']);
                } catch (ModelNotFoundException) {
                    $failedCount++;

                    continue;
                }

                foreach ($result->restored as $restored) {
                    $key = "{$restored['type']}:{$restored['title']}";
                    $isPrimary = $restored === $result->primary();

                    if ($isPrimary) {
                        $primaryCount++;

                        continue;
                    }

                    if (! in_array($key, $alreadyRestored, true)) {
                        $ancestorCount++;
                        $alreadyRestored[] = $key;
                    }
                }
            }

            return new BulkRestoreResult(
                primaryCount: $primaryCount,
                ancestorCount: $ancestorCount,
                failedCount: $failedCount,
            );
        });
    }
}
