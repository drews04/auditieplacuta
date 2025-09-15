<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Song;
use App\Models\Vote;
use App\Models\Winner;
use App\Models\Tiebreak;
use App\Models\ContestTheme;
use App\Models\ThemePool;    // columns: id, name, category
use App\Services\AwardPoints;
use App\Models\Poster;
use App\Models\ContestCycle;

class SongController extends Controller
{
    /** ---------- helpers ---------- */

    // Extract a canonical 11-char YouTube video ID from any common URL format
    private function ytId(string $url): ?string
    {
        $url = trim($url);

        if (preg_match('~youtu\.be/([0-9A-Za-z_-]{11})~', $url, $m)) {
            return $m[1];
        }
        if (preg_match('~(?:v=|/embed/|/v/)([0-9A-Za-z_-]{11})~', $url, $m)) {
            return $m[1];
        }
        if (preg_match('~([0-9A-Za-z_-]{11})~', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    private function userHasVotedToday(?int $userId): bool
    {
        if (!$userId) return false;
        return Vote::where('user_id', $userId)
            ->whereDate('vote_date', Carbon::today())
            ->exists();
    }

    /**
     * Return today's songs that are tied for FIRST place (share the max votes).
     */
    private function todayTieSongs()
    {
        $today = Carbon::today();
        $max = Song::whereDate('competition_date', $today)->max('votes');
        if ($max === null) return collect();

        return Song::whereDate('competition_date', $today)
            ->where('votes', $max)
            ->orderBy('created_at')
            ->get(['id','title','user_id','youtube_url','votes','theme_id']);
    }

    /** ---------- Tiebreak helpers ---------- */

    private function getActiveTiebreakForToday(): ?Tiebreak
    {
        $today = Carbon::today();
        return Tiebreak::whereDate('contest_date', $today)
            ->where('resolved', false)
            ->first();
    }

    private function openTiebreakIfNeeded(): ?Tiebreak
    {
        $today = Carbon::today();
        $now   = Carbon::now();

        if ($now->isWeekend() || $now->lt($today->copy()->setTime(20, 0))) {
            return null;
        }

        if ($tb = $this->getActiveTiebreakForToday()) {
            return $tb;
        }

        $tieSongs = $this->todayTieSongs();
        if ($tieSongs->count() < 2) return null;

        $starts = $today->copy()->setTime(20, 0);
        $ends   = $today->copy()->setTime(20, 30);

        return Tiebreak::create([
            'contest_date' => $today,
            'starts_at'    => $starts,
            'ends_at'      => $ends,
            'song_ids'     => $tieSongs->pluck('id')->values()->all(),
            'resolved'     => false,
        ]);
    }

    private function resolveTiebreakIfEnded(): ?Winner
    {
        $tb = $this->getActiveTiebreakForToday();
        if (!$tb) return null;

        $now = Carbon::now();
        if ($now->lt($tb->ends_at)) return null;

        $counts = Vote::select('song_id', DB::raw('COUNT(*) as total'))
            ->whereIn('song_id', $tb->song_ids)
            ->whereBetween('created_at', [$tb->starts_at, $tb->ends_at])
            ->groupBy('song_id')
            ->orderByDesc('total')
            ->get();

        $winnerSongId = null;
        $finalWasTie  = false;

        if ($counts->isNotEmpty()) {
            $topTotal = $counts->first()->total;
            $leaders  = $counts->where('total', $topTotal)->pluck('song_id')->values();
            if ($leaders->count() === 1) {
                $winnerSongId = $leaders->first();
            } else {
                $lastVote = Vote::whereIn('song_id', $leaders)
                    ->whereBetween('created_at', [$tb->starts_at, $tb->ends_at])
                    ->orderByDesc('created_at')
                    ->first();
                if ($lastVote) {
                    $winnerSongId = $lastVote->song_id;
                    $finalWasTie  = true;
                }
            }
        }

        if (!$winnerSongId) {
            $lastOfDay = Vote::whereIn('song_id', $tb->song_ids)
                ->where('created_at', '<', $tb->ends_at)
                ->orderByDesc('created_at')
                ->first();
            if ($lastOfDay) {
                $winnerSongId = $lastOfDay->song_id;
                $finalWasTie  = true;
            }
        }

        if (!$winnerSongId) {
            $winnerSongId = (int) collect($tb->song_ids)->first();
            $finalWasTie  = true;
        }

        $song = Song::find($winnerSongId);
        if (!$song) {
            $tb->resolved = true;
            $tb->save();
            return null;
        }

        $today = Carbon::today()->toDateString();

        $winner = Winner::create([
            'contest_date'          => $today,
            'user_id'               => $song->user_id,
            'song_id'               => $song->id,
            'vote_count'            => (int) Vote::where('song_id', $song->id)
                                        ->whereDate('vote_date', $today)
                                        ->count(),
            'was_tie'               => true,
            'theme_chosen'          => false,
            'competition_theme_id'  => $song->theme_id ?? null,
        ]);

        if (!$song->is_winner) {
            $song->is_winner = true;
            $song->save();
        }

        $tb->resolved = true;
        $tb->save();

        app(AwardPoints::class)->awardForDate($today);

        return $winner;
    }

    private function finalizeDailyWinnerIfNeeded(): ?Winner
    {
        $today = Carbon::today();

        if (Carbon::now()->isWeekend()) return null;
        if (Carbon::now()->lt($today->copy()->setTime(20, 0))) return null;
        if (Winner::whereDate('contest_date', $today)->exists()) return null;
        if ($this->getActiveTiebreakForToday()) return null;

        $maxVotes = Song::whereDate('competition_date', $today)->max('votes');
        if ($maxVotes === null) return null;

        $leaders = Song::whereDate('competition_date', $today)
            ->where('votes', $maxVotes)
            ->orderBy('created_at')
            ->get(['id','user_id','theme_id']);

        if ($leaders->count() !== 1) return null;

        $song = $leaders->first();

        $winner = Winner::create([
            'contest_date'          => $today->toDateString(),
            'user_id'               => $song->user_id,
            'song_id'               => $song->id,
            'vote_count'            => (int) Vote::where('song_id', $song->id)
                                            ->whereDate('vote_date', $today)
                                            ->count(),
            'was_tie'               => false,
            'theme_chosen'          => false,
            'competition_theme_id'  => $song->theme_id ?? null,
        ]);

        if (!$song->is_winner) {
            $song->is_winner = true;
            $song->save();
        }

        app(AwardPoints::class)->awardForDate($today->toDateString());

        return $winner;
    }

    /** ---------- date helper ---------- */

    private function nextWeekdayContestDate(Carbon $from): Carbon
    {
        $d = $from->copy()->startOfDay();
        do { $d->addDay(); } while (in_array($d->dayOfWeekIso, [6,7])); // Sat/Sun
        return $d;
    }

    /** ---------- pages ---------- */

    /**
     * Main Concurs page.
     */
    public function showTodaySongs(Request $request)
{
    $now = \Carbon\Carbon::now();

    // === Winner strip (last finished round) ===
    $winnerStripCycle = \App\Models\ContestCycle::where('vote_end_at', '<=', $now)
        ->orderByDesc('vote_end_at')
        ->first();

    $winnerStripWinner = null;
    if ($winnerStripCycle) {
        $winnerStripWinner = \App\Models\Winner::where('cycle_id', $winnerStripCycle->id)
            ->with(['user:id,name', 'song:id,title,youtube_url'])
            ->first();
    }

    // === CYCLES (current submission / current voting) ===
    $cycleSubmit = \App\Models\ContestCycle::where('start_at', '<=', $now)
        ->where('submit_end_at', '>', $now)
        ->orderByDesc('start_at')
        ->first();

    $cycleVote = \App\Models\ContestCycle::where('vote_start_at', '<=', $now)
        ->where('vote_end_at', '>', $now)
        ->orderByDesc('vote_start_at')
        ->first();

    // --- GAP FALLBACK: between submit_end_at and vote_start_at (show today's list + timer) ---
    $gapBetweenPhases = false;
    if (!$cycleSubmit && !$cycleVote) {
        $current = \App\Models\ContestCycle::where('start_at','<=',$now)
            ->where('vote_end_at','>',$now)
            ->orderByDesc('start_at')
            ->first();

        if ($current) {
            // Treat as "current submission context" so theme & songs show,
            // but lock uploads; we'll flip flags below.
            $cycleSubmit      = $current;
            $gapBetweenPhases = true;
        }
    }

    /* ---------- ensure submission cycle has a ContestTheme id ---------- */
    if ($cycleSubmit && empty($cycleSubmit->contest_theme_id)) {
        $raw  = (string)($cycleSubmit->theme_text ?? '');
        // If "CSD — Dinamita", keep only the right side ("Dinamita")
        $name = trim($raw);
        if (preg_match('/^\s*([^—-]+)\s*[—-]\s*(.+)$/u', $raw, $m)) {
            $name = trim($m[2] ?? $raw);
        }

       // Ensure only one theme per contest_date
$contestDay = optional($cycleSubmit->submit_end_at)->toDateString() ?? now()->toDateString();

$ct = \App\Models\ContestTheme::firstOrCreate(
    ['contest_date' => $contestDay],
    [
        'name'              => ($name !== '' ? $name : 'Tema'),
        'active'            => true,
        'chosen_by_user_id' => auth()->id(),
    ]
);

        $cycleSubmit->contest_theme_id = $ct->id;
        $cycleSubmit->save();
    }
    /* ------------------------------------------------------------------- */

    // --- fetch the actual themes with likes (for persistence) ---
    $submitTheme = null;
    $voteTheme   = null;
    $authId      = auth()->id();

    if ($cycleSubmit && $cycleSubmit->contest_theme_id) {
        $submitTheme = \App\Models\ContestTheme::query()
            ->withCount('likes')
            ->with(['likes' => fn($q) => $q->where('user_id', $authId ?? 0)])
            ->find($cycleSubmit->contest_theme_id);
    }

    if ($cycleVote && $cycleVote->contest_theme_id) {
        $voteTheme = \App\Models\ContestTheme::query()
            ->withCount('likes')
            ->with(['likes' => fn($q) => $q->where('user_id', $authId ?? 0)])
            ->find($cycleVote->contest_theme_id);
    }
    // --------------------------------------------------------------------

    // Songs per cycle
    $songsSubmit = collect();
    if ($cycleSubmit) {
        $songsSubmit = \App\Models\Song::where('cycle_id', $cycleSubmit->id)
            ->orderBy('id')->get();
    }

    $songsVote = collect();
    if ($cycleVote) {
        $songsVote = \App\Models\Song::where('cycle_id', $cycleVote->id)
            ->orderBy('id')->get();
    }

    // Simple flags
    if ($gapBetweenPhases) {
        $submissionsOpen = false;   // uploads are closed during the gap
        $votingOpen      = false;   // voting not yet open
    } else {
        $submissionsOpen = (bool) $cycleSubmit;
        $votingOpen      = (bool) $cycleVote;
    }

    // When will voting open for the CURRENT context?
    $votingOpensAt = null;
    if ($cycleVote) {
        // already open; keep null
    } elseif ($cycleSubmit) {
        $votingOpensAt = $cycleSubmit->vote_start_at ?? $cycleSubmit->submit_end_at;
        if ($votingOpensAt) {
            $votingOpensAt = $votingOpensAt->copy();
        }
    }

    // Legacy compat
    $today     = \Carbon\Carbon::today();
    $isWeekday = !$now->isWeekend();

    // Per-user flags
    $userHasVotedToday = false;
    if (\Auth::check() && $cycleVote) {
        $userHasVotedToday = \App\Models\Vote::where('user_id', \Auth::id())
            ->where('cycle_id', $cycleVote->id)
            ->exists();
    }

    $userHasUploadedToday = false;
    if (\Auth::check() && $cycleSubmit) {
        $userHasUploadedToday = \App\Models\Song::where('user_id', \Auth::id())
            ->where('cycle_id', $cycleSubmit->id)
            ->exists();
    }

    // Use vote list during voting, submit list otherwise
    $songs = $votingOpen ? $songsVote : $songsSubmit;

    // Simple theme object for header (from submission cycle)
    $theme = null;
    if ($cycleSubmit) {
        $theme = (object) [
            'title'          => $cycleSubmit->theme_text ?? '—',
            'category_code'  => 'GEN',
        ];
    }

    // === WINNER POPUP (only for the winner, within 1h after vote_end_at) ===
    $showWinnerModal = false;
    $winnerCycle = null;

    $finished = \App\Models\ContestCycle::query()
        ->where('vote_end_at', '<=', $now)
        ->orderByDesc('vote_end_at')
        ->first();

    if ($finished) {
        $winner = \App\Models\Winner::where('cycle_id', $finished->id)->first();
        if ($winner && !$winner->theme_chosen) {
            $withinHour = $now->between($finished->vote_end_at, $finished->vote_end_at->copy()->addHour());
            if ($withinHour && \Auth::check() && \Auth::id() === (int) $winner->user_id) {
                $showWinnerModal = true;
                $winnerCycle = $finished;
            }
        }
    }

    // === WEEKEND VIEW (read-only) ===
    $isWeekendView     = (!$submissionsOpen && !$votingOpen) && $now->isWeekend();

    $lastFinishedCycle = null;   // the most recent finished (Friday most likely)
    $lastSongs         = collect();
    $lastWinner        = null;

    $upcomingCycle     = null;   // the next scheduled cycle (e.g., Monday)

    if ($isWeekendView) {
        $lastFinishedCycle = \App\Models\ContestCycle::where('vote_end_at', '<=', $now)
            ->orderByDesc('vote_end_at')
            ->first();

        if ($lastFinishedCycle) {
            $lastSongs  = \App\Models\Song::where('cycle_id', $lastFinishedCycle->id)
                ->orderBy('id')->get();

            $lastWinner = \App\Models\Winner::where('cycle_id', $lastFinishedCycle->id)
                ->first();
        }

        $upcomingCycle = \App\Models\ContestCycle::where('start_at', '>=', $now)
            ->orderBy('start_at')
            ->first();
    }

    // Legacy placeholders still expected by the blade
    $todayWinner       = null;
    $showWinnerPopup   = $showWinnerModal;
    $tomorrowTheme     = null;
    $dayLocked         = $gapBetweenPhases ? true : false; // lock UI in the gap
    $uploadForTomorrow = false;

    return view('concurs', compact(
        'cycleSubmit', 'cycleVote',
        'songsSubmit', 'songsVote',
        'submissionsOpen', 'votingOpen', 'votingOpensAt',
        'showWinnerModal', 'winnerCycle',
        // weekend view extras:
        'isWeekendView', 'lastFinishedCycle', 'lastSongs', 'lastWinner', 'upcomingCycle',
        // legacy compat vars:
        'songs', 'theme', 'userHasVotedToday', 'userHasUploadedToday',
        'isWeekday', 'todayWinner', 'showWinnerPopup', 'tomorrowTheme',
        'dayLocked', 'uploadForTomorrow',
        // winner strip
        'winnerStripCycle', 'winnerStripWinner',
        // themes with likes so counts persist
        'submitTheme', 'voteTheme'
    ));
}

    
    
    /**
     * Versus page.
     */
    public function versus(Request $request)
    {
        $today = Carbon::today();
        $now   = Carbon::now();

        $tb = $this->getActiveTiebreakForToday();

        if (!$tb || !$now->between($tb->starts_at, $tb->ends_at)) {
            if ($tb && $now->greaterThanOrEqualTo($tb->ends_at) && !$tb->resolved) {
                $this->resolveTiebreakIfEnded();
            }
            return redirect()->route('concurs');
        }

        $songs     = Song::whereIn('id', (array) $tb->song_ids)->with('user:id,name')->get();
        $endsAtIso = $tb->ends_at?->toIso8601String();

        return view('concurs_versus', compact('songs', 'endsAtIso'));
    }

    /** ---------- actions ---------- */

    /**
     * Upload a YouTube song (1 per user/day).
     * Requires TODAY to have a ContestTheme row.
     */
    public function uploadSong(Request $request)
{
    $request->validate([
        'youtube_url' => 'required|url',
    ]);

    $user = \Auth::user();
    $now  = \Carbon\Carbon::now();

    // Weekdays only
    if ($now->isWeekend()) {
        return response()->json(['message' => 'Nu se ține concurs în weekend.'], 422);
    }

    // Find the CURRENT submission cycle
    $cycleSubmit = \App\Models\ContestCycle::where('start_at', '<=', $now)
        ->where('submit_end_at', '>', $now)
        ->orderByDesc('start_at')
        ->first();

    if (!$cycleSubmit) {
        return response()->json(['message' => 'Înscrierile nu sunt deschise acum.'], 422);
    }

    // One submission per user per CYCLE
    $already = \App\Models\Song::where('user_id', $user->id)
        ->where('cycle_id', $cycleSubmit->id)
        ->exists();
    if ($already) {
        return redirect()->back()->with('error', 'Ai încărcat deja o melodie.');
    }

    // Normalize URL → YouTube ID (block duplicates within the same cycle)
    $videoId = $this->ytId($request->youtube_url);
    if (!$videoId) {
        return response()->json(['message' => 'Link YouTube invalid.'], 422);
    }

    $dupe = \App\Models\Song::where('cycle_id', $cycleSubmit->id)
        ->get(['youtube_url'])
        ->contains(function ($s) use ($videoId) {
            return $this->ytId($s->youtube_url) === $videoId;
        });
    if ($dupe) {
        return response()->json(['message' => 'Această melodie este deja înscrisă în această rundă.'], 409);
    }

    // Best‑effort title (fallback kept)
    $title = 'Melodie YouTube';
    try {
        $resp = \Illuminate\Support\Facades\Http::timeout(6)->get('https://www.youtube.com/oembed', [
            'url'    => $request->youtube_url,
            'format' => 'json',
        ]);
        if ($resp->ok() && isset($resp['title'])) {
            $title = (string) $resp['title'];
        }
    } catch (\Throwable $e) {}

    // Save inside this CYCLE
    \App\Models\Song::create([
        'user_id'          => $user->id,
        'youtube_url'      => $request->youtube_url,
        'youtube_id'       => $videoId,               // 👈 add this line
        'title'            => $title,
        'votes'            => 0,
        'competition_date' => $now->toDateString(),
        'theme_id'         => null,
        'cycle_id'         => $cycleSubmit->id,
    ]);
    

    return response()->json(['message' => 'Melodie încărcată cu succes.']);
}


    /**
     * Return partial list of today's songs (for AJAX refresh).
     */
    public function todayList()
{
    $now = \Carbon\Carbon::now();

    // Current submission cycle only (the list we refresh after uploads)
    $cycleSubmit = \App\Models\ContestCycle::where('start_at', '<=', $now)
        ->where('submit_end_at', '>', $now)
        ->orderByDesc('start_at')
        ->first();

    // If no submission cycle, return empty list (no uploads should be shown)
    if (!$cycleSubmit) {
        $songs = collect();
        $userHasVotedToday = true; // ensures no vote buttons
        return view('partials.songs_list', [
            'songs' => $songs,
            'userHasVotedToday' => $userHasVotedToday,
            'showVoteButtons' => false,
        ]);
    }

    // Pull ONLY songs from this submission cycle
    $songs = \App\Models\Song::where('cycle_id', $cycleSubmit->id)
        ->orderBy('id')
        ->get();

    // During submissions, vote buttons must be hidden
    $userHasVotedToday = true;

    return view('partials.songs_list', [
        'songs' => $songs,
        'userHasVotedToday' => $userHasVotedToday,
        'showVoteButtons' => false,
    ]);
}

    /**
     * Voting for dual-cycle system.
     */
    public function voteForSong(Request $request)
{
    $request->validate([
        'song_id' => 'required|integer|exists:songs,id'
    ]);

    $user = Auth::user();
    $now  = Carbon::now();
    $song = Song::findOrFail((int)$request->input('song_id'));

    // -------- Weekend guard (unless test mode admin) --------
    if (!(config('ap.test_mode') && $user->is_admin)) {
        if ($now->isWeekend()) {
            return response()->json(['message' => 'Nu se votează în weekend.'], 422);
        }
    }

    // -------- Tiebreak window? (20:00–20:30) --------
    $tb = $this->getActiveTiebreakForToday();
    $activeTiebreak = $tb && $now->between($tb->starts_at, $tb->ends_at);

    if ($activeTiebreak) {
        // must vote only among Versus songs
        if (!in_array($song->id, (array)$tb->song_ids, true)) {
            return response()->json(['message' => 'În Versus poți vota doar melodiile aflate la egalitate.'], 422);
        }

        // one vote per user per tiebreak (unless test mode admin)
        $alreadyVotedThisTiebreak = (config('ap.test_mode') && $user->is_admin) ? false :
            Vote::where('user_id', $user->id)->where('tiebreak_id', $tb->id)->exists();

        if ($alreadyVotedThisTiebreak) {
            return response()->json(['message' => 'Ai votat deja în tiebreak.'], 403);
        }

        // self-vote blocked (unless test mode admin)
        if (!(config('ap.test_mode') && $user->is_admin) && (int)$song->user_id === (int)$user->id) {
            return response()->json(['message' => 'Nu poți vota propria melodie.'], 403);
        }

        Vote::create([
            'user_id'     => $user->id,
            'song_id'     => $song->id,
            'vote_date'   => Carbon::today(),
            'tiebreak_id' => $tb->id,
            // cycle_id left NULL in Versus path (we key by tiebreak_id)
        ]);

        $song->increment('votes');
        return response()->json(['message' => 'Vot înregistrat pentru Versus.']);
    }

    // -------- Normal voting window (00:00–20:00) --------
    // After 20:00 it’s closed (unless test mode admin)
    if (!(config('ap.test_mode') && $user->is_admin)) {
        if ($now->gte($now->copy()->startOfDay()->setTime(20, 0))) {
            return response()->json(['message' => 'Votarea pentru azi s-a închis la 20:00.'], 422);
        }
    }

    // must have an OPEN voting cycle
    $cycleVote = \App\Models\ContestCycle::where('vote_start_at', '<=', $now)
        ->where('vote_end_at', '>', $now)
        ->orderByDesc('vote_start_at')
        ->first();

    if (!$cycleVote) {
        return response()->json(['message' => 'Votarea nu este deschisă.'], 403);
    }

    // song must belong to this voting cycle
    if ((int)$song->cycle_id !== (int)$cycleVote->id) {
        return response()->json(['message' => 'Nu poți vota în altă rundă.'], 403);
    }

    // block self-vote (unless test mode admin)
    if (!(config('ap.test_mode') && $user->is_admin) && (int)$song->user_id === (int)$user->id) {
        return response()->json(['message' => 'Nu poți vota propria melodie.'], 403);
    }

    // one vote per user per cycle (unless test mode admin)
    $alreadyVoted = (config('ap.test_mode') && $user->is_admin) ? false :
        Vote::where('user_id', $user->id)->where('cycle_id', $cycleVote->id)->exists();

    if ($alreadyVoted) {
        return response()->json(['message' => 'Ai votat deja în această rundă.'], 403);
    }

    Vote::create([
        'user_id'   => $user->id,
        'song_id'   => $song->id,
        'cycle_id'  => $cycleVote->id,
        'vote_date' => $now->toDateString(),
    ]);

    $song->increment('votes');
    return response()->json(['message' => 'Vot înregistrat cu succes.']);
}


    /**
     * Legacy voting method (kept for backward compatibility).
     */
    public function voteForSongLegacy($id)
    {
        // Legacy GET/URL-style voting endpoint -> delegate to unified logic
        $req = request();
        $req->merge(['song_id' => (int) $id]);
        return $this->voteForSong($req);
    }

    /** ---------- Versus fallback (alt route variant) ---------- */

    public function showVersus()
    {
        $now = Carbon::now();
        $tb  = $this->getActiveTiebreakForToday();

        if (!$tb || !$now->between($tb->starts_at, $tb->ends_at)) {
            return redirect()->route('concurs')
                ->with('message', 'Nu există tiebreak activ acum.');
        }

        $songs     = Song::whereIn('id', (array) $tb->song_ids)->get();
        $endsAtIso = optional($tb->ends_at)->toIso8601String();

        return view('concurs_versus', [
            'songs'     => $songs,
            'tiebreak'  => $tb,
            'endsAtIso' => $endsAtIso,
        ]);
    }
    public function uploadPage(Request $request)
    {
        $now = \Carbon\Carbon::now();
    
        // OPEN submission cycle (page renders read-only if none)
        $cycleSubmit = \App\Models\ContestCycle::where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();
    
        $songsSubmit            = collect();
        $submitTheme            = null;
        $submissionsOpen        = (bool) $cycleSubmit;
        $userHasUploadedToday   = false;
        $votingOpensAt          = null;
    
        if ($cycleSubmit) {
            // songs in this submission cycle
            $songsSubmit = \App\Models\Song::where('cycle_id', $cycleSubmit->id)
                ->orderBy('id')->get();
    
            // per-user: already uploaded?
            if (\Auth::check()) {
                $userHasUploadedToday = \App\Models\Song::where('user_id', \Auth::id())
                    ->where('cycle_id', $cycleSubmit->id)
                    ->exists();
            }
    
            // theme + likes (for header pill)
            if ($cycleSubmit->contest_theme_id) {
                $submitTheme = \App\Models\ContestTheme::query()
                    ->withCount('likes')
                    ->with(['likes' => fn($q) => $q->where('user_id', \Auth::id() ?? 0)])
                    ->find($cycleSubmit->contest_theme_id);
            }
    
            // when voting will open for THIS cycle (for hint text)
            $votingOpensAt = $cycleSubmit->vote_start_at ?? $cycleSubmit->submit_end_at;
        }
    
        return view('concurs.upload', compact(
            'cycleSubmit',
            'songsSubmit',
            'submitTheme',
            'submissionsOpen',
            'userHasUploadedToday',
            'votingOpensAt'
        ));
    }
    
public function votePage(Request $request)
{
    $now = \Carbon\Carbon::now();

    // Current OPEN voting cycle (no redirects; page renders read-only if none)
    $cycleVote = \App\Models\ContestCycle::where('vote_start_at', '<=', $now)
        ->where('vote_end_at', '>', $now)
        ->orderByDesc('vote_start_at')
        ->first();

    $songsVote          = collect();
    $voteTheme          = null;
    $votingOpen         = (bool) $cycleVote;
    $userHasVotedToday  = false;

    if ($cycleVote) {
        // Songs in this voting cycle
        $songsVote = \App\Models\Song::where('cycle_id', $cycleVote->id)
            ->orderBy('id')->get();

        // Per-user flag (hide buttons when already voted)
        if (\Auth::check()) {
            $userHasVotedToday = \App\Models\Vote::where('user_id', \Auth::id())
                ->where('cycle_id', $cycleVote->id)
                ->exists();
        }

        // Theme + likes (to keep the same header pills/like counts)
        if ($cycleVote->contest_theme_id) {
            $voteTheme = \App\Models\ContestTheme::query()
                ->withCount('likes')
                ->with(['likes' => fn($q) => $q->where('user_id', \Auth::id() ?? 0)])
                ->find($cycleVote->contest_theme_id);
        }
    }

    return view('concurs.vote', compact(
        'cycleVote',
        'songsVote',
        'voteTheme',
        'votingOpen',
        'userHasVotedToday'
    ));
}



}
