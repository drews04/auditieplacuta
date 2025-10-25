<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add disqualification tracking to songs table
     * 
     * This allows:
     * 1. Auto-disqualification of past winners (they can upload but can't receive votes)
     * 2. Manual admin disqualification of any song
     */
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->boolean('is_disqualified')->default(false)->after('title');
            $table->string('disqualification_reason', 255)->nullable()->after('is_disqualified');
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropColumn(['is_disqualified', 'disqualification_reason']);
        });
    }
};
