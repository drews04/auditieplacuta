<?php

namespace App\Http\Controllers\Concurs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

/**
 * Handles song uploads for the Concurs
 * REBUILT PER COMPENDIUM V2 (2025-10-20)
 */
class UploadController extends Controller
{
    /**
     * Show upload page
     */
    public function page(Request $request)
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        // Query SUBMISSION lane
        $cycleSubmit = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();

        // BULLETPROOF FREEZE CHECK: submissions open if theme is NOT null
        $submissionsOpen = (bool)$cycleSubmit && !is_null($cycleSubmit->theme_id);

        $songsSubmit          = collect();
        $submitTheme          = null;
        $userHasUploadedToday = false;
        $votingOpensAt        = null;

        if ($cycleSubmit) {
            $songsSubmit = DB::table('songs')
                ->where('cycle_id', $cycleSubmit->id)
                ->join('users', 'users.id', '=', 'songs.user_id')
                ->select('songs.*', 'users.name as user_name')
                ->orderBy('songs.id')
                ->get();

            if (auth()->check()) {
                $userHasUploadedToday = DB::table('songs')
                    ->where('user_id', auth()->id())
                    ->where('cycle_id', $cycleSubmit->id)
                    ->exists();
            }

            if (!empty($cycleSubmit->theme_text)) {
                $themeId = $cycleSubmit->theme_id ?? null;
                
                // Query real likes count for this theme
                $likesCount = $themeId ? DB::table('theme_likes')
                    ->where('theme_id', $themeId)
                    ->count() : 0;
                
                // Check if current user has liked this theme
                $likedByMe = false;
                if (auth()->check() && $themeId) {
                    $likedByMe = DB::table('theme_likes')
                        ->where('user_id', auth()->id())
                        ->where('theme_id', $themeId)
                        ->exists();
                }
                
                $submitTheme = (object)[
                    'id'          => $themeId,
                    'name'        => $cycleSubmit->theme_text,
                    'likes_count' => $likesCount,
                    'liked_by_me' => $likedByMe,
                ];
            }

            $votingOpensAt = Carbon::parse($cycleSubmit->submit_end_at);
        }

        return view('concurs.upload', compact(
            'cycleSubmit', 'songsSubmit', 'submitTheme', 'submissionsOpen',
            'userHasUploadedToday', 'votingOpensAt'
        ));
    }

    /**
     * Store uploaded song
     * 
     * VALIDATIONS:
     * - Submissions must be open
     * - 1 upload per user per cycle
     * - No duplicate YouTube links in same cycle
     * - No banned songs (past winners)
     */
    public function store(Request $request)
    {
        $request->validate(['youtube_url' => 'required|url']);

        $user = auth()->user();
        $tz   = config('app.timezone', 'Europe/Bucharest');
        $now  = Carbon::now($tz);

        $wantsJson = $request->ajax() || $request->wantsJson();

        // 1) CHECK READ-ONLY WINDOW
        // BULLETPROOF FREEZE CHECK: Block uploads if submission is frozen
        $submissionCycle = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->first();
        
        if (!$submissionCycle || is_null($submissionCycle->theme_id)) {
            $msg = 'Înscrierile sunt blocate temporar (așteptăm tema nouă).';
            return $wantsJson ? response()->json(['message' => $msg], 422)
                              : back()->with('error', $msg);
        }

        // 2) CHECK IF SUBMISSIONS ARE OPEN
        $cycleSubmit = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();

        if (!$cycleSubmit) {
            $msg = 'Înscrierile nu sunt deschise acum.';
            return $wantsJson ? response()->json(['message' => $msg], 422)
                              : back()->with('error', $msg);
        }

        // 3) CHECK IF USER ALREADY UPLOADED
        $already = DB::table('songs')
            ->where('user_id', $user->id)
            ->where('cycle_id', $cycleSubmit->id)
            ->exists();
        
        if ($already) {
            $msg = 'Ai încărcat deja o melodie în această rundă.';
            return $wantsJson ? response()->json(['message' => $msg], 403)
                              : back()->with('error', $msg);
        }

        // 4) PARSE YOUTUBE ID
        $url = trim($request->youtube_url);
        $videoId = $this->extractYoutubeId($url);

        if (!$videoId) {
            $msg = 'Link YouTube invalid.';
            return $wantsJson ? response()->json(['message' => $msg], 422)
                              : back()->with('error', $msg);
        }

        // 5) CHECK BANNED SONGS (past winners)
        $banned = DB::table('banned_songs')
            ->where('youtube_id', $videoId)
            ->exists();

        if ($banned) {
            $msg = 'Această melodie a câștigat deja și nu poate fi re-încărcată.';
            return $wantsJson ? response()->json(['message' => $msg], 409)
                              : back()->with('error', $msg);
        }

        // 6) CHECK FOR DUPLICATE IN SAME CYCLE
        $dupe = DB::table('songs')
            ->where('cycle_id', $cycleSubmit->id)
            ->where(function($q) use ($videoId, $url) {
                $q->where('youtube_id', $videoId)->orWhere('youtube_url', $url);
            })
            ->exists();

        if ($dupe) {
            $msg = 'Această melodie este deja înscrisă în această rundă.';
            return $wantsJson ? response()->json(['message' => $msg], 409)
                              : back()->with('error', $msg);
        }

        // 7) GET TITLE FROM YOUTUBE OEMBED
        $title = 'Melodie YouTube';
        try {
            $resp = Http::timeout(6)->get('https://www.youtube.com/oembed', [
                'url'    => $url,
                'format' => 'json'
            ]);
            if ($resp->ok() && isset($resp['title'])) {
                $title = (string)$resp['title'];
            }
        } catch (\Throwable $e) {
            // Fallback to default title
        }

        // 8) INSERT SONG
        DB::table('songs')->insert([
            'user_id'     => $user->id,
            'cycle_id'    => $cycleSubmit->id,
            'youtube_id'  => $videoId,
            'youtube_url' => $url,
            'title'       => $title,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $ok = '✅ Melodie încărcată cu succes!';
        return $wantsJson ? response()->json(['message' => $ok])
                          : back()->with('status', $ok);
    }

    /**
     * Extract YouTube video ID from URL
     */
    private function extractYoutubeId(string $url): ?string
    {
        $url = trim($url);

        if (preg_match('~youtu\.be/([0-9A-Za-z_-]{11})~', $url, $m)) return $m[1];
        if (preg_match('~(?:v=|/embed/|/v/)([0-9A-Za-z_-]{11})~', $url, $m)) return $m[1];
        if (preg_match('~([0-9A-Za-z_-]{11})~', $url, $m)) return $m[1];

        return null;
    }
}
