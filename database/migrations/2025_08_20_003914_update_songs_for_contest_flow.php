<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            // Ensure competition_date exists and is indexed
            if (!Schema::hasColumn('songs', 'competition_date')) {
                $table->date('competition_date')->index()->after('title');
            } else {
                $table->date('competition_date')->change();
                $table->index('competition_date');
            }

            // Ensure votes exists (int) and defaults to 0
            if (!Schema::hasColumn('songs', 'votes')) {
                $table->integer('votes')->default(0)->after('competition_date');
            } else {
                $table->integer('votes')->default(0)->change();
            }

            // Ensure is_winner exists (bool) and defaults to false
            if (!Schema::hasColumn('songs', 'is_winner')) {
                $table->boolean('is_winner')->default(false)->after('votes');
            } else {
                $table->boolean('is_winner')->default(false)->change();
            }

            // Ensure theme_id exists and FK → contest_themes.id (nullable, null on delete)
            if (!Schema::hasColumn('songs', 'theme_id')) {
                $table->unsignedBigInteger('theme_id')->nullable()->after('is_winner');
                $table->foreign('theme_id')
                      ->references('id')->on('contest_themes')
                      ->nullOnDelete();
            } else {
                // make sure it's unsigned big int + nullable
                $table->unsignedBigInteger('theme_id')->nullable()->change();
                // drop any existing FK then recreate clean
                try { $table->dropForeign(['theme_id']); } catch (\Throwable $e) {}
                $table->foreign('theme_id')
                      ->references('id')->on('contest_themes')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            // roll back FK safely
            try { $table->dropForeign(['theme_id']); } catch (\Throwable $e) {}
            // Optional: comment out the drops if you want to keep columns on rollback
            // $table->dropColumn(['theme_id', 'is_winner', 'votes', 'competition_date']);
        });
    }
};
