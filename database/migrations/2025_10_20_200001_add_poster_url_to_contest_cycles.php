<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contest_cycles', function (Blueprint $table) {
            $table->string('poster_url', 500)->nullable()->after('theme_text');
        });
    }

    public function down(): void
    {
        Schema::table('contest_cycles', function (Blueprint $table) {
            $table->dropColumn('poster_url');
        });
    }
};

