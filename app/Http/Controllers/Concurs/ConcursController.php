<?php

namespace App\Http\Controllers\Concurs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Main Concurs page - displays both upload and vote sections
 * REBUILT PER COMPENDIUM V2 (2025-10-20)
 */
class ConcursController extends Controller
{
    public function index(Request $request)
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        // ========== CYCLES (using 'lane' field) ==========

        // Open SUBMISSION cycle (where users upload songs)
        $cycleSubmit = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();

        // Open VOTING cycle (where users vote on songs)
        $cycleVote = DB::table('contest_cycles')
            ->where('lane', 'voting')
            ->where('status', 'open')
            ->where('vote_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();

        // Gap detection (read-only window between 20:00 and theme pick)
        $window = DB::table('contest_flags')->where('name', 'window')->value('value');
        $gapBetweenPhases = ($window === 'waiting_theme');

        // ========== SONGS ==========

        // Submission songs (today's uploads)
        $songsSubmit = $cycleSubmit
            ? DB::table('songs')
                ->where('cycle_id', $cycleSubmit->id)
                ->join('users', 'users.id', '=', 'songs.user_id')
                ->select('songs.*', 'users.name as user_name')
                ->orderBy('songs.id')
                ->get()
            : collect();

        // Voting songs (yesterday's uploads, now being voted on)
        $songsVote = $cycleVote
            ? DB::table('songs')
                ->where('cycle_id', $cycleVote->id)
                ->join('users', 'users.id', '=', 'songs.user_id')
                ->select('songs.*', 'users.name as user_name')
                ->orderBy('songs.id')
                ->get()
            : collect();

        // ========== FLAGS ==========

        $submissionsOpen = (bool)$cycleSubmit && !$gapBetweenPhases;
        $votingOpen      = (bool)$cycleVote && !$gapBetweenPhases;

        // ========== PER-USER FLAGS ==========

        $authId = auth()->id();
        
        $votedSongId = null;
        $userHasVotedToday = false;
        if ($authId && $cycleVote) {
            $userVote = DB::table('votes')
                ->where('user_id', $authId)
                ->where('cycle_id', $cycleVote->id)
                ->first(['song_id']);
            
            $userHasVotedToday = (bool)$userVote;
            $votedSongId = $userVote->song_id ?? null;
        }

        $userHasUploadedToday = $authId && $cycleSubmit
            ? DB::table('songs')
                ->where('user_id', $authId)
                ->where('cycle_id', $cycleSubmit->id)
                ->exists()
            : false;

        // ========== WINNER STRIP (last finished cycle) ==========

        $winnerStripCycle = DB::table('contest_cycles')
            ->where('status', 'closed')
            ->whereNotNull('vote_end_at')
            ->orderByDesc('vote_end_at')
            ->first();

        $winnerStripWinner = null;
        if ($winnerStripCycle) {
            $winnerStripWinner = DB::table('winners')
                ->where('cycle_id', $winnerStripCycle->id)
                ->first();
            
            if ($winnerStripWinner) {
                // Eager load user and song
                $winnerStripWinner->user = DB::table('users')
                    ->where('id', $winnerStripWinner->user_id)
                    ->first(['id', 'name']);
                $winnerStripWinner->song = DB::table('songs')
                    ->where('id', $winnerStripWinner->song_id)
                    ->first(['id', 'title', 'youtube_url']);
            }
        }

        // ========== WINNER MODAL (20:00-21:00 window) ==========

        $isWinner = false;
        $tomorrowPicked = ($window !== 'waiting_theme'); // Theme has been picked if not waiting
        $showWinnerModal = false;
        $showWinnerPopup = false;

        if (auth()->check() && $window === 'waiting_theme') {
            // Check if current user is the last winner
            $latestWin = DB::table('winners')
                ->join('contest_cycles', 'winners.cycle_id', '=', 'contest_cycles.id')
                ->where('contest_cycles.status', 'closed')
                ->orderByDesc('winners.id')
                ->select('winners.*')
                ->first();
            
            if ($latestWin && (int)$latestWin->user_id === (int)auth()->id()) {
                $isWinner = true;
                $showWinnerModal = !session('winner_chose_theme'); // Show modal if they haven't chosen yet
            }
        }

        return view('concurs.index', compact(
            'cycleSubmit', 'cycleVote',
            'songsSubmit', 'songsVote',
            'submissionsOpen', 'votingOpen',
            'winnerStripCycle', 'winnerStripWinner',
            'userHasVotedToday', 'userHasUploadedToday',
            'votedSongId',
            'gapBetweenPhases',
            'isWinner', 'window', 'tomorrowPicked',
            'showWinnerModal', 'showWinnerPopup'
        ));
    }
}
