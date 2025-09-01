<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->unsignedBigInteger('cycle_id')->nullable()->after('id');
            $table->index('cycle_id');
            // optional FK:
            // $table->foreign('cycle_id')->references('id')->on('contest_cycles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            // $table->dropForeign(['cycle_id']);
            $table->dropIndex(['cycle_id']);
            $table->dropColumn('cycle_id');
        });
    }
};
