<?php

namespace App\Console\Commands\Concurs;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * BULLETPROOF FALLBACK THEME - SPEC COMPLIANT
 * 
 * LOGIC (BULLETPROOF):
 * 1. STRICT GUARDS: Require exactly 1 open submission with theme_id=NULL
 * 2. Only run after 21:00 (winner had their chance)
 * 3. Pick random theme from theme_pools + random category
 * 4. UNFREEZE submission (theme_id = non-NULL) - THE UNFREEZE SWITCH
 * 5. Contest continues seamlessly - no more contest_flags needed
 */
class FallbackTheme extends Command
{
    protected $signature   = 'concurs:fallback-theme {--force : Run regardless of time}';
    protected $description = 'BULLETPROOF: Auto-generate fallback theme if winner doesn\'t choose by 21:00';

    public function handle(): int
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        // ═══════════════════════════════════════════════════════════════════════════════
        // BULLETPROOF GUARDS: Exactly 1 open submission with theme_id=NULL (frozen)
        // ═══════════════════════════════════════════════════════════════════════════════
        if (!$this->guardInvariants()) {
            return self::FAILURE;
        }

        // Guard: only run after 21:00 (winner had their chance) unless --force
        if (!$this->option('force') && $now->hour < 21) {
            $this->info('Not yet 21:00. Winner still has time to choose.');
            return self::SUCCESS;
        }

        DB::beginTransaction();
        try {
            // ═══════════════════════════════════════════════════════════════════════════════
            // PICK RANDOM FALLBACK THEME
            // ═══════════════════════════════════════════════════════════════════════════════
            $categories = ['CSD', 'ITC', 'Artiști', 'Genuri'];
            
            $poolTheme = DB::table('theme_pools')
                ->where('is_active', 1)
                ->inRandomOrder()
                ->value('text');

            // Fallback if theme_pools is empty
            if (!$poolTheme) {
                $wordbank = [
                    'Neon Dreams', 'Lost Frequencies', 'Silent Waves',
                    'Echoes of Time', 'Retro Pulse', 'Urban Nights',
                    'Velvet Sky', 'Digital Mirage', 'Parallel Lines',
                    'Infinite Loop', 'Golden Hour', 'Electric Soul',
                    'Midnight City', 'Crystal Castles', 'Analog Sunset'
                ];
                $poolTheme = $wordbank[array_rand($wordbank)];
            }

            // Pick random category
            $category = $categories[array_rand($categories)];
            $themeText = "{$category} — {$poolTheme}";

            // Create theme in contest_themes
            $themeId = DB::table('contest_themes')->insertGetId([
                'name'              => $themeText,
                'chosen_by_user_id' => null, // Fallback (system-generated)
                'created_at'        => $now,
            ]);

            // Find the NEWEST frozen submission opened at 20:00
            $frozen = DB::table('contest_cycles')
                ->where('lane', 'submission')
                ->where('status', 'open')
                ->whereNull('theme_id')
                ->orderByDesc('id')
                ->first(['id', 'submit_end_at', 'theme_text']);

            if (!$frozen) {
                throw new \Exception('No frozen submission found to unfreeze.');
            }

            // Compute next 20:00 (voting end for promoted cycle, and submit_end for new submission)
            $tomorrow2000 = Carbon::parse($frozen->submit_end_at, $tz)->addDay()->setTime(20, 0, 0);

            // PROMOTE the frozen submission to voting WITHOUT changing its theme fields
            DB::table('contest_cycles')
                ->where('id', $frozen->id)
                ->update([
                    'lane'        => 'voting',
                    'status'      => 'open',
                    'vote_end_at' => $tomorrow2000,
                    // keep theme_id/theme_text as-is to preserve yesterday's theme on the vote page
                    'updated_at'  => $now,
                ]);

            // OPEN a fresh submission for uploads with the FALLBACK theme (this is the unfreeze)
            DB::table('contest_cycles')->insert([
                'lane'          => 'submission',
                'status'        => 'open',
                'theme_id'      => $themeId,
                'theme_text'    => $themeText,
                'start_at'      => $now,
                'submit_end_at' => $tomorrow2000,
                'vote_end_at'   => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            DB::commit();
            $this->info("[FALLBACK] Theme created: {$themeText}");
            $this->info('[PROMOTE] Frozen submission promoted to voting; vote page available.');
            $this->info('[UNFREEZE] New submission opened with fallback theme; uploads open.');
            
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("[FALLBACK] Error applying fallback theme: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * BULLETPROOF GUARDS: Exactly 1 open submission with theme_id=NULL (frozen)
     */
    private function guardInvariants(): bool
    {
        $frozenSubmission = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->whereNull('theme_id')
            ->count();

        if ($frozenSubmission !== 1) {
            $this->error("[FALLBACK] ABORT — expected 1 frozen submission (theme_id=NULL); found {$frozenSubmission}");
            return false;
        }

        $this->info("[FALLBACK] Guards passed: 1 frozen submission found ✅");
        return true;
    }
}
