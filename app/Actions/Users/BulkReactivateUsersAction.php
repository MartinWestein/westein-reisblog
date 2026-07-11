<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class BulkReactivateUsersAction
{
    /**
     * Reactiveer meerdere users in één transactie.
     *
     * F4-U22: silent-skip op reeds-actieve users.
     * F4-U20: reactivate reset zowel deactivated_at als deactivation_reason.
     *
     * @param  array<int>  $userIds
     * @return int Aantal daadwerkelijk gereactiveerde users
     */
    public function execute(array $userIds): int
    {
        return DB::transaction(function () use ($userIds) {
            $affected = 0;

            $users = User::query()
                ->whereIn('id', $userIds)
                ->whereNotNull('deactivated_at')
                ->get();

            foreach ($users as $user) {
                $user->update([
                    'deactivated_at' => null,
                    'deactivation_reason' => null,
                ]);
                $affected++;
            }

            return $affected;
        });
    }
}
