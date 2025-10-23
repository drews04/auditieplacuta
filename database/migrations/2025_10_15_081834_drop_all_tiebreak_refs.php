<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $db = DB::getDatabaseName();

        // Find every FK that references the `tiebreaks` table (any column)
        $fks = DB::select("
            SELECT TABLE_NAME AS tbl, CONSTRAINT_NAME AS fkname
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND REFERENCED_TABLE_NAME = 'tiebreaks'
        ", [$db]);

        // Drop them one by one
        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `{$fk->tbl}` DROP FOREIGN KEY `{$fk->fkname}`");
        }

        // Also drop any 'tiebreak_id' column that may still exist
        if (Schema::hasColumn('votes', 'tiebreak_id')) {
            Schema::table('votes', function (Blueprint $table) {
                try { $table->dropIndex(['tiebreak_id']); } catch (\Throwable $e) {}
                try { $table->dropUnique(['tiebreak_id']); } catch (\Throwable $e) {}
                $table->dropColumn('tiebreak_id');
            });
        }

        // Finally drop the table
        Schema::dropIfExists('tiebreaks');
    }

    public function down(): void
    {
        // No rollback; tiebreaks is deprecated.
    }
};
