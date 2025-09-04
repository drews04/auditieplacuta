<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    // Ensure DB default is utf8mb4
    DB::statement("ALTER DATABASE `".env('DB_DATABASE')."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Your real forum tables
    $tables = [
        'forum_categories',
        'forum_threads',
        'forum_posts',   // replies are posts with parent_id
        'forum_views',
        'forum_likes',
    ];

    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            DB::statement("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utf8mb4', function (Blueprint $table) {
            //
        });
    }
};
