<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contest_themes', function (Blueprint $table) {
            $table->id();
            $table->date('contest_date')->unique();                 // day this theme applies to
            $table->foreignId('theme_pool_id')->constrained();      // picked from theme_pools
            $table->boolean('picked_by_winner')->default(false);    // false = random fallback
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_themes');
    }
};
