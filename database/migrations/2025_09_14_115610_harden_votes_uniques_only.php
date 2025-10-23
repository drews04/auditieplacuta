<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // one vote per user per cycle
        $exists = DB::selectOne("
            SELECT 1 AS x
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name   = 'votes'
              AND index_name   = 'votes_user_cycle_unique'
            LIMIT 1
        ");
        if (!$exists) {
            DB::statement('ALTER TABLE `votes` ADD UNIQUE INDEX `votes_user_cycle_unique` (`user_id`, `cycle_id`)');
        }

        // one vote per user per tiebreak
        $exists = DB::selectOne("
            SELECT 1 AS x
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name   = 'votes'
              AND index_name   = 'votes_user_tiebreak_unique'
            LIMIT 1
        ");
        if (!$exists) {
            DB::statement('ALTER TABLE `votes` ADD UNIQUE INDEX `votes_user_tiebreak_unique` (`user_id`, `tiebreak_id`)');
        }
    }

    public function down(): void
    {
        $drop = function (string $idx) {
            $exists = DB::selectOne("
                SELECT 1 AS x
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name   = 'votes'
                  AND index_name   = ?
                LIMIT 1
            ", [$idx]);

            if ($exists) {
                DB::statement("ALTER TABLE `votes` DROP INDEX `{$idx}`");
            }
        };

        $drop('votes_user_cycle_unique');
        $drop('votes_user_tiebreak_unique');
    }
};
