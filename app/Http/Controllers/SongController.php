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
use App\Models\ContestTheme;
use App\Models\ContestCycle;

class SongController extends Controller
{
    /* -----------------------------------------------------------------------
     | Helpers
     |-----------------------------------------------------------------------*/

    private function ytId(string $url): ?string
    {
        $url = trim($url);

        if (preg_match('~youtu\.be/([0-9A-Za-z_-]{11})~', $url, $m)) return $m[1];
        if (preg_match('~(?:v=|/embed/|/v/)([0-9A-Za-z_-]{11})~', $url, $m)) return $m[1];
        if (preg_match('~([0-9A-Za-z_-]{11})~', $url, $m)) return $m[1];

        return null;
    }

    /* -----------------------------------------------------------------------
     | PAGES
     |-----------------------------------------------------------------------*/

    public function showTodaySongs(Request $request)
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        /* ---- Winner strip (last finished round) ---- */
        $winnerStripCycle = ContestCycle::where('vote_end_at', '<=', $now)
            ->orderByDesc('vote_end_at')
            ->first();

        $winnerStripWinner = null;
        if ($winnerStripCycle) {
            $winnerStripWinner = Winner::where('cycle_id', $winnerStripCycle->id)
                ->with(['user:id,name', 'song:id,title,youtube_url'])
                ->first();
        }

        /* ---- Cycles for current moment ---- */
        $cycleSubmit = ContestCycle::where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();

        $cycleVote = ContestCycle::where('vote_start_at', '<=', $now)
            ->where('vote_end_at', '>', $now)
            ->orderByDesc('vote_start_at')
            ->first();

        $gapBetweenPhases = false;
        if (!$cycleSubmit && !$cycleVote) {
            $current = ContestCycle::where('start_at', '<=', $now)
                ->where('vote_end_at', '>', $now)
                ->orderByDesc('start_at')
                ->first();

            if ($current) {
                $cycleSubmit      = $current;
                $gapBetweenPhases = true;
            }
        }

        /* ---- Ensure theme model ---- */
        if ($cycleSubmit && empty($cycleSubmit->contest_theme_id)) {
            $raw  = (string)($cycleSubmit->theme_text ?? '');
            $name = trim($raw);
            if (preg_match('/^\s*([^—-]+)\s*[—-]\s*(.+)$/u', $raw, $m)) {
                $name = trim($m[2] ?? $raw);
            }

            $contestDay = optional($cycleSubmit->submit_end_at)->toDateString() ?? $now->toDateString();
            $ct = ContestTheme::firstOrCreate(
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

        /* ---- Themes with likes ---- */
        $authId      = auth()->id();
        $submitTheme = $cycleSubmit && $cycleSubmit->contest_theme_id
            ? ContestTheme::query()
                ->withCount('likes')
                ->with(['likes' => fn($q) => $q->where('user_id', $authId ?? 0)])
                ->find($cycleSubmit->contest_theme_id)
            : null;

        $voteTheme = $cycleVote && $cycleVote->contest_theme_id
            ? ContestTheme::query()
                ->withCount('likes')
                ->with(['likes' => fn($q) => $q->where('user_id', $authId ?? 0)])
                ->find($cycleVote->contest_theme_id)
            : null;

        /* ---- Song lists ---- */
        $songsSubmit = $cycleSubmit
            ? Song::where('cycle_id', $cycleSubmit->id)->orderBy('id')->get()
            : collect();

        $songsVote = $cycleVote
            ? Song::where('cycle_id', $cycleVote->id)->orderBy('id')->get()
            : collect();

        /* ---- Flags for blades ---- */
        if ($gapBetweenPhases) {
            $submissionsOpen = false;
            $votingOpen      = false;
        } else {
            $submissionsOpen = (bool) $cycleSubmit;
            $votingOpen      = (bool) $cycleVote;
        }

        $votingOpensAt = null;
        if (!$cycleVote && $cycleSubmit) {
            $votingOpensAt = $cycleSubmit->vote_start_at ?? $cycleSubmit->submit_end_at;
            if ($votingOpensAt) $votingOpensAt = $votingOpensAt->copy();
        }

        /* ---- Per-user flags ---- */
        $userHasVotedToday = false;
        if (Auth::check() && $cycleVote) {
            $userHasVotedToday = Vote::where('user_id', Auth::id())
                ->where('cycle_id', $cycleVote->id)
                ->exists();
        }

        $userHasUploadedToday = false;
        if (Auth::check() && $cycleSubmit) {
            $userHasUploadedToday = Song::where('user_id', Auth::id())
                ->where('cycle_id', $cycleSubmit->id)
                ->exists();
        }

        $songs = $votingOpen ? $songsVote : $songsSubmit;

        $theme = null;
        if ($cycleSubmit) {
            $theme = (object)[
                'title'         => $cycleSubmit->theme_text ?? '—',
                'category_code' => 'GEN',
            ];
        }

        /* ---- Winner modal visibility ---- */
        $finished = ContestCycle::where('vote_end_at', '<=', $now)
            ->orderByDesc('vote_end_at')
            ->first();

        $todayWinner     = null;
        $showWinnerModal = false;
        $showWinnerPopup = false;
        $tomorrowTheme   = null;

        if ($finished) {
            $persisted = Winner::where('cycle_id', $finished->id)->first();

            if ($persisted) {
                $todayWinner = Song::with('user:id,name')->find($persisted->song_id);
            } else {
                $row = DB::table('votes as v')
                    ->join('songs as s', 's.id', '=', 'v.song_id')
                    ->where('s.cycle_id', $finished->id)
                    ->selectRaw('s.id as song_id, COUNT(*) as vote_count, MIN(v.created_at) as first_vote_at')
                    ->groupBy('s.id')
                    ->orderByDesc('vote_count')
                    ->orderBy('first_vote_at')
                    ->limit(1)
                    ->first();

                if ($row) {
                    $todayWinner = Song::with('user:id,name')->find($row->song_id);
                }
            }

            $tomorrowMidnight = $now->copy()->addDay()->startOfDay();
            $tomorrowCycle = ContestCycle::where('vote_start_at', $tomorrowMidnight)->first();
            $tomorrowPicked = (bool)($tomorrowCycle && (
                !empty($tomorrowCycle->contest_theme_id) || !empty($tomorrowCycle->theme_text)
            ));

            $showWindow = $now->between(
                $finished->vote_end_at ?? $now->copy()->setTime(20, 0, 0),
                ($finished->vote_end_at ?? $now->copy()->setTime(20, 0, 0))->copy()->addHour()
            );

            $isWinner = auth()->check() && $todayWinner && ((int)auth()->id() === (int)$todayWinner->user_id);
            $showWinnerModal = $todayWinner && $isWinner && $showWindow && !$tomorrowPicked;
            $showWinnerPopup = $showWinnerModal;

            $tomorrowTheme = $tomorrowCycle?->theme_text;
        }

        $dayLocked         = $gapBetweenPhases ? true : false;
        $uploadForTomorrow = false;

        return view('concurs', compact(
            'cycleSubmit','cycleVote',
            'songsSubmit','songsVote',
            'submissionsOpen','votingOpen','votingOpensAt',
            'showWinnerModal','showWinnerPopup','winnerStripCycle','winnerStripWinner',
            'songs','theme','userHasVotedToday','userHasUploadedToday',
            'todayWinner','tomorrowTheme','dayLocked','uploadForTomorrow',
            'submitTheme','voteTheme'
        ));
    }

    public function uploadPage(Request $request)
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);
    
        $cycleSubmit = ContestCycle::where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();
    
        $songsSubmit          = collect();
        $submitTheme          = null;
        $submissionsOpen      = (bool) $cycleSubmit;
        $userHasUploadedToday = false;
        $votingOpensAt        = null;
    
        if (!$cycleSubmit) {
            $cooldownCycle = ContestCycle::where('start_at', '<=', $now)
                ->where('vote_start_at', '>', $now)
                ->orderByDesc('start_at')
                ->first();
    
            if ($cooldownCycle && $cooldownCycle->submit_end_at
                && $now->between($cooldownCycle->submit_end_at, $cooldownCycle->vote_start_at)) {
                $cycleSubmit     = $cooldownCycle;
                $submissionsOpen = false;
            }
        }
    
        $preSubmit          = false;
        $submissionsOpensAt = null;
    
        if (!$cycleSubmit) {
            $nextCycle = ContestCycle::where('start_at', '>', $now)
                ->orderBy('start_at')
                ->first();
    
            if ($nextCycle) {
                $cycleSubmit        = $nextCycle;
                $submissionsOpen    = false;
                $preSubmit          = true;
                $submissionsOpensAt = $nextCycle->start_at;
            }
        }
    
        if ($cycleSubmit) {
            if (!$preSubmit) {
                $songsSubmit = Song::where('cycle_id', $cycleSubmit->id)->orderBy('id')->get();
            }
    
            if (Auth::check() && !$preSubmit) {
                $userHasUploadedToday = Song::where('user_id', Auth::id())
                    ->where('cycle_id', $cycleSubmit->id)
                    ->exists();
            }
    
            if ($cycleSubmit->contest_theme_id) {
                $submitTheme = ContestTheme::query()
                    ->withCount('likes')
                    ->with(['likes' => fn($q) => $q->where('user_id', Auth::id() ?? 0)])
                    ->find($cycleSubmit->contest_theme_id);
            }
    
            $votingOpensAt = $cycleSubmit->vote_start_at ?? $cycleSubmit->submit_end_at;
        }
    
        return view('concurs.upload', compact(
            'cycleSubmit',
            'songsSubmit',
            'submitTheme',
            'submissionsOpen',
            'userHasUploadedToday',
            'votingOpensAt',
            'preSubmit',
            'submissionsOpensAt'
        ));
    }
    
    
    public function votePage(Request $request)
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = now($tz);

        $cycleVote         = null;
        $songsVote         = collect();
        $votingOpen        = false;
        $preVote           = false;
        $voteOpensAt       = null;
        $voteTheme         = null;
        $userHasVotedToday = false;

        $todayStart = $now->copy()->startOfDay();
        $today2000  = $todayStart->copy()->setTime(20, 0, 0);
        $endOfDay   = $now->copy()->endOfDay();

        if ($now->between($today2000, $endOfDay)) {
            $midnightTomorrow = $now->copy()->addDay()->startOfDay();

            $previewCycle = ContestCycle::query()
                ->where('vote_start_at', '=', $midnightTomorrow)
                ->latest('id')
                ->first();

            if (!$previewCycle) {
                $previewCycle = ContestCycle::query()
                    ->whereBetween('submit_end_at', [
                        $today2000->copy()->subMinutes(15),
                        $today2000->copy()->addMinutes(15),
                    ])
                    ->latest('id')
                    ->first();
            }

            if ($previewCycle) {
                $cycleVote   = $previewCycle;
                $songsVote   = $previewCycle->songs()->with(['user:id,name'])->orderBy('id')->get();
                $votingOpen  = false;
                $preVote     = true;
                $voteOpensAt = $previewCycle->vote_start_at;
            }
        }

        if (!$cycleVote) {
            $openCycle = ContestCycle::query()
                ->where('vote_start_at', '<=', $now)
                ->where('vote_end_at',   '>',  $now)
                ->latest('id')
                ->first();

            if ($openCycle) {
                $cycleVote   = $openCycle;
                $songsVote   = $openCycle->songs()->with(['user:id,name'])->orderBy('id')->get();
                $votingOpen  = true;
                $preVote     = false;
                $voteOpensAt = $openCycle->vote_start_at;
            }
        }

        if ($cycleVote && method_exists($cycleVote, 'contestTheme')) {
            $voteTheme = $cycleVote->contestTheme()->withCount('likes')->first();
        }

        if ($cycleVote && auth()->check()) {
            $userHasVotedToday = Vote::query()
                ->where('user_id', auth()->id())
                ->where('cycle_id', $cycleVote->id)
                ->exists();
        }

        return view('concurs.vote', [
            'cycleVote'         => $cycleVote,
            'songsVote'         => $songsVote,
            'votingOpen'        => $votingOpen,
            'preVote'           => $preVote,
            'voteOpensAt'       => $voteOpensAt,
            'voteTheme'         => $voteTheme,
            'userHasVotedToday' => $userHasVotedToday,
        ]);
    }

    public function todayList(Request $request)
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        $cycleSubmit = ContestCycle::where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();

        if (!$cycleSubmit) {
            return view('partials.songs_list', [
                'songs'               => collect(),
                'userHasVotedToday'   => true,
                'showVoteButtons'     => false,
                'hideDisabledButtons' => true,
            ]);
        }

        $songs = Song::where('cycle_id', $cycleSubmit->id)->orderBy('id')->get();

        return view('partials.songs_list', [
            'songs'               => $songs,
            'userHasVotedToday'   => true,
            'showVoteButtons'     => false,
            'hideDisabledButtons' => true,
        ]);
    }

    /* -----------------------------------------------------------------------
     | ACTIONS
     |-----------------------------------------------------------------------*/

    public function uploadSong(Request $request)
    {
        $request->validate([
            'youtube_url' => 'required|url',
        ]);

        $user = Auth::user();
        $tz   = config('app.timezone', 'Europe/Bucharest');
        $now  = Carbon::now($tz);

        $wantsJson = $request->ajax() || $request->wantsJson();

        // ⛔️ removed weekend block

        $cycleSubmit = ContestCycle::where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();

        if (!$cycleSubmit) {
            $msg = 'Înscrierile nu sunt deschise acum.';
            return $wantsJson
                ? response()->json(['message' => $msg], 422)
                : redirect()->back()->with('error', $msg);
        }

        $already = Song::where('user_id', $user->id)
            ->where('cycle_id', $cycleSubmit->id)
            ->exists();
        if ($already) {
            $msg = 'Ai încărcat deja o melodie.';
            return $wantsJson
                ? response()->json(['message' => $msg], 403)
                : redirect()->back()->with('error', $msg);
        }

        $videoId = $this->ytId($request->youtube_url);
        if (!$videoId) {
            $msg = 'Link YouTube invalid.';
            return $wantsJson
                ? response()->json(['message' => $msg], 422)
                : redirect()->back()->with('error', $msg);
        }

        $yearStart = $now->copy()->startOfYear()->toDateString();
        $yearEnd   = $now->copy()->endOfYear()->toDateString();
        $wonThisYear = Winner::query()
            ->whereBetween('contest_date', [$yearStart, $yearEnd])
            ->whereHas('song', function ($q) use ($videoId) {
                $q->where('youtube_id', $videoId);
            })
            ->exists();
        if ($wonThisYear) {
            $msg = 'Această melodie a câștigat deja anul acesta. Te rog alege altă piesă.';
            return $wantsJson
                ? response()->json(['message' => $msg], 409)
                : redirect()->back()->with('error', $msg);
        }

        $dupe = Song::where('cycle_id', $cycleSubmit->id)
            ->get(['youtube_url', 'youtube_id'])
            ->contains(function ($s) use ($videoId) {
                if (!empty($s->youtube_id)) return $s->youtube_id === $videoId;
                return $this->ytId($s->youtube_url) === $videoId;
            });
        if ($dupe) {
            $msg = 'Această melodie este deja înscrisă în această rundă.';
            return $wantsJson
                ? response()->json(['message' => $msg], 409)
                : redirect()->back()->with('error', $msg);
        }

        $title = 'Melodie YouTube';
        try {
            $resp = Http::timeout(6)->get('https://www.youtube.com/oembed', [
                'url'    => $request->youtube_url,
                'format' => 'json',
            ]);
            if ($resp->ok() && isset($resp['title'])) {
                $title = (string)$resp['title'];
            }
        } catch (\Throwable $e) {}

        Song::create([
            'user_id'          => $user->id,
            'youtube_url'      => $request->youtube_url,
            'youtube_id'       => $videoId,
            'title'            => $title,
            'votes'            => 0,
            'competition_date' => $now->toDateString(),
            'theme_id'         => null,
            'cycle_id'         => $cycleSubmit->id,
        ]);

        $ok = 'Melodie încărcată cu succes.';
        return $wantsJson
            ? response()->json(['message' => $ok])
            : redirect()->back()->with('status', $ok);
    }

    public function voteForSong(Request $request)
    {
        $request->validate([
            'song_id'  => ['required','integer'],
            'cycle_id' => ['nullable','integer'],
        ]);

        $user = $request->user();
        $tz   = config('app.timezone', 'Europe/Bucharest');
        $now  = now($tz);

        $song = Song::query()->findOrFail($request->integer('song_id'));

        $cycleId = $request->integer('cycle_id')
            ?: ($song->cycle_id ?? null);

        if (!$cycleId) {
            $cycleId = DB::table('contest_cycles')
                ->where('vote_start_at', '<=', $now)
                ->where('vote_end_at',   '>',  $now)
                ->orderByDesc('id')
                ->value('id');
        }

        if (!$cycleId) {
            return response()->json(['message' => 'Runda de vot nu este deschisă acum.'], 422);
        }

        $cycle = ContestCycle::query()
            ->select(['id','vote_start_at','vote_end_at'])
            ->find($cycleId);

        if (!$cycle) {
            return response()->json(['message' => 'Runda de vot nu a fost găsită.'], 422);
        }

        $open = $cycle->vote_start_at && $cycle->vote_end_at
             && $now->between($cycle->vote_start_at, $cycle->vote_end_at);
        if (!$open) {
            return response()->json(['message' => 'Votul pentru această melodie nu este deschis.'], 422);
        }

        if ((int)$song->user_id === (int)$user->id) {
            return response()->json(['message' => 'Nu poți vota propria melodie.'], 422);
        }

        $already = Vote::query()
            ->where('user_id', $user->id)
            ->where('cycle_id', $cycle->id)
            ->exists();
        if ($already) {
            return response()->json(['message' => 'Ai votat deja în această rundă.'], 422);
        }

        try {
            $voteDate = optional($cycle->vote_start_at)->toDateString()
                     ?: now($tz)->toDateString();

            DB::table('votes')->insert([
                'user_id'    => $user->id,
                'song_id'    => $song->id,
                'cycle_id'   => $cycle->id,
                'vote_date'  => $voteDate,
                'created_at' => now($tz),
                'updated_at' => now($tz),
            ]);
        } catch (\Throwable $e) {
            $msg = strtolower($e->getMessage());
            if (str_contains($msg, 'unique') || str_contains($msg, 'duplicate')) {
                return response()->json(['message' => 'Ai votat deja în această rundă.'], 422);
            }
            throw $e;
        }

        return response()->json(['ok' => true, 'message' => 'Vot înregistrat.']);
    }

    public function voteForSongLegacy($id)
    {
        $req = request();
        $req->merge(['song_id' => (int)$id]);
        return $this->voteForSong($req);
    }
}
