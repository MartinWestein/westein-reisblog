<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class BulkDeactivateUsersAction
{
    /**
     * Deactiveer meerdere users in één transactie.
     *
     * F4-U22: silent-skip op reeds-gedeactiveerde users.
     * Guards (F4-U2 self, F4-U10 last-admin) worden in de Request afgehandeld
     * voordat deze action aangeroepen wordt.
     *
     * @param  array<int>  $userIds
     * @return int Aantal daadwerkelijk gedeactiveerde users
     */
    public function execute(array $userIds): int
    {
        return DB::transaction(function () use ($userIds) {
            $affected = 0;

            $users = User::query()
                ->whereIn('id', $userIds)
                ->whereNull('deactivated_at')
                ->get();

            foreach ($users as $user) {
                $user->update([
                    'deactivated_at' => now(),
                    'deactivation_reason' => null,
                ]);
                $affected++;
            }

            return $affected;
        });
    }
}
