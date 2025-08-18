<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Song;
use App\Models\Winner;

class DeclareDailyWinner extends Command
{
    protected $signature   = 'concurs:declare-winner';
    protected $description = 'Declare the daily song contest winner at 20:00 (Mon–Fri)';

    public function handle()
    {
        // 1) No contests on weekends
        if (now()->isWeekend()) {
            $this->info('Weekend — no contest today.');
            return self::SUCCESS;
        }

        $today = now()->toDateString();

        // 2) Avoid double run (check both columns just in case)
        $already = Winner::query()
            ->whereDate('contest_date', $today)
            ->orWhereDate('win_date', $today)
            ->exists();

        if ($already) {
            $this->info('Winner already declared for today.');
            return self::SUCCESS;
        }

        // 3) Work out which date column songs use
        $songDateCol = Schema::hasColumn('songs', 'competition_date')
            ? 'songs.competition_date'
            : 'songs.created_at';

        // 4) Compute today’s vote totals (only votes from today, only songs from today)
        $totals = DB::table('songs')
            ->join('votes', function ($join) use ($today) {
                $join->on('votes.song_id', '=', 'songs.id')
                     ->whereDate('votes.vote_date', $today);
            })
            ->whereDate($songDateCol, $today)
            ->groupBy('songs.id')
            ->selectRaw('songs.id as song_id, COUNT(votes.id) as total_votes')
            ->orderByDesc('total_votes')
            ->orderBy('songs.id') // stable tie-break
            ->get();

        if ($totals->isEmpty()) {
            $this->info('No votes today — no winner.');
            return self::SUCCESS;
        }

        // 5) Tie check (simple: if multiple have same top votes, skip and let Versus/UI handle)
        $topVotes = (int) $totals->first()->total_votes;
        $leaders  = $totals->where('total_votes', $topVotes);

        if ($leaders->count() > 1) {
            $this->info('Tie detected — handle Versus in app/UI.');
            return self::SUCCESS;
        }

        // 6) Persist winner
        $top   = $totals->first();
        $song  = Song::find($top->song_id);

        if (!$song) {
            $this->warn('Winning song not found — aborting.');
            return self::SUCCESS;
        }

        Winner::create([
            'contest_date'          => $today,
            'win_date'              => $today,                 // keep both for compatibility
            'user_id'               => $song->user_id,
            'song_id'               => $song->id,
            'vote_count'            => $topVotes,
            'was_tie'               => false,
            'theme_chosen'          => false,
            'competition_theme_id'  => $song->theme_id ?? null,
        ]);

        // Mark song as winner for convenience in UI
        if (!$song->is_winner) {
            $song->is_winner = true;
            $song->save();
        }

        // 7) NEW: write position points for today (idempotent via unique index)
        app(\App\Services\AwardPoints::class)->awardForDate($today);

        $this->info('✅ Winner declared: song #'.$song->id.' (user #'.$song->user_id.') with '.$topVotes.' votes.');
        return self::SUCCESS;
    }
}
