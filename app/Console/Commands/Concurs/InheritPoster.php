<?php

namespace App\Console\Commands\Concurs;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\ContestCycle;

/**
 * At the moment voting opens, copy today's UPLOAD poster onto the CURRENT voting cycle if missing
 * TODO (Phase 7): Trigger instantly when winner picks theme, not at 00:02
 */
class InheritPoster extends Command
{
    protected $signature   = 'concurs:inherit-poster';
    protected $description = 'Copy upload poster to voting cycle when voting opens.';

    public function handle(): int
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        // Find active UPLOAD cycle (poster lives here)
        $upload = ContestCycle::where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->whereNotNull('poster_url')
            ->orderByDesc('start_at')
            ->first();

        // Find active VOTE cycle
        $vote = ContestCycle::where('vote_end_at', '>', $now)
            ->orderByDesc('id')
            ->first();

        if (!$upload || empty($upload->poster_url)) {
            $this->line('No active UPLOAD poster found. Nothing to inherit.');
            return Command::SUCCESS;
        }

        if (!$vote) {
            $this->line('No active VOTE cycle found. Skipping.');
            return Command::SUCCESS;
        }

        if (!empty($vote->poster_url)) {
            $this->line('VOTE cycle already has a poster. Skipping.');
            return Command::SUCCESS;
        }

        // Copy file on disk to a new vote_* name
        $srcUrl  = $upload->poster_url;
        $srcPath = str_replace('/storage/', '', parse_url($srcUrl, PHP_URL_PATH) ?: '');

        if ($srcPath && Storage::disk('public')->exists($srcPath)) {
            $copyPath = 'concurs_posters/vote_' . $now->format('Ymd_His') . '_' . basename($srcPath);
            Storage::disk('public')->copy($srcPath, $copyPath);
            $vote->poster_url = Storage::url($copyPath) . '?t=' . time();
        } else {
            // Fallback: reuse same URL with cache-buster
            $vote->poster_url = $srcUrl . (str_contains($srcUrl, '?') ? '&' : '?') . 't=' . time();
        }

        $vote->save();

        $this->info('✅ Poster inherited to the current voting cycle.');
        return Command::SUCCESS;
    }
}

