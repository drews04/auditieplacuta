<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\ContestCycle;
use App\Models\Winner;
use App\Models\ContestTheme;
use App\Models\ThemePool;

class ConcursTemaController extends Controller
{
    /**
     * Winner's "choose theme" page.
     */
    public function create(Request $request)
    {
        return view('concurs.alege-tema', [
            'winnerName' => Auth::user()->name ?? 'campion',
            'categories' => [
                ['code' => 'CSD',     'label' => 'CSD — Cu și despre',    'disabled' => false],
                ['code' => 'ITC',     'label' => 'ITC — În titlu cuvânt', 'disabled' => false],
                ['code' => 'Artiști', 'label' => 'Artiști',               'disabled' => false],
                ['code' => 'Genuri',  'label' => 'Genuri',                'disabled' => false],
            ],
        ]);
    }

    /**
     * Winner submits the theme → we auto-schedule the next contest cycle (DAILY, no weekend pause).
     *
     * Windows (2-phase baseline):
     *   SUBMIT open:  D 00:00       → SUBMIT close: D 19:30
     *   VOTE  start:  D+1 00:00     → VOTE  end:    D+1 20:00
     *
     * For winner-picked late theme:
     *   - Start NOW: uploads + voting open immediately
     *   - Both close at (now + 1 day) 20:00 (even Sat/Sun)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'max:40'],
            'theme'    => ['required', 'string', 'max:120'],
        ]);

        $category  = trim($data['category']);     // CSD / ITC / Artisti / Genuri
        $theme     = trim($data['theme']);        // ex: “Dragoste”
        $themeText = "{$category} — {$theme}";
        $tz        = config('app.timezone', 'Europe/Bucharest');
        $now       = Carbon::now($tz);

        // 1) Last finished cycle (the one that ended at 20:00 just now)
        $finished = ContestCycle::query()
            ->where('vote_end_at', '<=', $now)
            ->orderByDesc('vote_end_at')
            ->first();

        if (!$finished) {
            return redirect()->route('concurs')
                ->with('status', 'Nu există o rundă încheiată pentru care să alegi tema.');
        }

        // 2) Ensure current user is that cycle’s winner
        $win = Winner::where('cycle_id', $finished->id)->first();
        if (!$win || !Auth::check() || (int)$win->user_id !== (int)Auth::id()) {
            return redirect()->route('concurs')
                ->with('status', 'Nu ai permisiunea să alegi tema.');
        }

        // 3) Only within +1h after voting ended and not already chosen
        $deadline = $finished->vote_end_at->copy()->addHour(); // 20:00 → 21:00
        if ($now->gt($deadline) || $win->theme_chosen) {
            return redirect()->route('concurs')
                ->with('status', 'Fereastra de alegere a temei s-a închis sau tema a fost deja aleasă.');
        }

        // 4) DAILY mode: close next day at 20:00 (no weekend skip)
        $voteEnd = $now->copy()->addDay()->setTime(20, 0);

        // 5) Start now: both upload & vote open immediately
        $startAt      = $now->copy();
        $submitEndAt  = $voteEnd->copy(); // uploads allowed until vote closes
        $voteStartAt  = $now->copy();
        $voteEndAt    = $voteEnd->copy();

        DB::transaction(function () use (
            $category, $theme, $themeText, $startAt, $submitEndAt, $voteStartAt, $voteEndAt, $win
        ) {
            // ThemePool
            $pool = ThemePool::firstOrCreate(
                ['name' => $theme, 'category' => $category],
                ['active' => true]
            );

            // ContestTheme for this cycle (contest_date = vote_end_at’s date)
            $contestDate = $voteEndAt->toDateString();

            $ct = ContestTheme::updateOrCreate(
                ['contest_date' => $contestDate],
                [
                    'name'             => $theme,
                    'category'         => $category,
                    'theme_pool_id'    => (int)$pool->id,
                    'picked_by_winner' => true,
                ]
            );
            $ct->chosen_by_user_id = (int)Auth::id();
            $ct->save();

            // ContestCycle: starts NOW; uploads & votes open NOW; both end at next day's 20:00
            $cycle = ContestCycle::firstOrNew(['start_at' => $startAt]);
            $cycle->submit_end_at    = $submitEndAt;
            $cycle->vote_start_at    = $voteStartAt;
            $cycle->vote_end_at      = $voteEndAt;
            $cycle->theme_text       = $themeText;
            $cycle->contest_theme_id = $ct->id;
            $cycle->save();

            // Stop the winner modal nag
            if (!$win->theme_chosen) {
                $win->theme_chosen = true;
                $win->save();
            }
        });

        session()->forget('ap_show_theme_modal');
        session()->put('winner_chose_theme', 1);

        $uploadUrl = url('/concurs/upload');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok'       => true,
                'message'  => 'Tema a fost salvată.',
                'redirect' => $uploadUrl,
            ]);
        }

        return redirect($uploadUrl)->with('success', 'Tema a fost salvată. Poți încărca și vota chiar acum.');
    }

    /**
     * Helper: next contest date (DAILY) as Y-m-d.
     */
    protected function nextContestDate(): string
    {
        $tz = config('app.timezone', 'Europe/Bucharest');
        return Carbon::now($tz)->addDay()->toDateString();
    }
}
