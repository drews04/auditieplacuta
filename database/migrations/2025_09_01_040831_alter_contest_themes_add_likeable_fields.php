<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // A) contest_themes columns (idempotent)
        Schema::table('contest_themes', function (Blueprint $table) {
            if (!Schema::hasColumn('contest_themes', 'name')) {
                $table->string('name')->default('')->after('id');
            }
            if (!Schema::hasColumn('contest_themes', 'category')) {
                $table->string('category')->nullable()->after('name');
            }
            if (!Schema::hasColumn('contest_themes', 'active')) {
                $table->boolean('active')->default(true)->after('category');
            }
            if (!Schema::hasColumn('contest_themes', 'contest_date')) {
                $table->date('contest_date')->nullable()->after('active');
            }
        });

        // A+) Ensure theme_pool_id is NULLable (handles your 1364 error)
        if (Schema::hasColumn('contest_themes', 'theme_pool_id')) {
            // Drop FK if it exists (name-safe attempt)
            try {
                DB::statement('ALTER TABLE contest_themes DROP FOREIGN KEY contest_themes_theme_pool_id_foreign');
            } catch (\Throwable $e) {}
            // Make column NULLable
            DB::statement('ALTER TABLE contest_themes MODIFY theme_pool_id BIGINT UNSIGNED NULL');
            // (Re-adding the FK is optional and not needed for this backfill)
        }

        // B) contest_cycles must have contest_theme_id
        Schema::table('contest_cycles', function (Blueprint $table) {
            if (!Schema::hasColumn('contest_cycles', 'contest_theme_id')) {
                $table->unsignedBigInteger('contest_theme_id')->nullable()->after('theme_text');
                $table->index('contest_theme_id', 'contest_cycles_theme_id_idx');
            }
        });

        // C) Backfill — create/link a ContestTheme for cycles missing one.
        $cycles = DB::table('contest_cycles')
            ->whereNull('contest_theme_id')
            ->whereNotNull('theme_text')
            ->select('id', 'theme_text', 'start_at')
            ->get();

        foreach ($cycles as $c) {
            $raw  = (string) $c->theme_text;
            $name = trim($raw);
            $cat  = null;

            // Parse "CSD — Dinamita" ⇒ category="CSD", name="Dinamita"
            if (preg_match('/^\s*([^—-]+)\s*[—-]\s*(.+)$/u', $raw, $m)) {
                $cat  = trim($m[1] ?? '') ?: null;
                $name = trim($m[2] ?? $raw);
            }

            $contestDate = $c->start_at
                ? \Illuminate\Support\Carbon::parse($c->start_at)->toDateString()
                : now()->toDateString();

            // REUSE existing theme for that date to avoid UNIQUE violation
            $existing = DB::table('contest_themes')
                ->whereDate('contest_date', $contestDate)
                ->first();

            if ($existing) {
                $themeId = $existing->id;
            } else {
                $themeId = DB::table('contest_themes')->insertGetId([
                    'name'          => ($name !== '' ? $name : 'Tema'),
                    'category'      => $cat,
                    'active'        => 1,
                    'contest_date'  => $contestDate,
                    'theme_pool_id' => null,         // keep it explicit
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            DB::table('contest_cycles')
                ->where('id', $c->id)
                ->update([
                    'contest_theme_id' => $themeId,
                    'updated_at'       => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('contest_cycles', function (Blueprint $table) {
            if (Schema::hasColumn('contest_cycles', 'contest_theme_id')) {
                try {
                    $table->dropIndex('contest_cycles_theme_id_idx');
                } catch (\Throwable $e) {
                    try { $table->dropIndex(['contest_theme_id']); } catch (\Throwable $e2) {}
                }
                $table->dropColumn('contest_theme_id');
            }
        });

        Schema::table('contest_themes', function (Blueprint $table) {
            if (Schema::hasColumn('contest_themes', 'contest_date')) $table->dropColumn('contest_date');
            if (Schema::hasColumn('contest_themes', 'active'))       $table->dropColumn('active');
            if (Schema::hasColumn('contest_themes', 'category'))     $table->dropColumn('category');
            if (Schema::hasColumn('contest_themes', 'name'))         $table->dropColumn('name');
            // We leave theme_pool_id as-is (don’t revert to NOT NULL).
        });
    }
};
