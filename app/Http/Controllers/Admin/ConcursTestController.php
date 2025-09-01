<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConcursAdminController extends Controller
{
    /**
     * Simple admin screen with the Start form.
     * (You can keep using your existing admin blade. This action just feeds it.)
     */
    public function dashboard()
    {
        // Optional: show current open submission and voting cycles (for admin info)
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

        return view('admin.concurs', compact('cycleSubmit', 'cycleVote'));
    }

    /**
     * Pressing Start creates a NEW 48h belt:
     * - 24h submissions (now -> +24h)
     * - 24h voting  (+24h -> +48h)
     *
     * It DOES NOT delete yesterday's data, so voting can run in parallel.
     */
    public function start(Request $request)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Admin access required');
        }

        $request->validate([
            'theme_text' => 'required|string|min:3|max:255',
        ]);

        $now = Carbon::now(); // Europe/Bucharest via app timezone

        // Guard: don't allow overlapping submissions
        $openSubmission = DB::table('contest_cycles')
            ->where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->exists();

        if ($openSubmission) {
            return back()->with('error', 'Există deja o fereastră de înscrieri deschisă.');
        }

        // Define times
        $start      = $now->copy();            // submissions open now
        $submitEnd  = $now->copy()->addDay();  // +24h
        $voteStart  = $submitEnd->copy();      // == submit_end
        $voteEnd    = $voteStart->copy()->addDay(); // +24h

        // Create the cycle
        DB::table('contest_cycles')->insert([
            'start_at'         => $start,
            'submit_end_at'    => $submitEnd,
            'vote_start_at'    => $voteStart,
            'vote_end_at'      => $voteEnd,
            'theme_text'       => (string) $request->input('theme_text'),
            'winner_song_id'   => null,
            'winner_user_id'   => null,
            'winner_decided_at'=> null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // (Optional) tidy only stray records from TODAY with no cycle_id (if you want a super-clean start for uploads today)
        // DB::table('songs')->whereNull('cycle_id')->whereDate('created_at', $now->toDateString())->delete();
        // DB::table('votes')->whereNull('cycle_id')->whereDate('created_at', $now->toDateString())->delete();

        return redirect()->route('admin.concurs')
            ->with('ok', 'Concurs pornit. Înscrierile sunt deschise 24h. Tema: ' . $request->input('theme_text'));
    }
}
