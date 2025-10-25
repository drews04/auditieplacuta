<?php

namespace App\Http\Controllers\Concurs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Archive Controller - Browse historical contest cycles
 */
class ArchiveController extends Controller
{
    /**
     * Show archived cycle by ID
     * 
     * GET /concurs/arhiva/{cycleId}
     */
    public function show(Request $request, $cycleId = null)
    {
        $tz = config('app.timezone', 'Europe/Bucharest');

        // If no cycleId provided, show latest archived cycle
        if (!$cycleId) {
            $latest = DB::table('contest_archives')
                ->orderByDesc('vote_end_at')
                ->first();
            
            if ($latest) {
                return redirect()->route('concurs.arhiva.show', ['cycleId' => $latest->cycle_id]);
            }
            
            // No archives yet
            return redirect()->route('concurs')->with('info', 'Nu există încă concursuri arhivate.');
        }

        // Get archive entry
        $archive = DB::table('contest_archives')
            ->where('cycle_id', $cycleId)
            ->first();

        if (!$archive) {
            return redirect()->route('concurs')->with('error', 'Concursul arhivat nu a fost găsit.');
        }

        // Decode ranking data
        $rankings = json_decode($archive->ranking_data, true) ?? [];

        // Get winner's other winning posters (for carousel)
        $winnerPosters = DB::table('contest_archives')
            ->where('winner_user_id', $archive->winner_user_id)
            ->orderByDesc('vote_end_at')
            ->select('cycle_id', 'theme_name', 'poster_url', 'vote_end_at')
            ->get();

        // Get prev/next navigation
        $prevArchive = DB::table('contest_archives')
            ->where('vote_end_at', '<', $archive->vote_end_at)
            ->orderByDesc('vote_end_at')
            ->first();

        $nextArchive = DB::table('contest_archives')
            ->where('vote_end_at', '>', $archive->vote_end_at)
            ->orderBy('vote_end_at')
            ->first();

        // Format date (e.g., "24 Octombrie 2025")
        $voteEndDate = Carbon::parse($archive->vote_end_at, $tz);
        $formattedDate = $voteEndDate->translatedFormat('d F Y');

        return view('concurs.arhiva', compact(
            'archive',
            'rankings',
            'winnerPosters',
            'prevArchive',
            'nextArchive',
            'formattedDate'
        ));
    }

    /**
     * Navigate to prev/next archived cycle
     * 
     * GET /concurs/arhiva/navigate/{direction}?current={cycleId}
     */
    public function navigate(Request $request, $direction)
    {
        $currentCycleId = $request->query('current');

        if (!$currentCycleId) {
            // If on main concurs page, go to latest archive
            if ($direction === 'prev') {
                $latest = DB::table('contest_archives')
                    ->orderByDesc('vote_end_at')
                    ->first();
                
                if ($latest) {
                    return redirect()->route('concurs.arhiva.show', ['cycleId' => $latest->cycle_id]);
                }
            }
            
            return redirect()->route('concurs');
        }

        // Get current archive
        $current = DB::table('contest_archives')
            ->where('cycle_id', $currentCycleId)
            ->first();

        if (!$current) {
            return redirect()->route('concurs');
        }

        // Navigate
        if ($direction === 'prev') {
            // Go to older cycle
            $target = DB::table('contest_archives')
                ->where('vote_end_at', '<', $current->vote_end_at)
                ->orderByDesc('vote_end_at')
                ->first();
        } else {
            // Go to newer cycle
            $target = DB::table('contest_archives')
                ->where('vote_end_at', '>', $current->vote_end_at)
                ->orderBy('vote_end_at')
                ->first();
        }

        if ($target) {
            return redirect()->route('concurs.arhiva.show', ['cycleId' => $target->cycle_id]);
        }

        // If no target (reached end), go to main concurs if going "next", stay if going "prev"
        if ($direction === 'next') {
            return redirect()->route('concurs');
        }

        return redirect()->route('concurs.arhiva.show', ['cycleId' => $currentCycleId]);
    }

    /**
     * List all archives (paginated)
     * 
     * GET /concurs/arhiva
     */
    public function index(Request $request)
    {
        $archives = DB::table('contest_archives')
            ->orderByDesc('vote_end_at')
            ->paginate(10);

        return view('concurs.arhiva-index', compact('archives'));
    }
}
