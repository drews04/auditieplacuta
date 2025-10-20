<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banned_songs', function (Blueprint $table) {
            $table->id();
            $table->string('youtube_id', 255)->unique();
            $table->string('song_title', 500)->nullable();
            $table->timestamp('banned_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banned_songs');
    }
};

