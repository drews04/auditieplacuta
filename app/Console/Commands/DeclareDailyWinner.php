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
    // Weekdays only
    if (now()->isWeekend()) {
        $this->info('Weekend — no contest.');
        return self::SUCCESS;
    }

    $now = now();

    // Pick the most recent cycle whose voting just ended and has no winner yet
    $cycle = \App\Models\ContestCycle::query()
        ->where('vote_end_at', '<=', $now)
        ->whereNull('winner_decided_at')
        ->orderByDesc('vote_end_at')
        ->first();

    if (!$cycle) {
        $this->info('No finished voting cycle without winner.');
        return self::SUCCESS;
    }

    // Compute votes inside this cycle
    $totals = \Illuminate\Support\Facades\DB::table('songs')
        ->join('votes', function ($join) use ($cycle) {
            $join->on('votes.song_id', '=', 'songs.id')
                 ->where('votes.cycle_id', $cycle->id);
        })
        ->where('songs.cycle_id', $cycle->id)
        ->groupBy('songs.id')
        ->selectRaw('songs.id as song_id, COUNT(votes.id) as total_votes')
        ->orderByDesc('total_votes')
        ->orderBy('songs.id') // stable
        ->get();

    if ($totals->isEmpty()) {
        $this->info('No votes in this cycle.');
        // Still mark cycle as decided to avoid looping forever
        \App\Models\ContestCycle::where('id', $cycle->id)->update(['winner_decided_at' => $now]);
        return self::SUCCESS;
    }

    // Tie? let Versus/UI handle; skip declaring
    $topVotes = (int) $totals->first()->total_votes;
    $leaders  = $totals->where('total_votes', $topVotes);
    if ($leaders->count() > 1) {
        $this->info('Tie detected — handle via Versus/UI.');
        return self::SUCCESS;
    }

    // Persist winner
    $top  = $totals->first();
    $song = \App\Models\Song::find($top->song_id);
    if (!$song) {
        $this->warn('Winning song not found.');
        return self::SUCCESS;
    }

    \App\Models\Winner::create([
        'cycle_id'              => $cycle->id,
        'user_id'               => $song->user_id,
        'song_id'               => $song->id,
        'vote_count'            => $topVotes,
        'was_tie'               => false,
        'theme_chosen'          => false,
        // optional legacy fields:
        'contest_date'          => $cycle->vote_end_at?->toDateString(),
        'win_date'              => $now->toDateString(),
        'competition_theme_id'  => $song->theme_id ?? null,
        'created_at'            => $now,
        'updated_at'            => $now,
    ]);

    // Mark on song and cycle
    if (!$song->is_winner) {
        $song->is_winner = true;
        $song->save();
    }

    \App\Models\ContestCycle::where('id', $cycle->id)
        ->update(['winner_decided_at' => $now,
                  'winner_song_id'    => $song->id,
                  'winner_user_id'    => $song->user_id]);

    // Award points for this cycle's “contest day”
    app(\App\Services\AwardPoints::class)->awardForDate($cycle->vote_end_at->toDateString());

    $this->info("✅ Winner declared for cycle #{$cycle->id}: song #{$song->id} ({$topVotes} votes).");
    return self::SUCCESS;
}

}
