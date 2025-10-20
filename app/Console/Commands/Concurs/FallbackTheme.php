<?php

namespace App\Console\Commands\Concurs;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Fallback theme picker if winner doesn't choose by 21:00
 * REBUILT PER COMPENDIUM V2 (2025-10-20)
 * 
 * LOGIC:
 * 1. Only run if window='waiting_theme'
 * 2. Pick random theme from theme_pools + random category
 * 3. Create NEW submission cycle (opens immediately)
 * 4. Unlock window
 * 5. Contest continues seamlessly
 */
class FallbackTheme extends Command
{
    protected $signature   = 'concurs:fallback-theme';
    protected $description = "Auto-generate fallback theme if winner doesn't choose by 21:00";

    public function handle(): int
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        // Guard: only run when window='waiting_theme'
        $window = DB::table('contest_flags')->where('name', 'window')->value('value');
        if ($window !== 'waiting_theme') {
            $this->info('Window not waiting_theme. Skipping.');
            return self::SUCCESS;
        }

        // Guard: only run after 21:00
        if ($now->hour < 21) {
            $this->info('Not yet 21:00. Skipping.');
            return self::SUCCESS;
        }

        DB::beginTransaction();
        try {
            // 1) PICK RANDOM THEME from theme_pools
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

            // 2) CREATE THEME in contest_themes
            $themeId = DB::table('contest_themes')->insertGetId([
                'name'              => $themeText,
                'chosen_by_user_id' => null, // Fallback (system-generated)
                'created_at'        => $now,
            ]);

            // 3) CREATE NEW SUBMISSION CYCLE
            $next2000 = $now->copy()->addDay()->setTime(20, 0, 0);
            
            DB::table('contest_cycles')->insert([
                'theme_id'      => $themeId,
                'theme_text'    => $themeText,
                'lane'          => 'submission',
                'status'        => 'open',
                'start_at'      => $now,
                'submit_end_at' => $next2000,
                'vote_end_at'   => null, // Will be set when promoted to voting
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            // 4) UNLOCK WINDOW
            DB::table('contest_flags')->updateOrInsert(
                ['name' => 'window'],
                ['value' => null, 'updated_at' => $now]
            );

            // 5) AUDIT LOG
            $seed = crc32("fallback|{$themeText}|" . $now->format('Y-m-d'));
            DB::table('contest_audit_logs')->insert([
                'event_type' => 'fallback_theme',
                'cycle_id'   => null,
                'seed'       => $seed,
                'details'    => json_encode([
                    'theme_id'   => $themeId,
                    'theme_name' => $themeText,
                    'category'   => $category,
                ]),
                'created_at' => $now,
            ]);

            DB::commit();
            $this->info("✅ Fallback theme created: {$themeText}");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("❌ Error creating fallback theme: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
