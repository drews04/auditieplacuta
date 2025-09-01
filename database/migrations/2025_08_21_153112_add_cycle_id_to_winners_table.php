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
        Schema::table('winners', function (Blueprint $table) {
            $table->unsignedBigInteger('cycle_id')->nullable()->after('id');
            $table->index('cycle_id');
            
            // Foreign key constraint (optional, can be enabled later)
            // $table->foreign('cycle_id')->references('id')->on('contest_cycles')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winners', function (Blueprint $table) {
            $table->dropIndex(['cycle_id']);
            $table->dropColumn('cycle_id');
        });
    }
};
