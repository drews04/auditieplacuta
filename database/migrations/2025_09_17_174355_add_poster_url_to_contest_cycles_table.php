<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('contest_cycles', function (Blueprint $t) {
        $t->string('poster_url', 2048)->nullable()->after('theme_text');
    });
}

public function down(): void
{
    Schema::table('contest_cycles', function (Blueprint $t) {
        $t->dropColumn('poster_url');
    });
}

};
