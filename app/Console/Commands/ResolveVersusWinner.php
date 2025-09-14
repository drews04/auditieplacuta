<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ContestCycle;
use App\Models\Tiebreak;
use App\Models\Song;
use App\Models\Vote;
use App\Models\Winner;

class ResolveVersusWinner extends Command
{
    protected $signature   = 'concurs:resolve-versus';
    protected $description = 'At 20:31 (Mon–Fri), resolve any active Versus (tie) by declaring a winner.';

    public function handle(): int
    {
        $now = Carbon::now();

        // Weekdays only
        if ($now->isWeekend()) {
            $this->info('Weekend — no Versus resolution.');
            return self::SUCCESS;
        }

        // Find today’s tiebreak that has ended but not resolved yet
        $tb = Tiebreak::whereDate('contest_date', $now->toDateString())
            ->where('resolved', false)
            ->first();

        if (!$tb) {
            $this->info('No active tiebreak to resolve.');
            return self::SUCCESS;
        }

        // Make sure it actually ended
        if ($now->lt($tb->ends_at)) {
            $this->info('Tiebreak still running; skip.');
            return self::SUCCESS;
        }

        $songIds = (array) $tb->song_ids;
        if (empty($songIds)) {
            $tb->resolved = true;
            $tb->save();
            $this->warn('Tiebreak has no songs — marking resolved.');
            return self::SUCCESS;
        }

        // Determine the related finished cycle (yesterday’s submissions that ended vote today ~20:00)
        $cycle = ContestCycle::whereDate('vote_end_at', $tb->contest_date)
            ->orderByDesc('vote_end_at')
            ->first();

        if (!$cycle) {
            // fallback: the most recent cycle that ended before now and has no winner
            $cycle = ContestCycle::where('vote_end_at', '<=', $now)
                ->whereNull('winner_decided_at')
                ->orderByDesc('vote_end_at')
                ->first();
        }

        if (!$cycle) {
            $this->warn('No target cycle to attach the winner to. Mark tiebreak resolved anyway.');
            $tb->resolved = true;
            $tb->save();
            return self::SUCCESS;
        }

        // Count votes only inside the Versus window
        $counts = Vote::select('song_id', DB::raw('COUNT(*) as total'))
            ->whereIn('song_id', $songIds)
            ->whereBetween('created_at', [$tb->starts_at, $tb->ends_at])
            ->groupBy('song_id')
            ->orderByDesc('total')
            ->get();

        $winnerSongId = null;
        $finalWasTie  = false;

        if ($counts->isNotEmpty()) {
            $topTotal   = (int) $counts->first()->total;
            $leadersIds = $counts->where('total', $topTotal)->pluck('song_id')->values();

            if ($leadersIds->count() === 1) {
                $winnerSongId = (int) $leadersIds->first();
            } else {
                // tie persists → last vote timestamp inside window wins
                $lastVote = Vote::whereIn('song_id', $leadersIds)
                    ->whereBetween('created_at', [$tb->starts_at, $tb->ends_at])
                    ->orderByDesc('created_at')
                    ->first();

                if ($lastVote) {
                    $winnerSongId = (int) $lastVote->song_id;
                    $finalWasTie  = true;
                }
            }
        }

        // If still no winner (no Versus votes), pick by the last vote of the day among tied songs, else first id
        if (!$winnerSongId) {
            $lastOfDay = Vote::whereIn('song_id', $songIds)
                ->where('created_at', '<=', $tb->ends_at)
                ->orderByDesc('created_at')
                ->first();
            if ($lastOfDay) {
                $winnerSongId = (int) $lastOfDay->song_id;
                $finalWasTie  = true;
            } else {
                $winnerSongId = (int) collect($songIds)->first();
                $finalWasTie  = true;
            }
        }

        $song = Song::find($winnerSongId);
        if (!$song) {
            $tb->resolved = true;
            $tb->save();
            $this->warn('Winner song not found — marking tiebreak resolved with no winner.');
            return self::SUCCESS;
        }

        // Persist Winner row
        $winDate = $cycle->vote_end_at?->toDateString() ?? $tb->contest_date?->toDateString() ?? $now->toDateString();

        Winner::create([
            'cycle_id'             => $cycle->id,
            'user_id'              => $song->user_id,
            'song_id'              => $song->id,
            'vote_count'           => (int) Vote::where('song_id', $song->id)->whereBetween('created_at', [$tb->starts_at, $tb->ends_at])->count(),
            'was_tie'              => true,
            'theme_chosen'         => false,
            'contest_date'         => $winDate,
            'win_date'             => $now->toDateString(),
            'competition_theme_id' => $song->theme_id ?? null,
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        // Mark song & cycle
        if (!$song->is_winner) {
            $song->is_winner = true;
            $song->save();
        }

        $cycle->winner_decided_at = $now;
        $cycle->winner_song_id    = $song->id;
        $cycle->winner_user_id    = $song->user_id;
        $cycle->save();

        // Close the tiebreak
        $tb->resolved = true;
        $tb->save();

        $this->info("✅ Versus resolved: cycle #{$cycle->id}, song #{$song->id}.");
        return self::SUCCESS;
    }
}
