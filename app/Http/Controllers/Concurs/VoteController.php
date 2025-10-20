<?php

namespace App\Http\Controllers\Concurs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Handles voting for songs in the Concurs
 * REBUILT PER COMPENDIUM V2 (2025-10-20)
 */
class VoteController extends Controller
{
    /**
     * Show vote page
     */
    public function page(Request $request)
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        // Query VOTING lane
        $cycleVote = DB::table('contest_cycles')
            ->where('lane', 'voting')
            ->where('status', 'open')
            ->where('vote_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();

        // Check read-only window
        $window = DB::table('contest_flags')->where('name', 'window')->value('value');
        $votingOpen = (bool)$cycleVote && ($window !== 'waiting_theme');

        $songsVote         = collect();
        $voteTheme         = null;
        $userHasVotedToday = false;
        $voteOpensAt       = null;

        if ($cycleVote) {
            $songsVote = DB::table('songs')
                ->where('cycle_id', $cycleVote->id)
                ->join('users', 'users.id', '=', 'songs.user_id')
                ->select('songs.*', 'users.name as user_name')
                ->orderBy('songs.id')
                ->get();

            $voteOpensAt = Carbon::parse($cycleVote->start_at ?? $cycleVote->vote_end_at);

            if (!empty($cycleVote->theme_text)) {
                $themeId = $cycleVote->theme_id ?? null;
                
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
                
                $voteTheme = (object)[
                    'id'          => $themeId,
                    'name'        => $cycleVote->theme_text,
                    'likes_count' => $likesCount,
                    'liked_by_me' => $likedByMe,
                ];
            }

            if (auth()->check()) {
                $userHasVotedToday = DB::table('votes')
                    ->where('user_id', auth()->id())
                    ->where('cycle_id', $cycleVote->id)
                    ->exists();
            }
        }

        return view('concurs.vote', compact(
            'cycleVote', 'songsVote', 'votingOpen',
            'voteOpensAt', 'voteTheme', 'userHasVotedToday', 'window'
        ));
    }

    /**
     * Store vote
     * 
     * VALIDATIONS:
     * - Voting must be open
     * - 1 vote per user per cycle
     * - Cannot vote own song
     * - Song must belong to current voting cycle
     */
    public function store(Request $request)
    {
        $request->validate([
            'song_id'  => ['required', 'integer'],
            'cycle_id' => ['nullable', 'integer'],
        ]);

        $user = $request->user();
        $tz   = config('app.timezone', 'Europe/Bucharest');
        $now  = Carbon::now($tz);

        // 1) CHECK READ-ONLY WINDOW
        $window = DB::table('contest_flags')->where('name', 'window')->value('value');
        if ($window === 'waiting_theme') {
            return response()->json(['message' => 'Votul este blocat temporar (așteptăm tema nouă).'], 422);
        }

        // 2) GET SONG
        $song = DB::table('songs')->where('id', $request->integer('song_id'))->first();
        
        if (!$song) {
            return response()->json(['message' => 'Melodia nu a fost găsită.'], 404);
        }

        // 3) GET VOTING CYCLE
        $cycleId = $request->integer('cycle_id') ?: $song->cycle_id;

        $cycle = DB::table('contest_cycles')
            ->where('id', $cycleId)
            ->where('lane', 'voting')
            ->where('status', 'open')
            ->first(['id', 'vote_end_at']);

        if (!$cycle) {
            return response()->json(['message' => 'Runda de vot nu este deschisă acum.'], 422);
        }

        // 4) CHECK IF VOTING IS OPEN
        $voteEndAt = Carbon::parse($cycle->vote_end_at);
        if ($now->gte($voteEndAt)) {
            return response()->json(['message' => 'Votul pentru această melodie s-a închis.'], 422);
        }

        // 5) PREVENT VOTING FOR SONG OUTSIDE CURRENT CYCLE
        if ((int)$song->cycle_id !== (int)$cycle->id) {
            return response()->json(['message' => 'Melodia nu aparține rundei de vot curente.'], 422);
        }

        // 6) PREVENT SELF-VOTING
        if ((int)$song->user_id === (int)$user->id) {
            return response()->json(['message' => 'Nu poți vota propria melodie.'], 422);
        }

        // 7) CHECK IF ALREADY VOTED
        $already = DB::table('votes')
            ->where('user_id', $user->id)
            ->where('cycle_id', $cycle->id)
            ->exists();

        if ($already) {
            return response()->json(['message' => 'Ai votat deja în această rundă.'], 422);
        }

        // 8) INSERT VOTE
        try {
            DB::table('votes')->insert([
                'user_id'    => $user->id,
                'song_id'    => $song->id,
                'cycle_id'   => $cycle->id,
                'created_at' => $now,
            ]);
        } catch (\Throwable $e) {
            $msg = strtolower($e->getMessage());
            if (str_contains($msg, 'unique') || str_contains($msg, 'duplicate')) {
                return response()->json(['message' => 'Ai votat deja în această rundă.'], 422);
            }
            throw $e;
        }

        return response()->json(['ok' => true, 'message' => '✅ Vot înregistrat!']);
    }
}
