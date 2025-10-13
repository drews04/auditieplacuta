<?php

namespace App\Services;

use App\Models\ContestCycle;
use Carbon\Carbon;

class ContestScheduler
{
    /**
     * Create a new cycle where uploads & voting start immediately
     * and both end tomorrow at 20:00 (local tz).
     */
    public function startInstantCycle(string $themeText, ?int $contestThemeId = null): ContestCycle
    {
        $now = Carbon::now();
        // Vote end tomorrow 20:00
        $voteEnd = Carbon::tomorrow()->setTime(20, 0, 0);

        return ContestCycle::create([
            'theme_text'        => $themeText,
            'contest_theme_id'  => $contestThemeId, // nullable if “free text”
            'start_at'          => $now,
            'vote_start_at'     => $now,
            // uploads are allowed the entire time while voting is open
            'submit_end_at'     => $voteEnd,
            'vote_end_at'       => $voteEnd,
        ]);
    }
}
