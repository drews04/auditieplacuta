<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\UserPoint;
use Illuminate\Support\Carbon;

class AwardPoints
{
    /** Award top-10 points for a given calendar day (idempotent). */
    public function awardForDate(Carbon|string $date): int
    {
        $contestDate = Carbon::parse($date)->toDateString();

        // Read mapping from config/leaderboards.php (or config/points.php), then hard fallback.
        $map = Config::get('leaderboards.position_points');
        if (!is_array($map) || empty($map)) {
            $map = Config::get('points.position_points');
        }
        if (!is_array($map) || empty($map)) {
            $map = [
                1 => 25, 2 => 18, 3 => 15, 4 => 12, 5 => 10,
                6 => 8,  7 => 6,  8 => 4,  9 => 2,  10 => 1,
            ];
        }

        $positions = $this->dailyPositions($contestDate);
        if ($positions->isEmpty()) return 0;

        $now = now();
        $rows = [];
        foreach ($positions as $row) {
            $pos = (int) $row->position;
            $pts = (int) ($map[$pos] ?? 0);
            if ($pts <= 0) continue;

            $rows[] = [
                'user_id'      => (int) $row->user_id,
                'points'       => $pts,
                'contest_date' => $contestDate,
                'song_id'      => (int) $row->song_id,
                'reason'       => 'position',
                'meta'         => json_encode(['position' => $pos]),
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        if (empty($rows)) return 0;

        // Idempotent via unique index (user_id, contest_date, reason)
        UserPoint::query()->upsert(
            $rows,
            ['user_id', 'contest_date', 'reason'],
            [] // do not update on conflict
        );

        return count($rows);
    }

    /**
     * Build daily positions:
     * - songs created on that calendar date
     * - votes counted only from that same calendar date
     * - no DQ filter (column not present in your schema)
     */
    protected function dailyPositions(string $contestDate)
{
    // Prefer songs.competition_date if present; else fallback to created_at
    $dateCol = \Schema::hasColumn('songs', 'competition_date')
        ? 'songs.competition_date'
        : 'songs.created_at';

    $rows = \DB::table('songs')
        ->leftJoin('votes', function ($join) use ($contestDate) {
            $join->on('votes.song_id', '=', 'songs.id')
                 ->whereDate('votes.vote_date', $contestDate);
        })
        ->whereDate($dateCol, $contestDate)
        ->groupBy('songs.id', 'songs.user_id')
        ->selectRaw('
            songs.id      AS song_id,
            songs.user_id AS user_id,
            COUNT(votes.id) AS vote_count
        ')
        ->orderByDesc('vote_count')
        ->orderBy('songs.id') // stable tie-break
        ->limit(10)
        ->get();

    // annotate positions 1..10
    return $rows->map(function ($r, $i) {
        $r->position = $i + 1;
        return $r;
    });
}
    
}

