<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ConcursAdminController extends Controller
{
    /** Admin status widget (shows current submission/voting windows) */
    public function dashboard()
    {
        $now = Carbon::now();

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
     * HARD RESET + START:
     * - Wipes any active/upcoming cycles (their songs, votes, winners, tiebreaks)
     * - Creates a fresh cycle with the proper windows:
     *     submissions close at 19:30 (weekday),
     *     voting 20:00 (same day) → next weekday 20:00.
     */
    public function start(Request $request)
    {
        if (!auth()->check() || !optional(auth()->user())->is_admin) {
            abort(403, 'Admin access required');
        }

        $data = $request->validate([
            'category' => ['required','string','max:40'],
            'theme'    => ['required','string','max:120'],
        ]);

        $themeText = trim($data['category']).' — '.trim($data['theme']);
        $now = Carbon::now()->timezone(config('app.timezone'));

        // ---- wipe active/upcoming rounds completely ----
        DB::transaction(function () use ($now) {
            // any cycle not finished yet
            $activeIds = DB::table('contest_cycles')
                ->where('vote_end_at', '>', $now)
                ->pluck('id');

            if ($activeIds->isNotEmpty()) {
                // delete in FK-safe order
                DB::table('votes')->whereIn('cycle_id', $activeIds)->delete();
                DB::table('songs')->whereIn('cycle_id', $activeIds)->delete();
                DB::table('winners')->whereIn('cycle_id', $activeIds)->delete();
                DB::table('contest_cycles')->whereIn('id', $activeIds)->delete();
            }

            // clear any tiebreaks from today forward
            if (Schema::hasTable('tiebreaks')) {
                DB::table('tiebreaks')
                    ->whereDate('contest_date', '>=', $now->toDateString())
                    ->delete();
            }
        });

        // ---- helpers to get next valid weekday moments ----
        $nextWeekdayAt = function (Carbon $from, int $hh, int $mm) {
            $t = $from->copy()->setTime($hh, $mm, 0);

            // if weekend OR already past target time today → push forward
            if ($from->isWeekend() || $from->gte($t)) {
                do { $t->addDay(); } while (in_array($t->dayOfWeekIso, [6, 7], true));
                $t->setTime($hh, $mm, 0);
            }
            return $t;
        };

        $nextWeekday20 = function (Carbon $from) {
            $t = $from->copy();
            // advance to next day first
            $t->addDay();
            while (in_array($t->dayOfWeekIso, [6, 7], true)) {
                $t->addDay();
            }
            return $t->setTime(20, 0, 0);
        };

       // If weekend, don't open submissions now—schedule the cycle to start next weekday 00:00
        $start = $now->copy();
        if ($now->isWeekend()) {
            // next Monday (or next weekday) at 00:00
            do { $start->addDay(); } while (in_array($start->dayOfWeekIso, [6,7], true));
            $start->startOfDay();
        }
        // submissions close at 19:30 on the next valid weekday moment (or today if before 19:30 and weekday)
        $submitEnd = $nextWeekdayAt($now, 19, 30);

        // voting opens same day at 20:00 (if submitEnd is today) — otherwise at 20:00 of that submitEnd date
        $voteStart = $submitEnd->copy()->setTime(20, 0, 0);

        // voting closes next weekday at 20:00
        $voteEnd   = $nextWeekday20($voteStart);

        // ---- create fresh cycle ----
        DB::table('contest_cycles')->insert([
            'start_at'          => $start,
            'submit_end_at'     => $submitEnd,
            'vote_start_at'     => $voteStart,
            'vote_end_at'       => $voteEnd,
            'theme_text'        => $themeText,
            'winner_song_id'    => null,
            'winner_user_id'    => null,
            'winner_decided_at' => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return back()->with('status', 'Concurs resetat și pornit. Înscrierile sunt deschise acum.');
    }
}
