<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\ContestCycle;
use App\Models\ContestTheme;
use App\Models\ThemePool;
use App\Models\Winner;

class ConcursFallbackTheme extends Command
{
    protected $signature   = 'concurs:fallback-theme';
    protected $description = 'If the winner did not choose a theme by +1h after vote_end_at, pick a fallback and start the next cycle immediately (upload+vote open).';

    public function handle(): int
    {
        $now = Carbon::now(config('app.timezone', 'Europe/Bucharest'));

        // 1) Most recent finished cycle (voting already ended)
        $finished = ContestCycle::where('vote_end_at', '<=', $now)
            ->orderByDesc('vote_end_at')
            ->first();

        if (!$finished) {
            $this->line('No finished cycle found.');
            return self::SUCCESS;
        }

        // 2) If a winner exists and already chose a theme, stop
        $winner = Winner::where('cycle_id', $finished->id)->first();
        if ($winner && $winner->theme_chosen) {
            $this->line('Theme already chosen by winner.');
            return self::SUCCESS;
        }

        // 3) Only after the 1h window (e.g., 20:00 → 21:00)
        $deadline = $finished->vote_end_at->copy()->addHour();
        if ($now->lt($deadline)) {
            $this->line('Pick window still open; skipping.');
            return self::SUCCESS;
        }

        // 4) Compute next cycle times (no weekend skip — runs daily)
        $voteEndDay   = $finished->vote_end_at->copy()->addDay()->startOfDay();
        $vote_end_at   = $voteEndDay->copy()->setTime(20, 0);
        $submit_end_at = $voteEndDay->copy()->setTime(19, 30);

        // Start new cycle immediately (upload+vote open now)
        $start_at      = $now->copy();
        $vote_start_at = $now->copy();

        // 5) If next cycle already exists with a theme, stop (mark winner if needed)
        $existingCycle = ContestCycle::whereBetween('vote_end_at', [
                $now->copy()->startOfDay(),
                $vote_end_at->copy()->endOfDay()
            ])
            ->orderByDesc('vote_end_at')
            ->first();

        if ($existingCycle && $existingCycle->theme_text) {
            if ($winner) { 
                $winner->theme_chosen = true; 
                $winner->save(); 
            }
            $this->line('Upcoming cycle already scheduled with theme. Winner marked (if present).');
            return self::SUCCESS;
        }

        // 6) Choose a fallback from pool (avoid last 14 days if possible)
        $recentPoolIds = ContestTheme::where('contest_date', '>=', $now->copy()->subDays(14)->toDateString())
            ->pluck('theme_pool_id')
            ->filter()
            ->all();

        $q = ThemePool::query()->where('active', true);
        if (!empty($recentPoolIds)) {
            $q->whereNotIn('id', $recentPoolIds);
        }

        $pool = $q->inRandomOrder()->first();
        if (!$pool) {
            // Absolute fallback if pool empty
            $pool = ThemePool::firstOrCreate(
                ['name' => 'Muzică liberă', 'category' => 'Genuri'],
                ['active' => true]
            );
        }

        // 7) Create ContestTheme for new cycle
        $ct = ContestTheme::updateOrCreate(
            ['contest_date' => $voteEndDay->toDateString()],
            [
                'name'             => $pool->name,
                'category'         => $pool->category,
                'theme_pool_id'    => (int) $pool->id,
                'picked_by_winner' => false,
            ]
        );

        // 8) Create/Update cycle: start NOW; uploads close next day 19:30; votes close next day 20:00
        $themeText = "{$pool->category} — {$pool->name}";

        if ($existingCycle) {
            $existingCycle->fill([
                'start_at'         => $start_at,
                'submit_end_at'    => $submit_end_at,
                'vote_start_at'    => $vote_start_at,
                'vote_end_at'      => $vote_end_at,
                'theme_text'       => $themeText,
                'contest_theme_id' => $ct->id,
            ])->save();
        } else {
            ContestCycle::create([
                'start_at'         => $start_at,
                'submit_end_at'    => $submit_end_at,
                'vote_start_at'    => $vote_start_at,
                'vote_end_at'      => $vote_end_at,
                'theme_text'       => $themeText,
                'contest_theme_id' => $ct->id,
            ]);
        }

        // 9) Mark winner as "theme chosen" to stop overlay
        if ($winner) {
            $winner->theme_chosen = true;
            $winner->save();
        }

        $this->info("✅ Fallback started NOW; closes {$vote_end_at->format('Y-m-d H:i')} with theme: {$themeText}");
        return self::SUCCESS;
    }
}
