<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->date('travel_date')->nullable();
            $table->timestamps();

            $table->index('destination_id');
            $table->index('travel_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
