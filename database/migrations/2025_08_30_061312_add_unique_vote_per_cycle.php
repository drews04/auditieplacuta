<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            // Hard guarantee: one vote per user per cycle
            $table->unique(['user_id', 'cycle_id'], 'votes_unique_user_cycle');

            // Nice-to-have indexes for speed
            $table->index(['cycle_id', 'song_id'], 'votes_cycle_song_idx');
            $table->index('user_id', 'votes_user_idx');
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropUnique('votes_unique_user_cycle');
            $table->dropIndex('votes_cycle_song_idx');
            $table->dropIndex('votes_user_idx');
        });
    }
};
