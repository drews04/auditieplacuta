<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('forum_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('forum_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('body');
            $table->unsignedInteger('replies_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('last_posted_at')->nullable();
            $table->foreignId('last_post_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('pinned')->default(false);
            $table->boolean('locked')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['category_id','updated_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('forum_threads');
    }
};
