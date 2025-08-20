<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            // drop old FK → competition_themes
            $table->dropForeign(['theme_id']); // name is songs_theme_id_foreign in your error

            // re-add FK → contest_themes
            $table->foreign('theme_id')
                  ->references('id')->on('contest_themes')
                  ->nullOnDelete(); // same behavior as before
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropForeign(['theme_id']);
            $table->foreign('theme_id')
                  ->references('id')->on('competition_themes')
                  ->nullOnDelete();
        });
    }
};
