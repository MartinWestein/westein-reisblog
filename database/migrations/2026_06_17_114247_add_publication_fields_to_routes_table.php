<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('travel_date');
            $table->timestamp('published_at')->nullable()->after('is_published');

            // Composite voor de `published()`-scope (filtert op beide)
            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropIndex(['is_published', 'published_at']);
            $table->dropColumn(['is_published', 'published_at']);
        });
    }
};
