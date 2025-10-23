<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop any FK that might still reference `tiebreaks` (paranoid)
        $db = DB::getDatabaseName();
        $fks = DB::select("
            SELECT TABLE_NAME AS tbl, CONSTRAINT_NAME AS fkname
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME = 'tiebreaks'
        ", [$db]);
        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `{$fk->tbl}` DROP FOREIGN KEY `{$fk->fkname}`");
        }

        // Drop votes.tiebreak_id if it exists (no index drops)
        if (Schema::hasColumn('votes', 'tiebreak_id')) {
            DB::statement("ALTER TABLE `votes` DROP COLUMN `tiebreak_id`");
        }

        // Hard drop table with FK checks disabled
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('DROP TABLE IF EXISTS `tiebreaks`');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // no-op (feature removed)
    }
};
