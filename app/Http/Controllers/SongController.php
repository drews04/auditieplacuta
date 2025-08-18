<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Song;
use App\Models\Theme;
use App\Models\Vote;
use App\Models\Winner;
use App\Models\CompetitionTheme;
use App\Models\Tiebreak;
use App\Services\AwardPoints;

class SongController extends Controller
{
    /** ---------- helpers ---------- */

    // Extract a canonical 11-char YouTube video ID from any common URL format
    private function ytId(string $url): ?string
    {
        $url = trim($url);

        // youtu.be/<id>
        if (preg_match('~youtu\.be/([0-9A-Za-z_-]{11})~', $url, $m)) {
            return $m[1];
        }

        // youtube.com/watch?v=<id> or /embed/<id> or /v/<id>
        if (preg_match('~(?:v=|/embed/|/v/)([0-9A-Za-z_-]{11})~', $url, $m)) {
            return $m[1];
        }

        // last‑chance generic 11‑char grab (keeps us robust when query is messy)
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
     * Count >= 2 means a tie at the top.
     */
    private function todayTieSongs()
    {
        $today = Carbon::today();

        $max = Song::whereDate('competition_date', $today)->max('votes');
        if ($max === null) {
            return collect();
        }

        return Song::whereDate('competition_date', $today)
            ->where('votes', $max)
            ->orderBy('created_at')
            ->get(['id', 'title', 'user_id', 'youtube_url', 'votes', 'theme_id']);
    }

    /** Tiebreak helpers **/
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

        // Only open at/after 20:00 and only on weekdays
        if ($now->isWeekend() || $now->lt($today->copy()->setTime(20, 0))) {
            return null;
        }

        // Already opened?
        if ($tb = $this->getActiveTiebreakForToday()) {
            return $tb;
        }

        // Need at least 2 leaders tied at max
        $tieSongs = $this->todayTieSongs();
        if ($tieSongs->count() < 2) {
            return null;
        }

        // Create a 30‑min window [20:00, 20:30)
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

    /**
     * Resolve a finished tiebreak (called when the window is over).
     */
    private function resolveTiebreakIfEnded(): ?\App\Models\Winner
    {
        $tb = $this->getActiveTiebreakForToday();
        if (!$tb) return null;
    
        $now = \Carbon\Carbon::now();
        if ($now->lt($tb->ends_at)) return null; // window not over yet
    
        // Count votes during the tiebreak window for ONLY the tied songs
        $counts = \App\Models\Vote::select('song_id', \DB::raw('COUNT(*) as total'))
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
                $lastVote = \App\Models\Vote::whereIn('song_id', $leaders)
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
            $lastOfDay = \App\Models\Vote::whereIn('song_id', $tb->song_ids)
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
    
        $song = \App\Models\Song::find($winnerSongId);
        if (!$song) {
            $tb->resolved = true;
            $tb->save();
            return null;
        }
    
        $today = \Carbon\Carbon::today()->toDateString();
    
        $winner = \App\Models\Winner::create([
            'contest_date'          => $today,
            'user_id'               => $song->user_id,
            'song_id'               => $song->id,
            'vote_count'            => (int) \App\Models\Vote::where('song_id', $song->id)
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
    
        // 👉 NEW: write points for today (idempotent)
        app(\App\Services\AwardPoints::class)->awardForDate($today);
    
        return $winner;
    }
    

    /**
     * NORMAL-DAY winner creation at/after 20:00 (only if no tie & no winner yet).
     */
    private function finalizeDailyWinnerIfNeeded(): ?Winner
    {
        $today = \Carbon\Carbon::today();
    
        // Weekdays only
        if (\Carbon\Carbon::now()->isWeekend()) return null;
    
        // Only at/after 20:00
        if (\Carbon\Carbon::now()->lt($today->copy()->setTime(20, 0))) return null;
    
        // If already have a winner, stop
        if (\App\Models\Winner::whereDate('contest_date', $today)->exists()) return null;
    
        // If a tiebreak is active (or just opened), let that flow handle it
        if ($this->getActiveTiebreakForToday()) return null;
    
        // Find the highest vote count today
        $maxVotes = \App\Models\Song::whereDate('competition_date', $today)->max('votes');
        if ($maxVotes === null) return null; // no songs today
    
        // Are there multiple leaders? then a tie exists (handled elsewhere)
        $leaders = \App\Models\Song::whereDate('competition_date', $today)
            ->where('votes', $maxVotes)
            ->orderBy('created_at')
            ->get(['id','user_id','theme_id']);
    
        if ($leaders->count() !== 1) return null; // tie (or weird state)
    
        $song = $leaders->first();
    
        // Persist normal-day winner
        $winner = \App\Models\Winner::create([
            'contest_date'          => $today->toDateString(),
            'user_id'               => $song->user_id,
            'song_id'               => $song->id,
            'vote_count'            => (int) \App\Models\Vote::where('song_id', $song->id)
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
    
        // 👉 NEW: write points for today (idempotent via unique index)
        app(\App\Services\AwardPoints::class)->awardForDate($today->toDateString());
    
        return $winner;
    }
    

    /** ---------- pages ---------- */

    /**
     * Show Concurs page for today.
     * - Before 20:00: normal flow.
     * - At 20:00 if tie → open tiebreak and redirect to Versus during [20:00,20:30).
     * - At/after 20:30 → resolve tiebreak (if any), then show page; winner popup starts 20:30 on tie days.
     */
    public function showTodaySongs(Request $request)
    {
        $today = Carbon::today();
        $now   = Carbon::now();
    
        // === TIEBREAK FLOW (open/resolve, then redirect if active) ===
        if (!$now->isWeekend() && $now->greaterThanOrEqualTo($today->copy()->setTime(20, 0))) {
            $tb = $this->openTiebreakIfNeeded();
            if ($tb && $now->greaterThanOrEqualTo($tb->ends_at) && !$tb->resolved) {
                $this->resolveTiebreakIfEnded();
            }
            $tb = $this->getActiveTiebreakForToday();
            if ($tb && $now->between($tb->starts_at, $tb->ends_at)) {
                return redirect()->route('concurs.versus');
            }
        }
        // === END TIEBREAK FLOW ===
    
        $this->finalizeDailyWinnerIfNeeded();
    
        $isWeekday = !$now->isWeekend();
    
        $submissionsOpen = $isWeekday && $now->between(
            $today->copy()->startOfDay(),
            $today->copy()->setTime(19, 30)
        );
    
        $theme        = Theme::whereDate('competition_date', $today)->first();
        $todayWinner  = Winner::whereDate('contest_date', $today)->first();
    
        $userHasVotedToday    = false;
        $userHasUploadedToday = false;
    
        if (Auth::check()) {
            $uid = Auth::id();
    
            $userHasVotedToday = Vote::where('user_id', $uid)
                ->whereDate('vote_date', $today)
                ->exists();
    
            $userHasUploadedToday = Song::where('user_id', $uid)
                ->whereDate('competition_date', $today)
                ->exists();
        }
    
        $tbResolvedToday = Tiebreak::whereDate('contest_date', $today)
            ->where('resolved', true)
            ->exists();
    
        $popupStart = $tbResolvedToday
            ? $today->copy()->setTime(20, 30)
            : $today->copy()->setTime(20, 0);
    
        $withinPopupWindow = $now->between(
            $popupStart,
            $today->copy()->setTime(21, 0)->subSecond()
        );
    
        $isWinnerUser    = Auth::check() && $todayWinner && $todayWinner->user_id === Auth::id();
        $showWinnerPopup = $isWeekday && $withinPopupWindow && $isWinnerUser;
    
        $songs = Song::whereDate('competition_date', $today)
            ->orderBy('created_at')
            ->get();
    
        // Compute next contest day (skip weekend)
        $tomorrow = $today->copy();
        do {
            $tomorrow->addDay();
        } while (in_array($tomorrow->dayOfWeekIso, [6, 7])); // 6=Sat, 7=Sun
    
        $tomorrowTheme = CompetitionTheme::with('chooser:id,name')
            ->whereDate('applies_on', $tomorrow->toDateString())
            ->first();
    
        // === End-of-day lock + early submissions for tomorrow ===
        $dayLocked = false;
        $uploadForTomorrow = false;
        if ($todayWinner && $tomorrowTheme) {
            // Hide today's list and reopen submissions for tomorrow
            $songs = collect();
            $submissionsOpen = true;     // keep the button visible
            $userHasVotedToday = true;   // hide vote UI tied to this flag
            $dayLocked = true;
            $uploadForTomorrow = true;   // tells Blade to show hint text if you want
        }
    
        return view('concurs', compact(
            'songs',
            'theme',
            'userHasVotedToday',
            'userHasUploadedToday',
            'isWeekday',
            'submissionsOpen',
            'todayWinner',
            'showWinnerPopup',
            'tomorrowTheme',
            'dayLocked',
            'uploadForTomorrow'
        ));
    }
    

    /**
     * Versus page — renders only the tied leaders from the active tiebreak window
     * (or recomputes if someone hits it without query params).
     * View: resources/views/concurs_versus.blade.php
     */
    public function versus(Request $request)
    {
        $today = Carbon::today();
        $now   = Carbon::now();

        $tb = $this->getActiveTiebreakForToday();

        // If no active tiebreak or we're out of window, resolve if needed and go back
        if (!$tb || !$now->between($tb->starts_at, $tb->ends_at)) {
            if ($tb && $now->greaterThanOrEqualTo($tb->ends_at) && !$tb->resolved) {
                $this->resolveTiebreakIfEnded();
            }
            return redirect()->route('concurs');
        }

        $songs     = Song::whereIn('id', (array) $tb->song_ids)->with('user:id,name')->get();
        $endsAtIso = $tb->ends_at?->toIso8601String(); // send to Blade for countdown

        return view('concurs_versus', compact('songs', 'endsAtIso'));
    }

    /** ---------- actions ---------- */

    /**
     * Upload a YouTube song (1 per user/day). Also blocks duplicate video IDs for today.
     */
    public function uploadSong(Request $request)
    {
        $request->validate([
            'youtube_url' => 'required|url',
        ]);
    
        $user  = Auth::user();
        $today = \Carbon\Carbon::today();
        $now   = \Carbon\Carbon::now();
    
        // Weekdays only
        if ($now->isWeekend()) {
            return response()->json(['message' => 'Nu se ține concurs în weekend.'], 422);
        }
    
        // Submissions close at 19:30
        if ($now->gt($today->copy()->setTime(19, 30))) {
            return response()->json(['message' => 'Înscrierile pentru azi s-au închis (după 19:30).'], 422);
        }
    
        // Today must have a theme
        $todayThemeId = \App\Models\CompetitionTheme::whereDate('applies_on', $today->toDateString())->value('id');
        if (!$todayThemeId) {
            return response()->json(['message' => 'Tema pentru azi nu este setată încă.'], 422);
        }
    
        // One submission per user per DAY (today)
        $alreadyUploaded = \App\Models\Song::where('user_id', $user->id)
            ->whereDate('competition_date', $today)
            ->exists();
    
        if ($alreadyUploaded) {
            return response()->json(['message' => 'Ai înscris deja o melodie pentru această zi.'], 403);
        }
    
        // Normalize URL -> videoId
        $videoId = $this->ytId($request->youtube_url);
        if (!$videoId) {
            return response()->json(['message' => 'Link YouTube invalid.'], 422);
        }
    
        // Block duplicates for TODAY (same video used by anyone)
        $dupeExists = \App\Models\Song::whereDate('competition_date', $today)
            ->get(['youtube_url'])
            ->contains(function ($s) use ($videoId) {
                return $this->ytId($s->youtube_url) === $videoId;
            });
    
        if ($dupeExists) {
            return response()->json(['message' => 'Această melodie este deja înscrisă azi.'], 409);
        }
    
        // Fetch title via oEmbed (best-effort)
        $title = 'Melodie YouTube';
        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(6)->get('https://www.youtube.com/oembed', [
                'url'    => $request->youtube_url,
                'format' => 'json',
            ]);
            if ($resp->ok() && isset($resp['title'])) {
                $title = (string) $resp['title'];
            }
        } catch (\Throwable $e) {
            // keep fallback
        }
    
        // Create today's submission
        \App\Models\Song::create([
            'user_id'          => $user->id,
            'youtube_url'      => $request->youtube_url,
            'competition_date' => $today->toDateString(),
            'title'            => $title,
            'votes'            => 0,
            'theme_id'         => $todayThemeId,
        ]);
    
        return response()->json(['message' => 'Melodie încărcată cu succes.']);
    }
    


    /**
     * Return partial list of today's songs.
     * (Now also passes $userHasVotedToday so the partial never lacks it.)
     */
    public function todayList()
    {
        $today = Carbon::today();

        $songs = Song::whereDate('competition_date', $today)
            ->orderBy('created_at')
            ->get();

        $userHasVotedToday = $this->userHasVotedToday(Auth::id());

        return view('partials.songs_list', compact('songs', 'userHasVotedToday'));
    }

    /**
     * Handle voting.
     * Rules:
     * - Before 20:00 on weekdays: normal voting (1 vote/day, cannot vote own song).
     * - During an ACTIVE tiebreak window [20:00,20:30): only tied songs can be voted, and it's 1 vote/user/tiebreak
     *   (allowed even if the user has already voted earlier in the day).
     * - After 20:30: no voting.
     */
    public function voteForSong($id)
    {
        $user  = Auth::user();
        $today = Carbon::today();
        $now   = Carbon::now();

        // Weekdays only (Mon–Fri)
        if ($now->isWeekend()) {
            return response()->json(['message' => 'Nu se votează în weekend.'], 422);
        }

        // Check for an active tiebreak window
        $tb = $this->getActiveTiebreakForToday();
        $activeTiebreak = $tb && $now->between($tb->starts_at, $tb->ends_at);

        // Hard cut-offs
        if (!$activeTiebreak && $now->greaterThanOrEqualTo($today->copy()->setTime(20, 30))) {
            return response()->json(['message' => 'Votarea pentru azi s-a închis.'], 422);
        }

        // If a winner already exists for today, close voting
        if (Winner::whereDate('contest_date', $today)->exists()) {
            return response()->json(['message' => 'Votarea pentru azi este închisă.'], 422);
        }

        // Song must be from today’s contest
        $song = Song::findOrFail($id);
        if (!Carbon::parse($song->competition_date)->isSameDay($today)) {
            return response()->json(['message' => 'Poți vota doar melodiile concursului de azi.'], 422);
        }

        // Cannot vote your own song (always)
        if ($song->user_id === $user->id) {
            return response()->json(['message' => 'Nu poți vota propria melodie.'], 403);
        }

        if ($activeTiebreak) {
            // Versus vote: only among tied songs; allow 1 vote per user within the tiebreak window
            if (!in_array($song->id, (array) $tb->song_ids, true)) {
                return response()->json(['message' => 'În Versus poți vota doar melodiile aflate la egalitate.'], 422);
            }

            $alreadyVotedThisTiebreak = Vote::where('user_id', $user->id)
                ->where('vote_date', $today)
                ->where('tiebreak_id', $tb->id)
                ->exists();

            if ($alreadyVotedThisTiebreak) {
                return response()->json(['message' => 'Ai votat deja în tiebreak.'], 403);
            }

            // Record Versus vote (separate from the daytime "1 vote/day" rule)
            Vote::create([
                'user_id'     => $user->id,
                'song_id'     => $song->id,
                'vote_date'   => $today,
                'tiebreak_id' => $tb->id,   // <-- crucial
            ]);

            // Optional: reflect in cached counter for UI
            $song->increment('votes');

            return response()->json(['message' => 'Vot înregistrat pentru Versus.']);
        }

        // ===== Normal daytime voting (before 20:00) =====
        if ($now->greaterThanOrEqualTo($today->copy()->setTime(20, 0))) {
            return response()->json(['message' => 'Votarea pentru azi s-a închis la 20:00.'], 422);
        }

        // One vote per user per day
        $alreadyVoted = Vote::where('user_id', $user->id)
            ->whereDate('vote_date', $today)
            ->where('created_at', '<', $today->copy()->setTime(20, 0)) // ensure we only check daytime vote
            ->exists();

        if ($alreadyVoted) {
            return response()->json(['message' => 'Ai votat deja azi.'], 403);
        }

        // Record normal vote
        Vote::create([
            'user_id'   => $user->id,
            'song_id'   => $song->id,
            'vote_date' => $today,
        ]);

        // Update cached counter on songs table
        $song->increment('votes');

        return response()->json(['message' => 'Vot înregistrat cu succes.']);
    }

    public function showVersus()
    {
        $now   = Carbon::now();

        $tb = $this->getActiveTiebreakForToday();

        if (!$tb || !$now->between($tb->starts_at, $tb->ends_at)) {
            return redirect()->route('concurs')
                ->with('message', 'Nu există tiebreak activ acum.');
        }

        $songs     = Song::whereIn('id', (array) $tb->song_ids)->get();
        $endsAtIso = optional($tb->ends_at)->toIso8601String();

        return view('concurs_versus', [
            'songs'     => $songs,
            'tiebreak'  => $tb,
            'endsAtIso' => $endsAtIso, // used by countdown
        ]);
    }
}
