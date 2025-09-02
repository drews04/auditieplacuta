<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('forum_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id', 64)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->unique(['thread_id','user_id','session_id'], 'thread_user_session_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('forum_views');
    }
};
