<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConcursAdminController extends Controller
{
    /** Admin status widget (shows current submission/voting windows) */
    public function dashboard()
    {
        $now = Carbon::now()->timezone(config('app.timezone'));

        $cycleSubmit = DB::table('contest_cycles')
            ->where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();

        $cycleVote = DB::table('contest_cycles')
            ->where('vote_start_at', '<=', $now)
            ->where('vote_end_at', '>', $now)
            ->orderByDesc('vote_start_at')
            ->first();

        return view('admin.concurs', compact('cycleSubmit','cycleVote'));
    }

    /**
     * START (safe & idempotent)
     * - Does NOT delete archives or past cycles.
     * - Ensures two days are seeded:
     *   • TODAY: submissions open (00:00→20:00), vote 20:00→tomorrow 20:00
     *   • TOMORROW: submissions open (00:00→20:00), vote 20:00→day-after 20:00
     * - Skips weekends: if today is weekend, first submit opens next weekday 00:00.
     */
    public function start(Request $request)
{
    if (!auth()->check() || !optional(auth()->user())->is_admin) {
        abort(403, 'Admin access required');
    }

    // accept BOTH your modal fields (theme_a_*/theme_b_*) and the simpler ones
    $themeACategory = trim($request->input('theme_a_category', $request->input('category', '')));
    $themeAName     = trim($request->input('theme_a_name',     $request->input('theme', '')));
    $themeBCategory = trim($request->input('theme_b_category', $request->input('tomorrow_category', '')));
    $themeBName     = trim($request->input('theme_b_name',     $request->input('tomorrow_theme', '')));

    if ($themeAName === '') {
        return back()->with('status', 'Completează măcar Tema A (categoria + numele).')->withInput();
    }

    $themeAText = ($themeACategory !== '' ? ucfirst($themeACategory).' — ' : '') . $themeAName;
    $themeBText = ($themeBCategory !== '' ? ucfirst($themeBCategory).' — ' : '') . ($themeBName !== '' ? $themeBName : $themeAName);

    $tz  = config('app.timezone', 'Europe/Bucharest');
    $now = now()->timezone($tz);

    // anchors
    $today       = $now->copy()->startOfDay();
    $tomorrow    = $today->copy()->addDay();
    $dayAfter    = $tomorrow->copy()->addDay();
    $today2000   = $today->copy()->setTime(20, 0, 0);
    $isAfter2000 = $now->greaterThanOrEqualTo($today2000);

    // optional hard reset
    $forceReset = $request->boolean('force_reset_today');
    if ($forceReset) {
        \DB::transaction(function () use ($now) {
            $activeIds = \DB::table('contest_cycles')
                ->where('vote_end_at', '>', $now)
                ->pluck('id');

            if ($activeIds->isNotEmpty()) {
                \DB::table('votes')->whereIn('cycle_id', $activeIds)->delete();
                \DB::table('songs')->whereIn('cycle_id', $activeIds)->delete();
                \DB::table('winners')->whereIn('cycle_id', $activeIds)->delete();
                \DB::table('contest_cycles')->whereIn('id', $activeIds)->delete();
            }
        });
    }

    \DB::transaction(function () use (
        $themeAText, $themeBText,
        $now, $today, $tomorrow, $dayAfter, $today2000, $isAfter2000
    ) {
        /* =========================
         * THEME A (today's contest)
         * ========================= */
        $themeA = \DB::table('contest_themes')->whereDate('contest_date', $today->toDateString())->first();
        $themeAId = $themeA?->id ?? \DB::table('contest_themes')->insertGetId([
            'name'             => $themeAText,
            'category'         => null,
            'contest_date'     => $today->toDateString(),
            'active'           => 1,
            'picked_by_winner' => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        if ($themeA && empty($themeA->name)) {
            \DB::table('contest_themes')->where('id', $themeAId)->update(['name' => $themeAText, 'updated_at' => now()]);
        }

        // Cycle A times:
        //  - If Start BEFORE 20:00 → classic: submit 00:00→20:00; vote 20:00→tomorrow 20:00
        //  - If Start AT/AFTER 20:00 → **open submissions NOW**; submit ends tomorrow 20:00;
        //                             vote starts tomorrow 00:00; ends day-after 20:00
        if (!$isAfter2000) {
            $aStart     = $today->copy()->startOfDay();
            $aSubEnd    = $today->copy()->setTime(20, 0, 0);
            $aVoteStart = $today->copy()->setTime(20, 0, 0);
            $aVoteEnd   = $tomorrow->copy()->setTime(20, 0, 0);
        } else {
            $aStart     = $now->copy();
            $aSubEnd    = $tomorrow->copy()->setTime(20, 0, 0);
            $aVoteStart = $tomorrow->copy()->startOfDay();       // voting opens at 00:00 tomorrow
            $aVoteEnd   = $dayAfter->copy()->setTime(20, 0, 0);  // and closes next day at 20:00
        }

        $cyA = \DB::table('contest_cycles')->where('contest_theme_id', $themeAId)->first();
        if ($cyA) {
            \DB::table('contest_cycles')->where('id', $cyA->id)->update([
                'theme_text'     => $cyA->theme_text ?: $themeAText,
                'start_at'       => $aStart,
                'submit_end_at'  => $aSubEnd,
                'vote_start_at'  => $aVoteStart,
                'vote_end_at'    => $aVoteEnd,
                'updated_at'     => now(),
            ]);
        } else {
            \DB::table('contest_cycles')->insert([
                'contest_theme_id' => $themeAId,
                'theme_text'       => $themeAText,
                'start_at'         => $aStart,
                'submit_end_at'    => $aSubEnd,
                'vote_start_at'    => $aVoteStart,
                'vote_end_at'      => $aVoteEnd,
                'winner_song_id'   => null,
                'winner_user_id'   => null,
                'winner_decided_at'=> null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        /* =========================
         * THEME B (tomorrow’s contest)
         * Keep the staggered lane alive.
         * ========================= */
        $themeB = \DB::table('contest_themes')->whereDate('contest_date', $tomorrow->toDateString())->first();
        $themeBId = $themeB?->id ?? \DB::table('contest_themes')->insertGetId([
            'name'             => $themeBText,
            'category'         => null,
            'contest_date'     => $tomorrow->toDateString(),
            'active'           => 1,
            'picked_by_winner' => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        if ($themeB && empty($themeB->name)) {
            \DB::table('contest_themes')->where('id', $themeBId)->update(['name' => $themeBText, 'updated_at' => now()]);
        }

        // Cycle B always starts at tomorrow 00:00 (uploads), votes the day after.
        $bStart     = $tomorrow->copy()->startOfDay();
        $bSubEnd    = $tomorrow->copy()->setTime(20, 0, 0);
        $bVoteStart = $tomorrow->copy()->setTime(20, 0, 0);
        $bVoteEnd   = $dayAfter->copy()->setTime(20, 0, 0);

        $cyB = \DB::table('contest_cycles')->where('contest_theme_id', $themeBId)->first();
        if ($cyB) {
            \DB::table('contest_cycles')->where('id', $cyB->id)->update([
                'theme_text'     => $cyB->theme_text ?: $themeBText,
                'start_at'       => $bStart,
                'submit_end_at'  => $bSubEnd,
                'vote_start_at'  => $bVoteStart,
                'vote_end_at'    => $bVoteEnd,
                'updated_at'     => now(),
            ]);
        } else {
            \DB::table('contest_cycles')->insert([
                'contest_theme_id' => $themeBId,
                'theme_text'       => $themeBText,
                'start_at'         => $bStart,
                'submit_end_at'    => $bSubEnd,
                'vote_start_at'    => $bVoteStart,
                'vote_end_at'      => $bVoteEnd,
                'winner_song_id'   => null,
                'winner_user_id'   => null,
                'winner_decided_at'=> null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    });

    return back()->with('status', 'Concurs pornit. Încărcările sunt deschise conform orei de start.');
}

    
    
}
