<?php

namespace App\Http\Controllers\Header\Concurs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ContestCycle;
use App\Models\Song;

class ConcursController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // Find current submission cycle (today's cycle)
        $cycleSubmit = ContestCycle::where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();

        // Find current voting cycle (yesterday's cycle)
        $cycleVote = ContestCycle::where('vote_start_at', '<=', $now)
            ->where('vote_end_at', '>', $now)
            ->orderByDesc('vote_start_at')
            ->first();

        // Load songs for each cycle
        $songsSubmit = collect();
        if ($cycleSubmit) {
            $songsSubmit = Song::where('cycle_id', $cycleSubmit->id)
                ->orderBy('id')
                ->get();
        }

        $songsVote = collect();
        if ($cycleVote) {
            $songsVote = Song::where('cycle_id', $cycleVote->id)
                ->orderBy('id')
                ->get();
        }

        // Compute simple booleans
        $submissionsOpen = (bool) $cycleSubmit;
        $votingOpen = (bool) $cycleVote;

        return view('concurs.concurs', compact(
            'cycleSubmit', 'cycleVote', 'songsSubmit', 'songsVote', 
            'submissionsOpen', 'votingOpen'
        ));
    }
}