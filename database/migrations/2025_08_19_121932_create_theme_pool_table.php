<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('theme_pools', function (Blueprint $table) {
            $table->id();
            // categories: csd = "CU si DESPRE", it, artisti, genuri
            $table->enum('category', ['csd', 'it', 'artisti', 'genuri']);
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_pools');
    }
};
