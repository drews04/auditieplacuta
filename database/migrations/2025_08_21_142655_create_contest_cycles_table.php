<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contest_cycles', function (Blueprint $table) {
            $table->id();

            // Admin presses Start → these four timestamps define the 48h belt
            $table->dateTime('start_at');          // when submissions open (now, Europe/Bucharest)
            $table->dateTime('submit_end_at');     // start_at + 24h (submissions close)
            $table->dateTime('vote_start_at');     // == submit_end_at
            $table->dateTime('vote_end_at');       // vote_start_at + 24h

            // Theme chosen at Start
            $table->string('theme_text');

            // Winner of THIS cycle (decided at end of voting)
            $table->unsignedBigInteger('winner_song_id')->nullable();
            $table->unsignedBigInteger('winner_user_id')->nullable();
            $table->dateTime('winner_decided_at')->nullable();

            $table->timestamps();

            // Helpful indexes
            $table->index('start_at');
            $table->index(['submit_end_at', 'vote_start_at', 'vote_end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_cycles');
    }
};
