<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) If a proper class-type row already exists for (user, target),
        //    delete any short-type duplicates that would collide on update.
    
        // Contest collisions: keep the class row, delete the short-type ones
        DB::statement("
            DELETE bad FROM theme_likes AS bad
            JOIN theme_likes AS good
              ON good.user_id = bad.user_id
             AND good.likeable_id = bad.likeable_id
             AND good.likeable_type = 'App\\\\Models\\\\ContestTheme'
            WHERE bad.likeable_type IN ('contest','ContestTheme')
        ");
    
        // Pool collisions
        DB::statement("
            DELETE bad FROM theme_likes AS bad
            JOIN theme_likes AS good
              ON good.user_id = bad.user_id
             AND good.likeable_id = bad.likeable_id
             AND good.likeable_type = 'App\\\\Models\\\\ThemePool'
            WHERE bad.likeable_type IN ('pool','ThemePool')
        ");
    
        // 2) Normalize remaining short types to FULL class names
        DB::statement("
            UPDATE theme_likes
               SET likeable_type = 'App\\\\Models\\\\ContestTheme'
             WHERE likeable_type IN ('contest','ContestTheme','\\\\App\\\\Models\\\\ContestTheme')
        ");
        DB::statement("
            UPDATE theme_likes
               SET likeable_type = 'App\\\\Models\\\\ThemePool'
             WHERE likeable_type IN ('pool','ThemePool','\\\\App\\\\Models\\\\ThemePool')
        ");
    
        // 3) Deduplicate within the same (user, type, id) after normalization (keep newest id)
        DB::statement("
            DELETE a FROM theme_likes a
            JOIN theme_likes b
              ON a.user_id = b.user_id
             AND a.likeable_type = b.likeable_type
             AND a.likeable_id = b.likeable_id
             AND a.id < b.id
        ");
    
        // 4) Ensure unique index exists (safe if already there)
        $db = DB::getDatabaseName();
        $exists = DB::selectOne("
            SELECT COUNT(*) AS c
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'theme_likes' AND INDEX_NAME = 'uniq_theme_likes_user_likeable'
        ", [$db]);
        if (!$exists || (int)$exists->c === 0) {
            DB::statement("
                ALTER TABLE theme_likes
                  ADD UNIQUE KEY uniq_theme_likes_user_likeable (user_id, likeable_type, likeable_id)
            ");
        }
    }
    
    
    

    public function down(): void
    {
        Schema::table('theme_likes', function (Blueprint $table) {
            if ($this->hasUniqueIndex('theme_likes', 'uniq_theme_likes_user_target')) {
                $table->dropUnique('uniq_theme_likes_user_target');
            }
        });

        // Optional rollback of type normalization (not needed)
    }

    // Helper (works on MySQL/MariaDB)
    private function hasUniqueIndex(string $table, string $index): bool
    {
        $db = DB::getDatabaseName();
        $exists = DB::selectOne("
            SELECT COUNT(*) AS c
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?
        ", [$db, $table, $index]);

        return ($exists && (int)$exists->c > 0);
    }
};
