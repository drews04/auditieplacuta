<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Song;
use App\Models\Winner;

class DeclareDailyWinner extends Command
{
    protected $signature   = 'concurs:declare-winner';
    protected $description = 'Declare the daily song contest winner at vote_end_at. Tie = random pick among leaders.';

    public function handle()
    {
      

        $now = now();


        // Find the most recent cycle whose voting ended and has no winner yet
        $cycle = \App\Models\ContestCycle::query()
            ->where('vote_end_at', '<=', $now)
            ->whereNull('winner_decided_at')
            ->orderByDesc('vote_end_at')
            ->first();

        if (!$cycle) {
            $this->info('No finished voting cycle without winner.');
            return self::SUCCESS;
        }

        // Tally votes for this cycle
        $totals = DB::table('songs')
            ->join('votes', function ($join) use ($cycle) {
                $join->on('votes.song_id', '=', 'songs.id')
                     ->where('votes.cycle_id', $cycle->id);
            })
            ->where('songs.cycle_id', $cycle->id)
            ->groupBy('songs.id')
            ->selectRaw('songs.id as song_id, COUNT(votes.id) as total_votes')
            ->orderByDesc('total_votes')
            ->orderBy('songs.id') // stable fallback
            ->get();

        if ($totals->isEmpty()) {
            $this->info('No votes in this cycle — leaving undecided.');
            return self::SUCCESS;
        }

        // Leaders (if tie on top, random pick)
        $topVotes = (int) $totals->first()->total_votes;
        $leaders  = $totals->where('total_votes', $topVotes)->values();

        if ($leaders->count() > 1) {
            $pick = $leaders->random();
            $winnerSongId = (int) $pick->song_id;
            $wasTie = true;
        } else {
            $winnerSongId = (int) $totals->first()->song_id;
            $wasTie = false;
        }

        $song = Song::find($winnerSongId);
        if (!$song) {
            $this->warn('Winning song not found.');
            return self::SUCCESS;
        }

        // Persist Winner row
        Winner::create([
            'cycle_id'             => $cycle->id,
            'user_id'              => $song->user_id,
            'song_id'              => $song->id,
            'vote_count'           => $topVotes,
            'was_tie'              => $wasTie,
            'theme_chosen'         => false,
            'contest_date'         => $cycle->vote_end_at?->toDateString(),
            'win_date'             => $now->toDateString(),
            'competition_theme_id' => $song->theme_id ?? null,
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        // Mark song as winner
        if (!$song->is_winner) {
            $song->is_winner = true;
            $song->save();
        }

        // Mark cycle as decided
        \App\Models\ContestCycle::where('id', $cycle->id)
            ->update([
                'winner_decided_at' => $now,
                'winner_song_id'    => $song->id,
                'winner_user_id'    => $song->user_id,
            ]);

        // Award points for this cycle’s contest day
        app(\App\Services\AwardPoints::class)
            ->awardForDate($cycle->vote_end_at->toDateString());

        $this->info("✅ Winner declared for cycle #{$cycle->id}: song #{$song->id} ({$topVotes} votes)" . ($wasTie ? ' [random among tie].' : '.'));
        return self::SUCCESS;
    }
}
