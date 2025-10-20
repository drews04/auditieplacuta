<?php

namespace App\Http\Controllers\Concurs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\ContestCycle;
use App\Models\Winner;
use App\Models\Song;
use App\Models\Vote;

/**
 * Archive pages for past competitions
 */
class ArchiveController extends Controller
{
    /**
     * Paginated list of finished competitions (newest first)
     */
    public function index(Request $request)
    {
        $now = Carbon::now();

        // Finished cycles only
        $cycles = ContestCycle::query()
            ->where('vote_end_at', '<=', $now)
            ->orderByDesc('vote_end_at')
            ->paginate(10);

        // Attach winner snapshot if present (falls back to computed)
        $winners = Winner::whereIn('cycle_id', $cycles->pluck('id'))->get()->keyBy('cycle_id');

        $items = $cycles->getCollection()->map(function ($c) use ($winners) {
            $w = $winners->get($c->id);
            if (!$w) {
                $w = $this->computeWinner($c->id);
            }
            $c->winner_snapshot = $w; // may be null if no votes
            return $c;
        });

        $cycles->setCollection($items);

        return view('concurs.archive.index', compact('cycles'));
    }

    /**
     * Detail page for one competition (standings + who voted)
     */
    public function show(string $date)
    {
        $now = Carbon::now();

        // We key archive pages by the day voting ENDED (YYYY-MM-DD)
        $cycle = ContestCycle::query()
            ->whereDate('vote_end_at', $date)
            ->where('vote_end_at', '<=', $now) // safety: finished only
            ->firstOrFail();

        // Winner snapshot (or compute if missing)
        $winner = Winner::where('cycle_id', $cycle->id)->first()
            ?? $this->computeWinner($cycle->id);

        // All songs in this cycle
        $songs = Song::with('user')
            ->where('cycle_id', $cycle->id)
            ->orderBy('id')
            ->get();

        // All votes (one per user per cycle)
        $votes = Vote::with('user')
            ->where('cycle_id', $cycle->id)
            ->get()
            ->groupBy('song_id'); // group voters per song

        // Standings (by vote count desc)
        $standings = $songs->map(function ($song) use ($votes) {
            $voters = $votes->get($song->id, collect());
            $song->vote_count = $voters->count();
            $song->voters = $voters->pluck('user'); // collection of User models
            return $song;
        })->sortByDesc('vote_count')->values();

        // Prev / Next finished cycles for arrow navigation
        $prev = ContestCycle::where('vote_end_at', '<', $cycle->vote_end_at)
            ->orderByDesc('vote_end_at')->first();

        $next = ContestCycle::where('vote_end_at', '>', $cycle->vote_end_at)
            ->where('vote_end_at', '<=', $now)
            ->orderBy('vote_end_at')->first();

        return view('concurs.archive.show', compact('cycle', 'winner', 'standings', 'prev', 'next'));
    }

    /**
     * JSON endpoint: voters for a specific song in a finished cycle
     */
    public function votersJson(string $date, int $songId)
    {
        $now = Carbon::now();

        // Find finished cycle by the date its voting ended (YYYY-MM-DD)
        $cycle = ContestCycle::query()
            ->whereDate('vote_end_at', $date)
            ->where('vote_end_at', '<=', $now)
            ->firstOrFail();

        // Ensure the song belongs to this cycle
        $song = Song::with('user:id,name')
            ->where('id', $songId)
            ->where('cycle_id', $cycle->id)
            ->firstOrFail();

        // Pull voters for that song within this cycle
        $votes = Vote::with('user:id,name')
            ->where('cycle_id', $cycle->id)
            ->where('song_id', $song->id)
            ->get();

        $voters = $votes->map(function ($v) {
            return [
                'id'   => optional($v->user)->id,
                'name' => optional($v->user)->name ?? '—',
            ];
        })->values();

        return response()->json([
            'cycle_id'   => $cycle->id,
            'date'       => $cycle->vote_end_at->toDateString(),
            'song'       => [
                'id'    => $song->id,
                'title' => $song->title,
                'user'  => [
                    'id'   => optional($song->user)->id,
                    'name' => optional($song->user)->name ?? '—',
                ],
            ],
            'vote_count' => $voters->count(),
            'voters'     => $voters,
        ]);
    }

    /**
     * Compute winner if the snapshot row is missing
     * Returns a lightweight object with: song_id, user_id, vote_count, song, user
     */
    protected function computeWinner(int $cycleId)
    {
        $top = Vote::select('song_id', DB::raw('COUNT(*) as vc'))
            ->where('cycle_id', $cycleId)
            ->groupBy('song_id')
            ->orderByDesc('vc')
            ->orderBy('song_id') // stable
            ->first();

        if (!$top) return null;

        $song = Song::with('user')->find($top->song_id);
        if (!$song) return null;

        return (object) [
            'cycle_id'   => $cycleId,
            'song_id'    => $song->id,
            'user_id'    => $song->user_id,
            'vote_count' => (int) $top->vc,
            'song'       => $song,
            'user'       => $song->user,
        ];
    }
}

