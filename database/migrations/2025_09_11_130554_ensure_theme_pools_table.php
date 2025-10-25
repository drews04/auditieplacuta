<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('theme_pools')) {
            Schema::create('theme_pools', function (Blueprint $table) {
                $table->id();
                $table->string('category', 40)->nullable()->index();     // e.g. csd, genuri, artisti, it
                $table->string('name', 120);                              // the theme text (e.g. "American")
                $table->boolean('active')->default(true)->index();        // 1 = eligible for random pick
                $table->timestamps();
                $table->unique(['category','name'], 'uniq_theme_pools_cat_name');
            });
            return;
        }

        // If table exists, add any missing columns/constraints without breaking existing data
        Schema::table('theme_pools', function (Blueprint $table) {
            if (!Schema::hasColumn('theme_pools', 'category')) {
                $table->string('category', 40)->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('theme_pools', 'name')) {
                $table->string('name', 120)->after('category');
            }
            if (!Schema::hasColumn('theme_pools', 'active')) {
                $table->boolean('active')->default(true)->after('name')->index();
            }
            if (!Schema::hasColumn('theme_pools', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('active');
            }
            if (!Schema::hasColumn('theme_pools', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });

        // Best effort: add unique index if it’s missing (MariaDB-safe)
        try {
            DB::statement("CREATE UNIQUE INDEX uniq_theme_pools_cat_name ON theme_pools (category, name)");
        } catch (\Throwable $e) {
            // ignore if it already exists
        }
    }

    public function down(): void
    {
        // We won't drop the table if it existed before; only drop what we added safely
        try { DB::statement("DROP INDEX uniq_theme_pools_cat_name ON theme_pools"); } catch (\Throwable $e) {}
        // (Optional) Uncomment to drop the table entirely if you created it in this migration:
        // Schema::dropIfExists('theme_pools');
    }
};
