<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ensure DB default is utf8mb4
        DB::statement("ALTER DATABASE `".env('DB_DATABASE')."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Only alter tables that actually exist
        $candidates = ['forum_threads','forum_replies','forum_likes'];

        foreach ($candidates as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
        }
    }


    public function down(): void
    {
        // Optional: no-op or revert to previous collation if you really need it
    }
};
