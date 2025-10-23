<?php

namespace App\Console\Commands\Concurs;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * BULLETPROOF DECLARE WINNER - SPEC COMPLIANT
 * 
 * LOGIC (BULLETPROOF):
 * 1. STRICT GUARDS: Require exactly 1 open submission + 1 open voting
 * 2. Find voting cycle that ended at 20:00
 * 3. Determine winner: votes → RANDOM TIE-BREAK → zero votes = random
 * 4. Close voting, stamp winner
 * 5. FREEZE submission (theme_id = NULL) - THE FREEZE SWITCH
 * 6. Winner sees modal until 21:00 or theme picked
 */
class DeclareWinner extends Command
{
    protected $signature   = 'concurs:declare-winner {--force : Run regardless of time}';
    protected $description = 'BULLETPROOF: Declare winner at 20:00, rotate lanes, open frozen submission, winner picks theme until 21:00';

    public function handle(): int
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        // Only run at/after 20:00 unless --force
        if (!$this->option('force') && $now->hour < 20) {
            $this->info('Not yet 20:00. Skipping.');
            return self::SUCCESS;
        }

        // Normalize open cycles to guarantee invariants before proceeding
        $norm = $this->normalizeOpenCycles($now);

        // ═══════════════════════════════════════════════════════════════════════════════
        // HANDLE FIRST ITERATION (after START button) - NO VOTING CYCLE YET
        // ═══════════════════════════════════════════════════════════════════════════════
        $openCounts = DB::selectOne("
            SELECT 
                SUM(CASE WHEN lane='submission' AND status='open' THEN 1 ELSE 0 END) as submission_count,
                SUM(CASE WHEN lane='voting' AND status='open' THEN 1 ELSE 0 END) as voting_count
            FROM contest_cycles
        ");

        // FIRST ITERATION: Only submission exists, no voting yet
        if ($openCounts->submission_count == 1 && $openCounts->voting_count == 0) {
            $this->info('[DECLARE] First iteration detected (no voting cycle yet).');
            return $this->handleFirstIteration($now);
        }

        // ═══════════════════════════════════════════════════════════════════════════════
        // BULLETPROOF GUARDS: Exactly 1 open submission + 1 open voting (NORMAL CYCLES)
        // ═══════════════════════════════════════════════════════════════════════════════
        if ($openCounts->submission_count != 1 || $openCounts->voting_count != 1) {
            $this->error("[DECLARE] ABORT — expected 1 open voting & 1 open submission; found voting={$openCounts->voting_count}, submission={$openCounts->submission_count}");
            return self::FAILURE;
        }

        $this->info("[DECLARE] Guards passed: 1 open submission + 1 open voting ✅");

        // ═══════════════════════════════════════════════════════════════════════════════
        // FIND VOTING CYCLE THAT ENDED AT 20:00 (or latest open if --force)
        // ═══════════════════════════════════════════════════════════════════════════════
        $votingQuery = DB::table('contest_cycles')
            ->where('lane', 'voting')
            ->where('status', 'open');
        if (!$this->option('force')) {
            $votingQuery->where('vote_end_at', '<=', $now);
        }
        $votingCycle = $votingQuery->orderBy('vote_end_at')->first();

        if (!$votingCycle) {
            $this->info('No voting cycle to close.');
            return self::SUCCESS;
        }

        // Idempotent: if winner already exists, bail
        $already = DB::table('winners')->where('cycle_id', $votingCycle->id)->exists();
        if ($already) {
            $this->info("Winner already declared for cycle {$votingCycle->id}.");
            
            // Ensure the cycle is closed
            DB::table('contest_cycles')
                ->where('id', $votingCycle->id)
                ->update(['status' => 'closed', 'updated_at' => $now]);
            
            // Freeze only; NO rotation at 20:00 (advance happens on pick/fallback)
            $this->freezeSubmission($now);
            return self::SUCCESS;
        }

        DB::beginTransaction();
        try {
            // ═══════════════════════════════════════════════════════════════════════════════
            // BULLETPROOF WINNER DETERMINATION (RANDOM TIE-BREAKING)
            // ═══════════════════════════════════════════════════════════════════════════════
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

                // Freeze only; NO rotation at 20:00
                $this->freezeSubmission($now);

                DB::commit();
                return self::SUCCESS;
            }

            // Tally votes with deterministic ordering for tie-breaking
            $rows = DB::table('votes as v')
                ->join('songs as s', 's.id', '=', 'v.song_id')
                ->where('s.cycle_id', $votingCycle->id)
                ->selectRaw('s.id as song_id, s.user_id, COUNT(*) as votes')
                ->groupBy('s.id', 's.user_id')
                ->orderByDesc('votes')
                ->orderBy('s.id') // Deterministic tie-break by song_id
                ->get();

            if ($rows->isEmpty()) {
                // ZERO VOTES → RANDOM winner from all songs
                $rngSeed = crc32("cycle:{$votingCycle->id}|zero|" . $now->format('Y-m-d'));
                mt_srand($rngSeed);
                $pick = $songsInCycle[mt_rand(0, $songsInCycle->count() - 1)];
                $winnerSongId = $pick->song_id;
                $winnerUserId = $pick->user_id;
                $decideMethod = 'random';
                $this->info("[DECLARE] ZERO VOTES → Random winner: song {$winnerSongId}, user {$winnerUserId}");
            } else {
                // Check for tie on top vote count
                $topVotes   = (int)$rows->first()->votes;
                $topBucket  = $rows->where('votes', $topVotes)->values();

                if ($topBucket->count() === 1) {
                    // Single winner
                    $winnerSongId = $topBucket[0]->song_id;
                    $winnerUserId = $topBucket[0]->user_id;
                    $decideMethod = 'normal';
                    $this->info("[DECLARE] Clear winner: song {$winnerSongId}, user {$winnerUserId} with {$topVotes} votes");
                } else {
                    // TIE → RANDOM pick among top vote getters
                    $rngSeed = crc32("cycle:{$votingCycle->id}|tie|votes:{$topVotes}|" . $now->format('Y-m-d'));
                    mt_srand($rngSeed);
                    $pick = $topBucket[mt_rand(0, $topBucket->count() - 1)];
                    $winnerSongId = $pick->song_id;
                    $winnerUserId = $pick->user_id;
                    $decideMethod = 'random';
                    $this->info("[DECLARE] TIE ({$topBucket->count()} songs with {$topVotes} votes) → Random winner: song {$winnerSongId}, user {$winnerUserId}");
                }
            }

            // ═══════════════════════════════════════════════════════════════════════════════
            // WRITE WINNER & CLOSE VOTING CYCLE
            // ═══════════════════════════════════════════════════════════════════════════════
            DB::table('winners')->insert([
                'cycle_id'       => $votingCycle->id,
                'song_id'        => $winnerSongId,
                'user_id'        => $winnerUserId,
                'decide_method'  => $decideMethod,
                'created_at'     => $now,
            ]);

            // Close voting cycle & stamp winner
            DB::table('contest_cycles')
                ->where('id', $votingCycle->id)
                ->update([
                    'status'          => 'closed',
                    'winner_user_id'  => $winnerUserId,
                    'winner_song_id'  => $winnerSongId,
                    'decide_method'   => $decideMethod,
                    'updated_at'      => $now,
                ]);

            // Freeze only; actual rotation will occur on theme pick/fallback
            $this->freezeSubmission($now);

            DB::commit();
            $this->info("[DECLARE] Winner declared for cycle {$votingCycle->id}: song {$winnerSongId}, user {$winnerUserId} ({$decideMethod})");
            $this->info("[FREEZE] Submission frozen (theme_id=NULL). Nothing advances until theme is chosen.");
            
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            // Idempotency on race condition
            if (($e->errorInfo[1] ?? null) === 1062) {
                $this->info("Winner concurrently inserted for cycle {$votingCycle->id}.");
                return self::SUCCESS;
            }
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Failed to declare winner: {$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * HANDLE FIRST ITERATION (after START button)
     * - Promote submission → voting
     * - Open new submission with Theme B (from cache)
     * - NO winner declared (no votes yet)
     */
    private function handleFirstIteration($now): int
    {
        $tz = config('app.timezone', 'Europe/Bucharest');
        
        $submissionCycle = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->where('submit_end_at', '<=', $now)
            ->first();

        if (!$submissionCycle) {
            $this->info('[FIRST] No submission cycle to promote yet.');
            return self::SUCCESS;
        }

        DB::beginTransaction();
        try {
            // 1) PROMOTE SUBMISSION → VOTING (Theme A songs)
            $tomorrow2000 = Carbon::parse($submissionCycle->submit_end_at, $tz)->addDay()->setTime(20, 0, 0);
            
            DB::table('contest_cycles')
                ->where('id', $submissionCycle->id)
                ->update([
                    'lane'        => 'voting',
                    'vote_end_at' => $tomorrow2000,
                    'updated_at'  => $now,
                ]);
            
            $this->info("[FIRST] Promoted cycle {$submissionCycle->id} to voting. Vote ends: {$tomorrow2000}");
            
            // 2) GET THEME B FROM CACHE (set by START button)
            $nextThemeId   = cache()->pull('concurs_next_theme_id');
            $nextThemeText = cache()->pull('concurs_next_theme_text');
            
            if (!$nextThemeId || !$nextThemeText) {
                throw new \Exception('Theme B not found in cache! START button must set it.');
            }
            
            // 3) CREATE NEW SUBMISSION CYCLE WITH THEME B
            $next2000 = $tomorrow2000->copy(); // same as voting end
            
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
            
            $this->info("[FIRST] Created new submission cycle with Theme B: {$nextThemeText}");
            $this->info("[FIRST] NO WINNER DECLARED (first iteration has no votes yet)");
            
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("[FIRST] Failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * FREEZE SUBMISSION: Set theme_id = NULL (the bulletproof freeze switch)
     */
    private function freezeSubmission($now): void
    {
        DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->update([
                'theme_id'   => null,
                'updated_at' => $now,
            ]);
        
        $this->info("[FREEZE] Submission cycle frozen (theme_id=NULL)");
    }

    /**
     * Promote current open submission → voting and create a NEW frozen submission for tomorrow.
     * This ensures voting remains available immediately at 20:00 and theme pick only unfreezes.
     */
    private function rotateLanesForNextDay(Carbon $now): void
    {
        $tz = config('app.timezone', 'Europe/Bucharest');

        // Find the open submission (today uploads)
        $submission = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->orderByDesc('start_at')
            ->first(['id', 'submit_end_at']);

        if (!$submission) {
            $this->warn('[ROTATE] No open submission to promote.');
            return;
        }

        $tomorrow2000 = Carbon::parse($submission->submit_end_at, $tz)->addDay()->setTime(20, 0, 0);

        // Promote submission → voting (open immediately)
        DB::table('contest_cycles')
            ->where('id', $submission->id)
            ->update([
                'lane'        => 'voting',
                'status'      => 'open',
                'vote_end_at' => $tomorrow2000,
                'updated_at'  => $now,
            ]);

        // Create a NEW frozen submission (theme_id=NULL) for the winner to set theme
        DB::table('contest_cycles')->insert([
            'theme_id'      => null,
            'theme_text'    => null,
            'lane'          => 'submission',
            'status'        => 'open',
            'start_at'      => $now,
            'submit_end_at' => $tomorrow2000,
            'vote_end_at'   => null,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $this->info('[ROTATE] Promoted current submission to voting and opened new frozen submission.');
    }

    /**
     * Preflight: collapse duplicate open lanes and normalize to a single open voting and a single open submission.
     * - Keep newest voting (vote_end_at DESC, id DESC); close the rest
     * - Keep newest submission (start_at DESC, id DESC); close the rest
     * - If multiple frozen submissions exist, keep newest frozen; close the rest
     * Returns an array with current counts after normalization
     */
    private function normalizeOpenCycles(Carbon $now): array
    {
        // Collapse open voting to a single row (keep newest)
        $openV = DB::table('contest_cycles')
            ->where('lane', 'voting')->where('status', 'open')
            ->orderByDesc('vote_end_at')->orderByDesc('id')
            ->get(['id']);

        if ($openV->count() > 1) {
            $keep = (int)$openV->first()->id;
            DB::table('contest_cycles')
                ->where('lane', 'voting')->where('status', 'open')
                ->where('id', '<>', $keep)
                ->update(['status' => 'closed', 'updated_at' => $now]);
            $this->warn("[NORMALIZE] Closed extra open voting cycles, kept id={$keep}");
        }

        // Collapse open submission to a single row (keep newest)
        $openS = DB::table('contest_cycles')
            ->where('lane', 'submission')->where('status', 'open')
            ->orderByDesc('start_at')->orderByDesc('id')
            ->get(['id','theme_id']);

        if ($openS->count() > 1) {
            $keep = (int)$openS->first()->id;
            DB::table('contest_cycles')
                ->where('lane', 'submission')->where('status', 'open')
                ->where('id', '<>', $keep)
                ->update(['status' => 'closed', 'updated_at' => $now]);
            $this->warn("[NORMALIZE] Closed extra open submission cycles, kept id={$keep}");
        }

        // If multiple frozen submissions exist, keep newest frozen
        $frozen = DB::table('contest_cycles')
            ->where('lane', 'submission')->where('status', 'open')
            ->whereNull('theme_id')
            ->orderByDesc('id')
            ->get(['id']);

        if ($frozen->count() > 1) {
            $keep = (int)$frozen->first()->id;
            DB::table('contest_cycles')
                ->where('lane', 'submission')->where('status', 'open')
                ->whereNull('theme_id')
                ->where('id', '<>', $keep)
                ->update(['status' => 'closed', 'updated_at' => $now]);
            $this->warn("[NORMALIZE] Closed older frozen submissions, kept id={$keep}");
        }

        // Return current counts after normalization
        $counts = DB::selectOne("
            SELECT 
                SUM(CASE WHEN lane='submission' AND status='open' THEN 1 ELSE 0 END) as submission_count,
                SUM(CASE WHEN lane='voting' AND status='open' THEN 1 ELSE 0 END) as voting_count
            FROM contest_cycles
        ");

        return [
            'submission_count' => (int)($counts->submission_count ?? 0),
            'voting_count'     => (int)($counts->voting_count ?? 0),
        ];
    }
}
