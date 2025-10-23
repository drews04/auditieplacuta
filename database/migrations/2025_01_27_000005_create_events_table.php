<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->string('slug')->unique();
            $table->date('event_date')->nullable();
            $table->string('poster_path');
            $table->text('body')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->index(['created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
};
