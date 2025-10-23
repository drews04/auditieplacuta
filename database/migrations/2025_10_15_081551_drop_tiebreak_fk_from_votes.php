<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // No-op: tiebreak column/table already removed on this environment.
        // We keep this migration file so "migrate" stays happy.
        return;
    }

    public function down(): void
    {
        // Intentionally empty – feature deprecated.
    }
};
