<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4 user-aanpassingen:
     *  - drop avatar_path (vervangen door Media Library 'avatar' collectie in Stap 3.3)
     *  - add deactivated_at (NULL = actief, niet-NULL = gedeactiveerd)
     *  - add deactivation_reason (optionele toelichting voor admin)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_path');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('deactivated_at')->nullable()->after('social_links');
            $table->text('deactivation_reason')->nullable()->after('deactivated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['deactivated_at', 'deactivation_reason']);
            $table->string('avatar_path')->nullable()->after('bio');
        });
    }
};
