<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            if (!Schema::hasColumn('songs', 'youtube_id')) {
                // 11-char canonical YouTube ID
                $table->string('youtube_id', 11)->nullable()->after('youtube_url');
            }
        });

        // Backfill youtube_id for existing rows (best-effort regex in PHP)
        $rows = DB::table('songs')->whereNull('youtube_id')->select('id','youtube_url')->get();
        foreach ($rows as $r) {
            $id = self::ytId((string)$r->youtube_url);
            if ($id) {
                DB::table('songs')->where('id', $r->id)->update(['youtube_id' => $id]);
            }
        }

        // Unique per cycle: a video can only appear once in a cycle
        // (MySQL allows multiple NULLs; OK for legacy rows)
        $exists = DB::selectOne("
            SELECT 1 AS x FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name   = 'songs'
              AND index_name   = 'songs_cycle_youtube_unique'
            LIMIT 1
        ");
        if (!$exists) {
            DB::statement('ALTER TABLE `songs` ADD UNIQUE INDEX `songs_cycle_youtube_unique` (`cycle_id`, `youtube_id`)');
        }
    }

    public function down(): void
    {
        // Drop unique if exists
        $exists = DB::selectOne("
            SELECT 1 AS x FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name   = 'songs'
              AND index_name   = 'songs_cycle_youtube_unique'
            LIMIT 1
        ");
        if ($exists) {
            DB::statement('ALTER TABLE `songs` DROP INDEX `songs_cycle_youtube_unique`');
        }

        if (Schema::hasColumn('songs', 'youtube_id')) {
            Schema::table('songs', function (Blueprint $table) {
                $table->dropColumn('youtube_id');
            });
        }
    }

    // minimal copy of your controller helper
    private static function ytId(string $url): ?string
    {
        $url = trim($url);
        if (preg_match('~youtu\.be/([0-9A-Za-z_-]{11})~', $url, $m)) return $m[1];
        if (preg_match('~(?:v=|/embed/|/v/)([0-9A-Za-z_-]{11})~', $url, $m)) return $m[1];
        if (preg_match('~([0-9A-Za-z_-]{11})~', $url, $m)) return $m[1];
        return null;
    }
};
