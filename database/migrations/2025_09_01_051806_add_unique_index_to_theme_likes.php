<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('theme_likes', function (Blueprint $table) {
            // one like per user per target
            $table->unique(['user_id','likeable_type','likeable_id'], 'uniq_theme_likes_user_target');
            // optional: speeds up counts by target
            $table->index(['likeable_type','likeable_id'], 'idx_theme_likes_target');
        });
    }

    public function down(): void
    {
        Schema::table('theme_likes', function (Blueprint $table) {
            $table->dropUnique('uniq_theme_likes_user_target');
            $table->dropIndex('idx_theme_likes_target');
        });
    }
};
