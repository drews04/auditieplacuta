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
        // During freeze there may be no open voting; show the cycle with the last winner (for poster + list)
        if (!$cycleVote) {
            // Get the last winner's cycle (most recent winner)
            $lastWinner = DB::table('winners')->orderByDesc('id')->first();
            if ($lastWinner) {
                $cycleVote = DB::table('contest_cycles')
                    ->where('id', $lastWinner->cycle_id)
                    ->first();
            }
            // Fallback: if no winner yet, get last closed voting cycle
            if (!$cycleVote) {
                $cycleVote = DB::table('contest_cycles')
                    ->where('lane', 'voting')
                    ->where('status', 'closed')
                    ->orderByDesc('id')
                    ->first();
            }
        }

        // BULLETPROOF FREEZE DETECTION: Check if submission.theme_id is NULL
        $submissionCycle = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->first();
        
        $gapBetweenPhases = $submissionCycle && is_null($submissionCycle->theme_id);
        
        // DEBUG: Log state on every /concurs load
        \Log::info('[CONCURS_INDEX_LOAD]', [
            'user_id' => auth()->id(),
            'submission_cycle_id' => $submissionCycle->id ?? null,
            'submission_theme_id' => $submissionCycle->theme_id ?? 'NULL',
            'submission_theme_text' => $submissionCycle->theme_text ?? 'NULL',
            'gapBetweenPhases' => $gapBetweenPhases,
            'session_winner_chose' => session('winner_chose_theme'),
            'will_show_modal' => ($isWinner ?? false) && $gapBetweenPhases && !session('winner_chose_theme'),
        ]);
        
        // If site is no longer frozen, clear the winner_chose_theme flag for next day
        if (!$gapBetweenPhases && session('winner_chose_theme')) {
            session()->forget('winner_chose_theme');
        }

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
        $votingOpen      = (bool)$cycleVote && !$gapBetweenPhases && (($cycleVote->status ?? 'closed') === 'open');

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

        // ========== LAST WINNER (from archive) ==========
        $latestWinnerArchive = DB::table('contest_archives')
            ->orderByDesc('vote_end_at')
            ->first();
        
        // Keep old structure for compatibility, but populate from archive
        $winnerStripCycle = null;
        $winnerStripWinner = null;
        
        if ($latestWinnerArchive) {
            // Build cycle object
            $winnerStripCycle = (object)[
                'id' => $latestWinnerArchive->cycle_id,
                'theme_text' => $latestWinnerArchive->theme_category . ' — ' . $latestWinnerArchive->theme_name,
                'theme_category' => $latestWinnerArchive->theme_category,
                'theme_likes_count' => $latestWinnerArchive->theme_likes_count,
                'vote_end_at' => \Carbon\Carbon::parse($latestWinnerArchive->vote_end_at),
                'poster_url' => $latestWinnerArchive->poster_url,
            ];
            
            // Build winner object
            $winnerStripWinner = (object)[
                'user_id' => $latestWinnerArchive->winner_user_id,
                'song_id' => $latestWinnerArchive->winner_song_id,
                'vote_count' => $latestWinnerArchive->winner_votes,
                'points' => $latestWinnerArchive->winner_points,
                'user' => (object)[
                    'id' => $latestWinnerArchive->winner_user_id,
                    'name' => $latestWinnerArchive->winner_name,
                    'profile_photo_url' => $latestWinnerArchive->winner_photo_url,
                ],
                'song' => (object)[
                    'id' => $latestWinnerArchive->winner_song_id,
                    'title' => $latestWinnerArchive->winner_song_title,
                    'youtube_url' => $latestWinnerArchive->winner_song_url,
                ],
            ];
        }

        // ========== BULLETPROOF WINNER MODAL ==========

        $isWinner = false;
        $tomorrowPicked = !$gapBetweenPhases; // Theme has been picked if not frozen
        $showWinnerModal = false;
        $showWinnerPopup = false;

        \Log::info('[CONCURS_INDEX_MODAL_CHECK]', [
            'user_id' => auth()->id(),
            'gapBetweenPhases' => $gapBetweenPhases,
            'session_winner_chose_theme' => session('winner_chose_theme'),
            'session_force_theme_modal' => session('force_theme_modal'),
        ]);
        
        if (auth()->check() && $gapBetweenPhases) {
            // Check if current user is the last winner
            $latestWin = DB::table('winners')
                ->join('contest_cycles', 'winners.cycle_id', '=', 'contest_cycles.id')
                ->where('contest_cycles.status', 'closed')
                ->orderByDesc('winners.id')
                ->select('winners.*')
                ->first();
            
            \Log::info('[CONCURS_INDEX_WINNER_CHECK]', [
                'user_id' => auth()->id(),
                'latest_win_user_id' => $latestWin->user_id ?? null,
                'is_same_user' => $latestWin ? ((int)$latestWin->user_id === (int)auth()->id()) : false,
            ]);
            
            if ($latestWin && (int)$latestWin->user_id === (int)auth()->id()) {
                $isWinner = true;
                // Only show modal if winner hasn't chosen theme yet
                if (!session('winner_chose_theme')) {
                    $showWinnerModal = true;
                    \Log::info('[CONCURS_INDEX_SHOW_MODAL]', ['reason' => 'winner_chose_theme is false']);
                } else {
                    \Log::info('[CONCURS_INDEX_HIDE_MODAL]', ['reason' => 'winner_chose_theme is true']);
                }
            } else {
                // User is NOT the current winner, clear any old winner flags
                session()->forget('winner_chose_theme');
                session()->forget('force_theme_modal');
                \Log::info('[CONCURS_INDEX_CLEAR_SESSION]', ['reason' => 'user is not winner']);
            }
        } else {
            // Not in gap phase, clear winner flags
            session()->forget('winner_chose_theme');
            session()->forget('force_theme_modal');
            \Log::info('[CONCURS_INDEX_CLEAR_SESSION]', ['reason' => 'not in gap phase', 'gapBetweenPhases' => $gapBetweenPhases]);
        }

        return view('concurs.index', compact(
            'cycleSubmit', 'cycleVote',
            'songsSubmit', 'songsVote',
            'submissionsOpen', 'votingOpen',
            'winnerStripCycle', 'winnerStripWinner',
            'userHasVotedToday', 'userHasUploadedToday',
            'votedSongId',
            'gapBetweenPhases',
            'isWinner', 'tomorrowPicked',
            'showWinnerModal', 'showWinnerPopup'
        ));
    }
}
