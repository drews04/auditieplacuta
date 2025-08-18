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
    public function store(Request $request)
{
        $data = $request->validate([
            'category' => 'required|in:CSD,ITC,Artiști,Genuri',
            'theme'    => 'required|string|min:2|max:80',
        ]);

        // 1) determine next contest day (skip weekend)
        $appliesOn = $this->nextContestDate(); // Y-m-d string

        // 2) persist (upsert) tomorrow’s theme
        CompetitionTheme::updateOrCreate(
            ['applies_on' => $appliesOn],
            [
                'category_code' => $data['category'],
                'title'         => trim($data['theme']),
                'chosen_by'     => Auth::id(),
                'chosen_at'     => now(),
            ]
        );

        // TODO: mark the winner record as theme_chosen = true (when you expose that model/table)

        // 3) redirect back to Concurs with success flag (popup)
        return redirect()
            ->route('concurs')              // existing route in your app
            ->with('tema_success', true);
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
