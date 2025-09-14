<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\ContestCycle;
use App\Models\ContestTheme;
use App\Models\ThemePool;
use App\Models\Winner;

class ConcursFallbackTheme extends Command
{
    protected $signature   = 'concurs:fallback-theme';
    protected $description = 'If the winner did not choose a theme by +1h after vote_end_at, pick a fallback and schedule the next cycle.';

    public function handle(): int
    {
        $now = Carbon::now();
    
        // 1) Find the most recent finished cycle (voting already ended).
        $finished = ContestCycle::where('vote_end_at', '<=', $now)
            ->orderByDesc('vote_end_at')
            ->first();
    
        if (!$finished) { $this->line('No finished cycle found.'); return self::SUCCESS; }
    
        // 2) Winner row (optional). We do NOT require it anymore.
        $winner = Winner::where('cycle_id', $finished->id)->first();
    
        // 3) If a winner is present and already chose a theme, stop.
        if ($winner && $winner->theme_chosen) {
            $this->line('Theme already chosen by winner.');
            return self::SUCCESS;
        }
    
        // 4) Only after the 1h window (e.g. 20:00 → 21:00).
        $deadline = $finished->vote_end_at->copy()->addHour();
        if ($now->lt($deadline)) {
            $this->line('Pick window still open; skipping.');
            return self::SUCCESS;
        }
    
        // 5) Determine submissions day D (next weekday after the finished round).
        $D = $finished->vote_end_at->copy()->addDay()->startOfDay();
        while (in_array($D->dayOfWeekIso, [6, 7])) { $D->addDay(); } // skip Sat/Sun
    
        // If a cycle already exists for D with a theme, nothing to do (but mark winner chosen if present).
        $existingCycleForD = ContestCycle::whereDate('start_at', $D->toDateString())->first();
        if ($existingCycleForD && $existingCycleForD->theme_text) {
            if ($winner) { $winner->theme_chosen = true; $winner->save(); }
            $this->line('Next day already scheduled with theme. Winner marked (if present).');
            return self::SUCCESS;
        }
    
        // 6) Choose a fallback theme from pool (avoid last 14 days if possible).
        $recentIds = ContestTheme::where('contest_date', '>=', $now->copy()->subDays(14)->toDateString())
            ->pluck('theme_pool_id')->filter()->all();
    
        $q = ThemePool::query()->where('active', true);
        if (!empty($recentIds)) { $q->whereNotIn('id', $recentIds); }
    
        $pool = $q->inRandomOrder()->first();
    
        if (!$pool) {
            // Absolute fallback if pool is empty.
            $pool = ThemePool::firstOrCreate(
                ['name' => 'Muzică liberă', 'category' => 'Genuri'],
                ['active' => true]
            );
        }
    
        // 7) Create/Update ContestTheme for D (picked_by_winner = false).
        $ct = ContestTheme::updateOrCreate(
            ['contest_date' => $D->toDateString()],
            ['theme_pool_id' => (int)$pool->id, 'picked_by_winner' => false]
        );
    
        // 8) Create/update the cycle windows for D.
        // NOTE: keep your current single-cycle windows (submit → vote) unchanged.
        $start_at      = $D->copy()->setTime(0, 0);
        $submit_end_at = $D->copy()->setTime(19, 30);
        $vote_start_at = $D->copy()->setTime(20, 0);
        $vote_end_at   = $D->copy()->addDay()->setTime(20, 0);
    
        $themeText = "{$pool->category} — {$pool->name}";
    
        if ($existingCycleForD) {
            $existingCycleForD->fill([
                'start_at'       => $start_at,
                'submit_end_at'  => $submit_end_at,
                'vote_start_at'  => $vote_start_at,
                'vote_end_at'    => $vote_end_at,
                'theme_text'     => $themeText,
            ])->save();
        } else {
            ContestCycle::create([
                'start_at'       => $start_at,
                'submit_end_at'  => $submit_end_at,
                'vote_start_at'  => $vote_start_at,
                'vote_end_at'    => $vote_end_at,
                'theme_text'     => $themeText,
            ]);
        }
    
        // 9) If a Winner row exists, mark as "theme chosen" so the modal stops nagging.
        if ($winner) {
            $winner->theme_chosen = true;
            $winner->save();
        }
    
        $this->info("✅ Fallback scheduled for {$D->toDateString()}: {$themeText}");
        return self::SUCCESS;
    }
    
}
