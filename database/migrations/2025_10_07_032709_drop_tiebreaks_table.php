<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tiebreaks')) {
            // Drop any foreign keys owned by tiebreaks (works if you know their names)
            // Example names—adjust to your schema or discover them with the SQL above.
            try { DB::statement('ALTER TABLE `tiebreaks` DROP FOREIGN KEY `tiebreaks_song_id_foreign`'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE `tiebreaks` DROP FOREIGN KEY `tiebreaks_user_id_foreign`'); } catch (\Throwable $e) {}
            // …add more DROP FOREIGN KEY lines as needed

            Schema::drop('tiebreaks');
        }

        // Also drop any referencing columns you might still have elsewhere (rare now):
        if (Schema::hasTable('votes') && Schema::hasColumn('votes', 'tiebreak_id')) {
            Schema::table('votes', function (Blueprint $table) {
                $table->dropForeign(['tiebreak_id']);
                $table->dropColumn('tiebreak_id');
            });
        }
        if (Schema::hasTable('winners') && Schema::hasColumn('winners', 'tiebreak_id')) {
            Schema::table('winners', function (Blueprint $table) {
                $table->dropForeign(['tiebreak_id']);
                $table->dropColumn('tiebreak_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tiebreaks')) {
            Schema::create('tiebreaks', function (Blueprint $table) {
                $table->id();
                $table->date('contest_date')->nullable();
                $table->dateTime('starts_at')->nullable();
                $table->dateTime('ends_at')->nullable();
                $table->json('song_ids')->nullable();
                $table->boolean('resolved')->default(false);
                $table->timestamps();
            });
        }
        // (re-adding any FKs is optional; we’re retiring tiebreaks anyway)
    }
};
