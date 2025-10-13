<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Artists
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Categories (free-form)
        Schema::create('release_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Releases
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->date('release_date')->index();
            $table->string('week_key', 8)->index(); // e.g. 2025W39
            $table->string('type')->default('album'); // album|single|ep
            $table->string('cover_path')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_highlight')->default(false)->index();
            $table->timestamps();
        });

        // Pivots
        Schema::create('artist_release', function (Blueprint $table) {
            $table->unsignedBigInteger('artist_id');
            $table->unsignedBigInteger('release_id');
            $table->primary(['artist_id', 'release_id']);
            $table->foreign('artist_id')->references('id')->on('artists')->cascadeOnDelete();
            $table->foreign('release_id')->references('id')->on('releases')->cascadeOnDelete();
        });

        Schema::create('category_release', function (Blueprint $table) {
            $table->unsignedBigInteger('release_category_id');
            $table->unsignedBigInteger('release_id');
            $table->primary(['release_category_id', 'release_id'], 'cat_rel_pk');
            $table->foreign('release_category_id','cat_fk')->references('id')->on('release_categories')->cascadeOnDelete();
            $table->foreign('release_id')->references('id')->on('releases')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_release');
        Schema::dropIfExists('artist_release');
        Schema::dropIfExists('releases');
        Schema::dropIfExists('release_categories');
        Schema::dropIfExists('artists');
    }
};
