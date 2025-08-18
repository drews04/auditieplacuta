<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_user_points_monthly');

        DB::unprepared(<<<SQL
CREATE VIEW v_user_points_monthly AS
SELECT
    up.user_id,
    DATE_FORMAT(up.contest_date, '%Y-%m') AS ym,
    SUM(up.points) AS points
FROM user_points up
WHERE up.contest_date IS NOT NULL
GROUP BY up.user_id, DATE_FORMAT(up.contest_date, '%Y-%m');
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_user_points_monthly');
    }
};
