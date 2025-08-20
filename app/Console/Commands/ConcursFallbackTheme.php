<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\ContestTheme;
use App\Models\ThemePool;
use App\Models\Winner;

class ConcursFallbackTheme extends Command
{
    // 👇 MUST match what Kernel schedules at 21:00
    protected $signature   = 'concurs:fallback-theme';
    protected $description = 'At 21:00, if tomorrow has no theme, set a random active theme (Mon–Fri).';

    public function handle()
    {
        $now = Carbon::now();

        if ($now->isWeekend()) {
            $this->info('Weekend — no fallback.');
            return self::SUCCESS;
        }

        $today = $now->toDateString();

        // Compute next weekday date (skip Sat/Sun)
        $next = Carbon::today();
        do { $next->addDay(); } while (in_array($next->dayOfWeekIso, [6, 7]));
        $nextDate = $next->toDateString();

        // If tomorrow already has a theme, stop
        if (ContestTheme::whereDate('contest_date', $nextDate)->exists()) {
            $this->info("Theme for {$nextDate} already exists — skipping.");
            return self::SUCCESS;
        }

        // Pick random active theme
        $pool = ThemePool::query()
            ->where('active', true)
            ->inRandomOrder()
            ->first();

        if (!$pool) {
            $this->warn('No active ThemePool entries found — cannot set fallback theme.');
            return self::SUCCESS;
        }

        // Create tomorrow's theme (picked_by_winner = false)
        ContestTheme::create([
            'contest_date'     => $nextDate,
            'theme_pool_id'    => (int) $pool->id,
            'picked_by_winner' => false,
        ]);

        // Do not flip theme_chosen=true — winner did not choose
        $this->info("✅ Fallback theme set for {$nextDate}: {$pool->category} — {$pool->name}");
        return self::SUCCESS;
    }
}
