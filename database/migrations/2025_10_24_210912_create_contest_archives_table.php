<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create contest_archives table
     * 
     * This table stores SNAPSHOTS of completed cycles so they can be browsed
     * historically without being affected by future data changes/deletions.
     */
    public function up(): void
    {
        Schema::create('contest_archives', function (Blueprint $table) {
            $table->id();
            
            // Core identifiers
            $table->unsignedBigInteger('cycle_id');
            $table->unsignedBigInteger('theme_id')->nullable();
            
            // Theme snapshot (frozen at time of archiving)
            $table->string('theme_name', 255);
            $table->string('theme_category', 50);
            $table->integer('theme_likes_count')->default(0);
            
            // Winner snapshot (frozen at time of archiving)
            $table->unsignedBigInteger('winner_user_id');
            $table->unsignedBigInteger('winner_song_id');
            $table->string('winner_name', 255);
            $table->string('winner_photo_url', 500)->nullable();
            $table->string('winner_song_title', 500);
            $table->string('winner_song_url', 500);
            $table->integer('winner_votes');
            $table->integer('winner_points');
            
            // Cycle metadata
            $table->string('poster_url', 500)->nullable();
            $table->timestamp('vote_end_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            
            // Full ranking data (JSON snapshot)
            $table->json('ranking_data');
            
            $table->timestamps();
            
            // Indexes for fast navigation and queries
            $table->index('cycle_id');
            $table->index('winner_user_id');
            $table->index('vote_end_at');
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_archives');
    }
};
