<?php

namespace App\Console\Commands\Concurs;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Declare the daily song contest winner at 20:00
 * REBUILT PER COMPENDIUM V2 (2025-10-20)
 * 
 * LOGIC:
 * 1. Find voting cycle that ended at 20:00
 * 2. Determine winner (votes → tie-random → autowin → zero → no-winner)
 * 3. Close submission cycle → promote to voting lane
 * 4. Set window='waiting_theme' (winner has until 21:00)
 * 5. Ban winning song
 */
class DeclareWinner extends Command
{
    protected $signature   = 'concurs:declare-winner';
    protected $description = 'Declare winner at 20:00, promote cycles, set waiting_theme window';

    public function handle(): int
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        // Only run at/after 20:00
        if ($now->hour < 20) {
            $this->info('Not yet 20:00. Skipping.');
            return self::SUCCESS;
        }

        // ═══════════════════════════════════════════════════════════════════════════════
        // STEP 1: ROTATE SUBMISSION → VOTING (if needed)
        // ═══════════════════════════════════════════════════════════════════════════════
        
        $submissionCycle = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->where('submit_end_at', '<=', $now)
            ->orderBy('submit_end_at')
            ->first();

        if ($submissionCycle) {
            $this->info("Found submission cycle {$submissionCycle->id} that ended. Rotating to voting...");
            
            DB::beginTransaction();
            try {
                $tomorrow2000 = Carbon::parse($submissionCycle->submit_end_at, $tz)->addDay()->setTime(20, 0, 0);
                
                // Promote submission → voting
                DB::table('contest_cycles')
                    ->where('id', $submissionCycle->id)
                    ->update([
                        'lane'        => 'voting',
                        'vote_end_at' => $tomorrow2000,
                        'updated_at'  => $now,
                    ]);
                
                $this->info("Promoted cycle {$submissionCycle->id} to voting. Vote ends: {$tomorrow2000}");
                
                // Get Theme B from cache (or fallback)
                $nextThemeId   = cache()->pull('concurs_next_theme_id');
                $nextThemeText = cache()->pull('concurs_next_theme_text');
                
                if (!$nextThemeId || !$nextThemeText) {
                    $this->warn('No Theme B in cache. Creating fallback theme...');
                    
                    // Fallback: pick random theme
                    $poolTheme = DB::table('theme_pools')
                        ->where('is_active', 1)
                        ->inRandomOrder()
                        ->value('text');
                    
                    $categories = ['CSD', 'ITC', 'Artiști', 'Genuri'];
                    $category = $categories[array_rand($categories)];
                    
                    if ($poolTheme) {
                        $nextThemeText = "{$category} - {$poolTheme}";
                    } else {
                        $defaultThemes = ['Kickoff', 'Neon Dreams', 'Lost Frequencies', 'Silent Waves'];
                        $nextThemeText = "{$category} - " . $defaultThemes[array_rand($defaultThemes)];
                    }
                    
                    $nextThemeId = DB::table('contest_themes')->insertGetId([
                        'name'              => $nextThemeText,
                        'chosen_by_user_id' => null,
                        'created_at'        => $now,
                    ]);
                }
                
                // Create new submission cycle with Theme B
                $next2000 = $tomorrow2000->copy(); // same as voting end = next 20:00
                
                DB::table('contest_cycles')->insert([
                    'theme_id'      => $nextThemeId,
                    'theme_text'    => $nextThemeText,
                    'lane'          => 'submission',
                    'status'        => 'open',
                    'start_at'      => $now,
                    'submit_end_at' => $next2000,
                    'vote_end_at'   => null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
                
                $this->info("Created new submission cycle with theme: {$nextThemeText}");
                
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("Rotation failed: {$e->getMessage()}");
                return self::FAILURE;
            }
        }

        // ═══════════════════════════════════════════════════════════════════════════════
        // STEP 2: DECLARE WINNER (if there's a voting cycle to close)
        // ═══════════════════════════════════════════════════════════════════════════════

        $votingCycle = DB::table('contest_cycles')
            ->where('lane', 'voting')
            ->where('status', 'open')
            ->where('vote_end_at', '<=', $now)
            ->orderBy('vote_end_at')
            ->first();

        if (!$votingCycle) {
            $this->info('No voting cycle to close.');
            return self::SUCCESS;
        }

        // Idempotent: if winner already exists, bail
        $already = DB::table('winners')->where('cycle_id', $votingCycle->id)->exists();
        if ($already) {
            $this->info("Winner already declared for cycle {$votingCycle->id}.");
            
            // Still close the cycle if it's open
            DB::table('contest_cycles')
                ->where('id', $votingCycle->id)
                ->update(['status' => 'closed', 'updated_at' => $now]);
            
            return self::SUCCESS;
        }

        DB::beginTransaction();
        try {
            // 2) DETERMINE WINNER
            $winnerSongId = null;
            $winnerUserId = null;
            $decideMethod = 'normal';
            $rngSeed      = null;

            // Get all songs in this cycle
            $songsInCycle = DB::table('songs')
                ->where('cycle_id', $votingCycle->id)
                ->select('id as song_id', 'user_id')
                ->get();

            if ($songsInCycle->isEmpty()) {
                // ZERO SUBMISSIONS → No winner
                $this->info("No songs in cycle {$votingCycle->id}. No winner.");
                
                // Close cycle without winner
                DB::table('contest_cycles')
                    ->where('id', $votingCycle->id)
                    ->update(['status' => 'closed', 'updated_at' => $now]);

                // Still set waiting_theme (fallback will create new theme)
                DB::table('contest_flags')->updateOrInsert(
                    ['name' => 'window'],
                    ['value' => 'waiting_theme', 'updated_at' => $now]
                );

                DB::commit();
                return self::SUCCESS;
            }

            // Tally votes
            $rows = DB::table('votes as v')
                ->join('songs as s', 's.id', '=', 'v.song_id')
                ->where('s.cycle_id', $votingCycle->id)
                ->selectRaw('s.id as song_id, s.user_id, COUNT(*) as votes, MIN(v.created_at) as first_vote_at')
                ->groupBy('s.id', 's.user_id')
                ->orderByDesc('votes')
                ->orderBy('first_vote_at')
                ->get();

            if ($rows->isEmpty()) {
                // ZERO VOTES → Random winner from all songs
                $rngSeed = crc32("cycle:{$votingCycle->id}|zero|" . $now->format('Y-m-d'));
                mt_srand($rngSeed);
                $pick = $songsInCycle[mt_rand(0, $songsInCycle->count() - 1)];
                $winnerSongId = $pick->song_id;
                $winnerUserId = $pick->user_id;
                $decideMethod = 'random';
            } else {
                // Check tie on top vote count
                $topVotes   = (int)$rows->first()->votes;
                $topBucket  = $rows->where('votes', $topVotes)->values();

                if ($topBucket->count() === 1) {
                    // Single winner
                    $winnerSongId = $topBucket[0]->song_id;
                    $winnerUserId = $topBucket[0]->user_id;
                    $decideMethod = 'normal';
                } else {
                    // TIE → Random pick among top
                    $rngSeed = crc32("cycle:{$votingCycle->id}|tie|votes:{$topVotes}|" . $now->format('Y-m-d'));
                    mt_srand($rngSeed);
                    $pick = $topBucket[mt_rand(0, $topBucket->count() - 1)];
                    $winnerSongId = $pick->song_id;
                    $winnerUserId = $pick->user_id;
                    $decideMethod = 'random';
                }
            }

            // 3) WRITE WINNER
            DB::table('winners')->insert([
                'cycle_id'       => $votingCycle->id,
                'song_id'        => $winnerSongId,
                'user_id'        => $winnerUserId,
                'decide_method'  => $decideMethod,
                'created_at'     => $now,
            ]);

            // 4) BAN WINNING SONG
            $ytId = DB::table('songs')->where('id', $winnerSongId)->value('youtube_id');
            if ($ytId) {
                DB::table('banned_songs')->insertOrIgnore([
                    'youtube_id' => $ytId,
                    'reason'     => 'winner_ban',
                    'created_at' => $now,
                ]);
            }

            // 5) CLOSE VOTING CYCLE
            DB::table('contest_cycles')
                ->where('id', $votingCycle->id)
                ->update([
                    'status'          => 'closed',
                    'winner_user_id'  => $winnerUserId,
                    'winner_song_id'  => $winnerSongId,
                    'decide_method'   => $decideMethod,
                    'updated_at'      => $now,
                ]);

            // 6) PROMOTE SUBMISSION CYCLE → VOTING LANE
            $submissionCycle = DB::table('contest_cycles')
                ->where('lane', 'submission')
                ->where('status', 'open')
                ->orderByDesc('start_at')
                ->first();

            if ($submissionCycle) {
                $next2000 = $now->copy()->addDay()->setTime(20, 0, 0);
                
                DB::table('contest_cycles')
                    ->where('id', $submissionCycle->id)
                    ->update([
                        'lane'        => 'voting',
                        'status'      => 'open',
                        'vote_end_at' => $next2000,
                        'updated_at'  => $now,
                    ]);
            }

            // 7) SET WINDOW='waiting_theme' (winner has until 21:00)
            DB::table('contest_flags')->updateOrInsert(
                ['name' => 'window'],
                ['value' => 'waiting_theme', 'updated_at' => $now]
            );

            // 8) AUDIT LOG (optional - skip if table doesn't exist)
            try {
                DB::table('contest_audit_logs')->insert([
                    'event_type' => 'declare_winner',
                    'cycle_id'   => $votingCycle->id,
                    'seed'       => $rngSeed,
                    'details'    => json_encode([
                        'method'  => $decideMethod,
                        'song_id' => $winnerSongId,
                        'user_id' => $winnerUserId,
                    ]),
                    'created_at' => $now,
                ]);
            } catch (\Throwable $e) {
                // Table doesn't exist yet, skip audit log
                \Log::warning('contest_audit_logs table missing: ' . $e->getMessage());
            }

            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            // Idempotency on race
            if (($e->errorInfo[1] ?? null) === 1062) {
                $this->info("Winner concurrently inserted for cycle {$votingCycle->id}.");
                return self::SUCCESS;
            }
            throw $e;
        }

        $this->info("✅ Winner declared for cycle {$votingCycle->id}: song {$winnerSongId}, user {$winnerUserId} ({$decideMethod}).");
        return self::SUCCESS;
    }
}
