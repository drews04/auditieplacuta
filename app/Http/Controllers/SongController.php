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

// ✅ New theme stack (your classes/columns)
use App\Models\ContestTheme; // columns: contest_date, theme_pool_id, picked_by_winner
use App\Models\ThemePool;    // columns: id, name, category

use App\Services\AwardPoints;

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
        $today = \Carbon\Carbon::today();
        $now   = \Carbon\Carbon::now();
    
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
    
        // 🛡️ Winner banner: ignore any dummy/reset placeholders
        $todayWinner = \App\Models\Winner::whereDate('contest_date', $today)
            ->whereHas('song', function ($q) {
                $q->where('title', '!=', '__AP_DUMMY__');
            })
            ->with(['user:id,name', 'song'])
            ->first();
    
        $userHasVotedToday    = false;
        $userHasUploadedToday = false;
    
        if (\Illuminate\Support\Facades\Auth::check()) {
            $uid = \Illuminate\Support\Facades\Auth::id();
    
            $userHasVotedToday = \App\Models\Vote::where('user_id', $uid)
                ->whereDate('vote_date', $today)
                ->exists();
    
            // 🛡️ Upload guard: do not count a dummy/reset upload
            $userHasUploadedToday = \App\Models\Song::where('user_id', $uid)
                ->whereDate('competition_date', $today)
                ->where('title', '!=', '__AP_DUMMY__')
                ->exists();
        }
    
        $tbResolvedToday = \App\Models\Tiebreak::whereDate('contest_date', $today)
            ->where('resolved', true)
            ->exists();
    
        $popupStart = $tbResolvedToday
            ? $today->copy()->setTime(20, 30)
            : $today->copy()->setTime(20, 0);
    
        $withinPopupWindow = $now->between(
            $popupStart,
            $today->copy()->setTime(21, 0)->subSecond()
        );
    
        $isWinnerUser    = \Illuminate\Support\Facades\Auth::check()
                            && $todayWinner
                            && $todayWinner->user_id === \Illuminate\Support\Facades\Auth::id();
        $showWinnerPopup = $isWeekday && $withinPopupWindow && $isWinnerUser;
    
        // 🎵 Songs list = TODAY (hide any dummy/reset rows)
        $songs = \App\Models\Song::whereDate('competition_date', $today)
            ->where('title', '!=', '__AP_DUMMY__')
            ->orderBy('created_at')
            ->get();
    
        // === Compute next contest day (skip weekend) ===
        $nextContest = $today->copy();
        do { $nextContest->addDay(); } while (in_array($nextContest->dayOfWeekIso, [6, 7]));
    
        // === TODAY'S THEME (compat with Blade: needs $theme truthy) ===
        $ctToday = \App\Models\ContestTheme::whereDate('contest_date', $today->toDateString())
            ->with('pool:id,name,category')
            ->first();
    
        $theme = null; // old Blade expects this
        if ($ctToday) {
            $theme = (object) [
                'title'         => $ctToday->pool->name ?? '—',
                'category_code' => $ctToday->pool->category ?? 'GEN',
                '_raw'          => $ctToday,
            ];
        }
    
        // === TOMORROW'S THEME (shown as "Tema pentru mâine") ===
        $ctTomorrow = \App\Models\ContestTheme::whereDate('contest_date', $nextContest->toDateString())
            ->with('pool:id,name,category')
            ->first();
    
        $tomorrowTheme = null;
        if ($ctTomorrow) {
            $chooserName = 'sistem';
            if ($ctTomorrow->picked_by_winner && $todayWinner && $todayWinner->relationLoaded('user')) {
                $chooserName = $todayWinner->user->name ?? 'câștigătorul zilei';
            }
            $tomorrowTheme = (object) [
                'title'         => $ctTomorrow->pool->name ?? '—',
                'category_code' => $ctTomorrow->pool->category ?? 'GEN',
                'chooser'       => (object) ['name' => $chooserName],
                '_raw'          => $ctTomorrow,
            ];
        }
    
        // === End-of-day lock + early submissions for tomorrow (FOR EVERYONE once tomorrow's theme exists)
        $dayLocked = false;
        $uploadForTomorrow = false;
    
        if ($tomorrowTheme) {
            // hide today's list entirely
            $songs = collect();
    
            // keep uploads open (they will target tomorrow)
            $submissionsOpen   = true;
            $userHasVotedToday = true;   // hides any vote UI
            $dayLocked         = true;   // banner state
            $uploadForTomorrow = true;   // “goes to tomorrow” hint
        }
    
        return view('concurs', compact(
            'songs',
            'theme',              // ✅ now set when today's ContestTheme exists
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

    $user  = Auth::user();
    $today = \Carbon\Carbon::today();
    $now   = \Carbon\Carbon::now();

    // Weekdays only
    if ($now->isWeekend()) {
        return response()->json(['message' => 'Nu se ține concurs în weekend.'], 422);
    }

    // Figure out next contest day (skip weekend)
    $nextContest = $this->nextWeekdayContestDate($today);

    // Read official themes from ContestTheme
    $ctTodayId = \App\Models\ContestTheme::whereDate('contest_date', $today->toDateString())->value('id');
    $ctNext    = \App\Models\ContestTheme::whereDate('contest_date', $nextContest->toDateString())->first();

    // Decide TARGET day for the new submission:
    // - If a theme for tomorrow already exists (winner picked or fallback), we IMMEDIATELY switch to "upload for tomorrow" mode.
    // - Otherwise, before 19:30 we accept uploads for TODAY; after 19:30 we close (classic behavior).
    $targetDate     = null;
    $targetThemeId  = null;

    if ($ctNext) {
        // Winner chose theme (or fallback) → start collecting for tomorrow NOW
        $targetDate    = $nextContest;
        $targetThemeId = $ctNext->id;
    } else {
        // Normal daytime flow (today only, and only until 19:30)
        if ($now->gt($today->copy()->setTime(19, 30))) {
            return response()->json(['message' => 'Înscrierile pentru azi s-au închis (după 19:30).'], 422);
        }
        if (!$ctTodayId) {
            return response()->json(['message' => 'Tema pentru azi nu este setată încă.'], 422);
        }
        $targetDate    = $today;
        $targetThemeId = $ctTodayId;
    }

    // One submission per user per TARGET day (ignore reset dummy songs)
$alreadyUploaded = \App\Models\Song::where('user_id', $user->id)
->whereDate('competition_date', $targetDate)
->where('title', '!=', '__AP_DUMMY__')
->exists();

if ($alreadyUploaded) {
return response()->json(['message' => 'Ai înscris deja o melodie pentru această zi.'], 403);
}

    // Normalize URL -> videoId
    $videoId = $this->ytId($request->youtube_url);
    if (!$videoId) {
        return response()->json(['message' => 'Link YouTube invalid.'], 422);
    }

    // Block duplicates for the TARGET day
    $dupeExists = \App\Models\Song::whereDate('competition_date', $targetDate)
        ->get(['youtube_url'])
        ->contains(function ($s) use ($videoId) {
            return $this->ytId($s->youtube_url) === $videoId;
        });
    if ($dupeExists) {
        return response()->json(['message' => 'Această melodie este deja înscrisă pentru acea zi.'], 409);
    }

    // Fetch title via oEmbed (best effort)
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
        // keep fallback title
    }

    // Create submission for TARGET day (today OR tomorrow)
    \App\Models\Song::create([
        'user_id'          => $user->id,
        'youtube_url'      => $request->youtube_url,
        'competition_date' => $targetDate->toDateString(),
        'title'            => $title,
        'votes'            => 0,
        'theme_id'         => $targetThemeId, // references ContestTheme.id
    ]);

    $msg = $ctNext
        ? 'Melodie încărcată cu succes. Intră în concursul de mâine.'
        : 'Melodie încărcată cu succes.';
    return response()->json(['message' => $msg]);
}


    /**
     * Return partial list of today's songs (for AJAX refresh).
     */
    public function todayList()
{
    $today = \Carbon\Carbon::today();

    // compute next contest day (skip weekend)
    $next = $today->copy();
    do { $next->addDay(); } while (in_array($next->dayOfWeekIso, [6, 7]));

    // If a theme exists for tomorrow, we are in "tomorrow mode":
    //  - list TOMORROW's songs
    //  - force-hide vote UI (we don't vote now)
    $tomorrowTheme = \App\Models\ContestTheme::whereDate('contest_date', $next->toDateString())->first();

    if ($tomorrowTheme) {
        $songs = \App\Models\Song::whereDate('competition_date', $next)
            ->orderBy('created_at')
            ->get();

        $userHasVotedToday = true;   // 🔒 hide "Votează" buttons in tomorrow-mode
    } else {
        // normal day → list TODAY and use real voted flag
        $songs = \App\Models\Song::whereDate('competition_date', $today)
            ->orderBy('created_at')
            ->get();

        $userHasVotedToday = $this->userHasVotedToday(\Auth::id());
    }

    return view('partials.songs_list', compact('songs', 'userHasVotedToday'));
}


    /**
     * Voting with daytime / tiebreak rules.
     */
    public function voteForSong($id)
    {
        $user  = Auth::user();
        $today = Carbon::today();
        $now   = Carbon::now();

        if ($now->isWeekend()) {
            return response()->json(['message' => 'Nu se votează în weekend.'], 422);
        }

        $tb = $this->getActiveTiebreakForToday();
        $activeTiebreak = $tb && $now->between($tb->starts_at, $tb->ends_at);

        if (!$activeTiebreak && $now->greaterThanOrEqualTo($today->copy()->setTime(20, 30))) {
            return response()->json(['message' => 'Votarea pentru azi s-a închis.'], 422);
        }

        if (Winner::whereDate('contest_date', $today)->exists()) {
            return response()->json(['message' => 'Votarea pentru azi este închisă.'], 422);
        }

        $song = Song::findOrFail($id);
        if (!Carbon::parse($song->competition_date)->isSameDay($today)) {
            return response()->json(['message' => 'Poți vota doar melodiile concursului de azi.'], 422);
        }

        if ($song->user_id === $user->id) {
            return response()->json(['message' => 'Nu poți vota propria melodie.'], 403);
        }

        if ($activeTiebreak) {
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

            Vote::create([
                'user_id'     => $user->id,
                'song_id'     => $song->id,
                'vote_date'   => $today,
                'tiebreak_id' => $tb->id,
            ]);

            $song->increment('votes');

            return response()->json(['message' => 'Vot înregistrat pentru Versus.']);
        }

        if ($now->greaterThanOrEqualTo($today->copy()->setTime(20, 0))) {
            return response()->json(['message' => 'Votarea pentru azi s-a închis la 20:00.'], 422);
        }

        $alreadyVoted = Vote::where('user_id', $user->id)
            ->whereDate('vote_date', $today)
            ->where('created_at', '<', $today->copy()->setTime(20, 0))
            ->exists();

        if ($alreadyVoted) {
            return response()->json(['message' => 'Ai votat deja azi.'], 403);
        }

        Vote::create([
            'user_id'   => $user->id,
            'song_id'   => $song->id,
            'vote_date' => $today,
        ]);

        $song->increment('votes');

        return response()->json(['message' => 'Vot înregistrat cu succes.']);
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
}
