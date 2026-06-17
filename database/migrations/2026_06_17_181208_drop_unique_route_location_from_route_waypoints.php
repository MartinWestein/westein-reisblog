<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_waypoints', function (Blueprint $table) {
            // Fase 3 voegde dit toe; in 4.8 besloten dat revisits toegestaan zijn.
            $table->dropUnique('route_waypoints_route_id_location_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('route_waypoints', function (Blueprint $table) {
            $table->unique(['route_id', 'location_id']);
        });
    }
};
