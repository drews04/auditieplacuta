<?php

namespace App\Console\Commands\Concurs;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Health check + Auto-repair for Concurs
 * REBUILT PER COMPENDIUM V2 (2025-10-20)
 * 
 * AUTO-REPAIR LOGIC:
 * - If no cycles exist: seed initial submission cycle
 * - If stuck in waiting_theme past 21:00: trigger fallback
 * - If submission/voting cycles are missing: recreate them
 */
class HealthCheck extends Command
{
    protected $signature = 'concurs:health {--repair : Auto-repair issues}';
    protected $description = 'Health check + auto-repair for Concurs system';

    public function handle(): int
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        $this->info('═══════════════════════════════════════');
        $this->info('  CONCURS HEALTH CHECK');
        $this->info('═══════════════════════════════════════');
        $this->line('Time: ' . $now->toDateTimeString());

        // Window flag
        $window = DB::table('contest_flags')->where('name', 'window')->value('value') ?? '(none)';
        $this->line('Window: ' . $window);
        $this->newLine();

        // Open cycles
        $submit = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->orderByDesc('id')->first();

        $voting = DB::table('contest_cycles')
            ->where('lane', 'voting')
            ->where('status', 'open')
            ->orderByDesc('id')->first();

        // Display status
        $this->table(
            ['Lane', 'Cycle ID', 'Theme', 'Status', 'Start', 'End'],
            [
                [
                    'submission',
                    $submit->id ?? '—',
                    $submit->theme_text ?? '—',
                    $submit->status ?? '—',
                    optional($submit)->start_at,
                    optional($submit)->submit_end_at,
                ],
                [
                    'voting',
                    $voting->id ?? '—',
                    $voting->theme_text ?? '—',
                    $voting->status ?? '—',
                    optional($voting)->start_at,
                    optional($voting)->vote_end_at,
                ],
            ]
        );

        // Last finished + winner
        $finished = DB::table('contest_cycles')
            ->where('status', 'closed')
            ->orderByDesc('vote_end_at')->first();

        $winner = $finished
            ? DB::table('winners')->where('cycle_id', $finished->id)->first()
            : null;

        if ($finished) {
            $this->table(
                ['Last Finished', 'Winner Song', 'Winner User', 'Method'],
                [[
                    $finished->id,
                    $winner->song_id  ?? '—',
                    $winner->user_id  ?? '—',
                    $winner->decide_method ?? 'normal',
                ]]
            );
        }

        // ═══════════════════════════════════════
        // AUTO-REPAIR
        // ═══════════════════════════════════════

        if (!$this->option('repair')) {
            $this->newLine();
            $this->comment('💡 Run with --repair to auto-fix issues');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('🔧 AUTO-REPAIR ENABLED');
        $this->newLine();

        $repaired = false;

        // REPAIR 1: No cycles exist → seed initial submission cycle
        if (!$submit && !$voting) {
            $this->warn('⚠️  No cycles exist. Creating initial submission cycle...');
            
            $categories = ['CSD', 'ITC', 'Artiști', 'Genuri'];
            $themeName = 'Kickoff';
            $category = $categories[array_rand($categories)];
            $themeText = "{$category} — {$themeName}";
            $next2000 = $now->copy()->setTime(20, 0, 0);
            if ($now->hour >= 20) {
                $next2000->addDay();
            }

            DB::beginTransaction();
            try {
                $themeId = DB::table('contest_themes')->insertGetId([
                    'name'              => $themeText,
                    'chosen_by_user_id' => null,
                    'created_at'        => $now,
                ]);

                DB::table('contest_cycles')->insert([
                    'theme_id'      => $themeId,
                    'theme_text'    => $themeText,
                    'lane'          => 'submission',
                    'status'        => 'open',
                    'start_at'      => $now,
                    'submit_end_at' => $next2000,
                    'vote_end_at'   => null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);

                DB::table('contest_flags')->updateOrInsert(
                    ['name' => 'window'],
                    ['value' => null, 'updated_at' => $now]
                );

                DB::commit();
                $this->info('✅ Initial submission cycle created.');
                $repaired = true;
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error('❌ Failed to create initial cycle: ' . $e->getMessage());
            }
        }

        // REPAIR 2: Stuck in waiting_theme past 21:00 → trigger fallback
        if ($window === 'waiting_theme' && $now->hour >= 21) {
            $this->warn('⚠️  Stuck in waiting_theme past 21:00. Triggering fallback...');
            try {
                \Artisan::call('concurs:fallback-theme');
                $this->info('✅ Fallback theme triggered.');
                $repaired = true;
            } catch (\Throwable $e) {
                $this->error('❌ Fallback failed: ' . $e->getMessage());
            }
        }

        // REPAIR 3: Submission cycle expired but no voting cycle → promote it
        if ($submit && Carbon::parse($submit->submit_end_at)->lte($now) && !$voting) {
            $this->warn('⚠️  Submission cycle expired but no voting cycle. Promoting...');
            
            $next2000 = $now->copy()->addDay()->setTime(20, 0, 0);
            
            DB::table('contest_cycles')
                ->where('id', $submit->id)
                ->update([
                    'lane'        => 'voting',
                    'vote_end_at' => $next2000,
                    'updated_at'  => $now,
                ]);
            
            $this->info('✅ Submission cycle promoted to voting.');
            $repaired = true;
        }

        if (!$repaired) {
            $this->info('✅ System healthy. No repairs needed.');
        }

        return self::SUCCESS;
    }
}
