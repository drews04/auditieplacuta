<?php

namespace App\Http\Controllers;

use App\Models\CompetitionTheme; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ConcursTemaController extends Controller
{
    // Show form
    public function create(Request $request)
    {
        return view('concurs.alege-tema', [
            'winnerName' => Auth::user()->name ?? 'campion',
            'categories' => [
                ['code' => 'CSD',     'label' => 'CSD — Cu și despre',    'disabled' => false],
                ['code' => 'ITC',     'label' => 'ITC — În titlu cuvânt', 'disabled' => false],
                ['code' => 'Artiști', 'label' => 'Artiști',                'disabled' => false],
                ['code' => 'Genuri',  'label' => 'Genuri',                 'disabled' => false],
            ],
        ]);
    }

    // Handle submit
    public function store(\Illuminate\Http\Request $request)
{
    $today = \Carbon\Carbon::today();
    $now   = \Carbon\Carbon::now();

    // must be today's winner
    $winner = \App\Models\Winner::whereDate('contest_date', $today)->first();
    if (!$winner || !auth()->check() || (int)$winner->user_id !== (int)auth()->id()) {
        return redirect()->route('concurs')->with('status', 'Nu ai permisiunea să alegi tema.');
    }

    // only before 21:00
    if ($now->gte($today->copy()->setTime(21, 0))) {
        return redirect()->route('concurs')->with('status', 'Fereastra s-a închis. Fallback-ul va alege tema.');
    }

    // validate JUST your two inputs
    $data = $request->validate([
        'category' => ['required','string','max:40'],
        'theme'    => ['required','string','max:120'],
    ]);

    // next weekday (skip weekend)
    $next = $today->copy();
    do { $next->addDay(); } while (in_array($next->dayOfWeekIso, [6, 7]));

    // if already set, succeed idempotently
    if (\App\Models\ContestTheme::whereDate('contest_date', $next->toDateString())->exists()) {
        session()->forget('ap_show_theme_modal');
        return redirect()->route('concurs')->with('tema_success', true);
    }

    // ensure a ThemePool row exists for this manual choice (creates if missing)
    $pool = \App\Models\ThemePool::firstOrCreate(
        ['name' => trim($data['theme']), 'category' => trim($data['category'])],
        ['active' => true]
    );

    // write the theme the Concurs page reads
    \App\Models\ContestTheme::create([
        'contest_date'     => $next->toDateString(),
        'theme_pool_id'    => (int)$pool->id,
        'picked_by_winner' => true,
    ]);

    // mark winner + stop reopening the modal
    $winner->theme_chosen = 1;
    $winner->save();
    session()->forget('ap_show_theme_modal');

    // success toast; Concurs page will now HIDE the list & SHOW upload for tomorrow
    return redirect()->route('concurs')->with('tema_success', true);
}

    

/**
 * Returns next contest date (Mon–Fri) as Y-m-d.
 */
protected function nextContestDate(): string
{
    $d = Carbon::now();
    // contest is for "tomorrow" but skip Sat/Sun
    do {
        $d = $d->addDay();
    } while (in_array($d->dayOfWeekIso, [6, 7])); // 6=Sat,7=Sun

    return $d->toDateString();
}

}
