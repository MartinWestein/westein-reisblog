<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voegt soft deletes toe aan de vijf kern-content tabellen.
     * Comments, Users, Subscribers krijgen bewust geen soft deletes:
     *  - Comments: heeft al status-flow (pending/approved/rejected/spam)
     *  - Users: AVG (recht op vergetelheid) → harde delete + deactivated_at
     *  - Subscribers: heeft al confirmed_at + unsubscribe_token flow
     */
    public function up(): void
    {
        foreach (['posts', 'destinations', 'locations', 'routes', 'pages'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (['posts', 'destinations', 'locations', 'routes', 'pages'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropSoftDeletes();
            });
        }
    }
};
