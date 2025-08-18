<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ConcursTestController extends Controller
{
    public function declareWinnerNow(Request $request)
    {
        // Run the same logic as cron (uses ForceWeekday fake date if enabled)
        Artisan::call('concurs:declare-winner');

        $d = now()->toDateString();

        // Find today's winner row (works whether code checks contest_date or win_date)
        $winner = DB::table('winners')
            ->whereDate('contest_date', $d)
            ->orWhereDate('win_date', $d)
            ->orderByDesc('id')
            ->first();

        // Default flash
        $flash = ['status' => 'Winner logic executed.'];

        if ($winner) {
            $isMe = auth()->id() === (int) $winner->user_id;
            $needsTheme = (int) ($winner->theme_chosen ?? 0) === 0;

            // Tell the view to pop the normal winner→theme modal NOW
            if ($isMe && $needsTheme) {
                $flash['ap_show_theme_modal'] = true;
            }

            $flash['status'] =
                "Winner today is user #{$winner->user_id}, song #{$winner->song_id} ({$winner->vote_count} votes).";
        }

        return redirect()->route('concurs')->with($flash);
    }
}
