<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('theme_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Polymorphic target (ThemePool or ContestTheme)
            $table->morphs('likeable'); // creates likeable_type (string) + likeable_id (UBIGINT) + index

            $table->timestamps();

            // One like per user per item
            $table->unique(['user_id', 'likeable_type', 'likeable_id'], 'uniq_theme_likes_user_likeable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_likes');
    }
};
