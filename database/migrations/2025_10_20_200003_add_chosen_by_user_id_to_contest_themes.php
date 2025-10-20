<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contest_themes', function (Blueprint $table) {
            $table->unsignedBigInteger('chosen_by_user_id')->nullable()->after('name');
            $table->foreign('chosen_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('contest_themes', function (Blueprint $table) {
            $table->dropForeign(['chosen_by_user_id']);
            $table->dropColumn('chosen_by_user_id');
        });
    }
};

