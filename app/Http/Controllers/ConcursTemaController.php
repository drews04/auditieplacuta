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
     * Winner submits the theme → we auto-schedule the next contest cycle.
     *
     * Rules encoded here:
     * - If submitted on a weekend → schedule next Monday.
     * - If submitted on a weekday after 19:30 → schedule the next weekday (skip Sat/Sun).
     * - Otherwise (before 19:30) → schedule today.
     * Windows (2-phase):
     *   SUBMIT open:  D 00:00  →  SUBMIT close: D 19:30
     *   VOTE  start:  D 20:00  →  VOTE  end:    D+1 20:00
     */
    public function store(Request $request)
    {
        // Validate form
        $data = $request->validate([
            'category' => ['required', 'string', 'max:40'],
            'theme'    => ['required', 'string', 'max:120'],
        ]);

        $category  = trim($data['category']);
        $theme     = trim($data['theme']);
        $themeText = "{$category} — {$theme}";
        $now       = Carbon::now();

        // Find most recent finished voting cycle
        $finished = ContestCycle::query()
            ->where('vote_end_at', '<=', $now)
            ->orderByDesc('vote_end_at')
            ->first();

        if (!$finished) {
            return redirect()->route('concurs')
                ->with('status', 'Nu există o rundă încheiată pentru care să alegi tema.');
        }

        // Check user is the winner for that finished cycle
        $win = Winner::where('cycle_id', $finished->id)->first();
        if (!$win || !Auth::check() || (int)$win->user_id !== (int)Auth::id()) {
            return redirect()->route('concurs')
                ->with('status', 'Nu ai permisiunea să alegi tema.');
        }

        // Only within 1 hour after voting ended AND not already chosen
        $deadline = $finished->vote_end_at->copy()->addHour(); // e.g. 21:00 if vote_end_at is 20:00
        if ($now->gt($deadline) || $win->theme_chosen) {
            return redirect()->route('concurs')
                ->with('status', 'Fereastra de alegere a temei s-a închis sau tema a fost deja aleasă.');
        }

        // Decide the submissions day D (Mon–Fri only)
        if ($now->isWeekend()) {
            // winner picked on weekend → schedule next Monday
            $D = $now->copy()->next(Carbon::MONDAY)->startOfDay();
        } else {
            // After 19:30 today → schedule next weekday
            if ($now->gte($now->copy()->setTime(19, 30))) {
                $D = $now->copy()->addDay()->startOfDay();
                while (in_array($D->dayOfWeekIso, [6, 7])) {
                    $D->addDay();
                }
            } else {
                // Before 19:30 → today
                $D = $now->copy()->startOfDay();
            }
        }

        // Build the two windows
        $start_at      = $D->copy()->setTime(0, 0);        // SUBMIT open
        $submit_end_at = $D->copy()->setTime(19, 30);      // SUBMIT close
        $vote_start_at = $D->copy()->setTime(20, 0);       // VOTE starts
        $vote_end_at   = $D->copy()->addDay()->setTime(20, 0); // VOTE ends next day

        // Persist everything atomically
        DB::transaction(function () use (
            $category, $theme, $themeText, $start_at, $submit_end_at, $vote_start_at, $vote_end_at, $win, $D
        ) {
            // 1) ThemePool (so we can track / reuse)
            $pool = ThemePool::firstOrCreate(
                ['name' => $theme, 'category' => $category],
                ['active' => true]
            );

            // 2) ContestTheme for D (persist name, category, and who chose it)
            $ct = ContestTheme::updateOrCreate(
                ['contest_date' => $D->toDateString()],
                [
                    'name'              => $theme,
                    'category'          => $category,
                    'theme_pool_id'     => (int)$pool->id,
                    'picked_by_winner'  => true,
                    // chosen_by_user_id is set below to bypass any fillable/guarded issues
                ]
            );

            // Force-set chooser (safe even if model is guarded)
            $ct->chosen_by_user_id = (int)Auth::id();
            $ct->save();

            // 3) ContestCycle (idempotent create/update for the day D)
            $existing = ContestCycle::where('start_at', $start_at)->first();
            if ($existing) {
                // Update windows & theme text if something changed
                $existing->fill([
                    'submit_end_at' => $submit_end_at,
                    'vote_start_at' => $vote_start_at,
                    'vote_end_at'   => $vote_end_at,
                    'theme_text'    => $themeText,
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

            // 4) Mark winner as done picking (stops the modal nag)
            if (!$win->theme_chosen) {
                $win->theme_chosen = true;
                $win->save();
            }
        });

        // Stop reopening the modal and show success toast
        session()->forget('ap_show_theme_modal');

        return redirect()->route('concurs')->with('tema_success', true);
    }

    /**
     * Helper: next contest date (Mon–Fri) as Y-m-d.
     */
    protected function nextContestDate(): string
    {
        $d = Carbon::now();
        do { $d = $d->addDay(); } while (in_array($d->dayOfWeekIso, [6, 7]));
        return $d->toDateString();
    }
}
