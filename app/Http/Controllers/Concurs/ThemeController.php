<?php

namespace App\Http\Controllers\Concurs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Handles winner theme picking (20:00-21:00 window)
 * REBUILT PER COMPENDIUM V2 (2025-10-20)
 */
class ThemeController extends Controller
{
    /**
     * Show theme picker form (full page or modal)
     */
    public function create(Request $request)
    {
        return view('concurs.alege-tema', [
            'winnerName' => auth()->user()->name ?? 'campion',
            'categories' => [
                ['code' => 'CSD',     'label' => 'CSD — Cu și despre'],
                ['code' => 'ITC',     'label' => 'ITC — În titlu cuvânt'],
                ['code' => 'Artiști', 'label' => 'Artiști'],
                ['code' => 'Genuri',  'label' => 'Genuri'],
            ],
        ]);
    }

    /**
     * Winner submits the theme (20:00-21:00 window)
     * 
     * LOGIC:
     * 1. Verify user is the winner
     * 2. Verify window='waiting_theme' and within 1-hour window
     * 3. Create new submission cycle with chosen theme
     * 4. Unlock window
     * 5. Redirect to /concurs (upload opens instantly)
     */
    public function store(Request $request)
    {
        // ULTRA AGGRESSIVE LOGGING
        file_put_contents(storage_path('logs/theme_pick_trace.txt'), "[" . date('Y-m-d H:i:s') . "] CONTROLLER HIT!\n", FILE_APPEND);
        file_put_contents(storage_path('logs/theme_pick_trace.txt'), "User: " . (auth()->id() ?? 'NOT LOGGED IN') . "\n", FILE_APPEND);
        file_put_contents(storage_path('logs/theme_pick_trace.txt'), "Data: " . json_encode($request->all()) . "\n\n", FILE_APPEND);
        
        \Log::info('[THEME_PICK_START]', [
            'user_id' => auth()->id(),
            'request_data' => $request->all(),
        ]);
        
        $data = $request->validate([
            'category' => ['required', 'string', 'max:40'],
            'theme'    => ['required', 'string', 'max:120'],
        ]);

        $category  = trim($data['category']);
        $theme     = trim($data['theme']);
        $themeText = "{$category} — {$theme}";
        $tz        = config('app.timezone', 'Europe/Bucharest');
        $now       = Carbon::now($tz);

        // 1) BULLETPROOF VERIFY: submission must be frozen (theme_id=NULL)
        $submissionCycle = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->first();
        
        \Log::info('[THEME_PICK_CHECK1]', [
            'found_submission_cycle' => $submissionCycle ? $submissionCycle->id : null,
            'theme_id' => $submissionCycle ? $submissionCycle->theme_id : null,
        ]);
            
        if (!$submissionCycle || !is_null($submissionCycle->theme_id)) {
            \Log::error('[THEME_PICK_FAIL1]', ['reason' => 'No frozen cycle']);
            return $this->respondError('Nu este fereastră de alegere a temei.');
        }

        // 2) FIND LAST WINNER (most recent)
        $lastWinner = DB::table('winners')
            ->orderByDesc('id')
            ->first();

        \Log::info('[THEME_PICK_CHECK2]', [
            'found_last_winner' => $lastWinner ? $lastWinner->id : null,
            'winner_cycle_id' => $lastWinner ? $lastWinner->cycle_id : null,
            'winner_user_id' => $lastWinner ? $lastWinner->user_id : null,
        ]);

        if (!$lastWinner) {
            \Log::error('[THEME_PICK_FAIL2]', ['reason' => 'No winner found']);
            return $this->respondError('Nu există un câștigător pentru care să alegi tema.');
        }

        // Get the voting cycle for this winner
        $lastVoting = DB::table('contest_cycles')
            ->where('id', $lastWinner->cycle_id)
            ->first();

        if (!$lastVoting) {
            \Log::error('[THEME_PICK_FAIL2B]', ['reason' => 'Winner cycle not found']);
            return $this->respondError('Ciclul câștigătorului nu a fost găsit.');
        }

        // 3) VERIFY USER IS THE WINNER
        $win = $lastWinner;
        
        \Log::info('[THEME_PICK_CHECK3]', [
            'found_winner' => $win ? $win->user_id : null,
            'current_user' => auth()->id(),
            'matches' => $win && (int)$win->user_id === (int)auth()->id(),
        ]);
        
        if (!$win || !auth()->check() || (int)$win->user_id !== (int)auth()->id()) {
            \Log::error('[THEME_PICK_FAIL3]', ['reason' => 'Not the winner']);
            return $this->respondError('Nu ai permisiunea să alegi tema.');
        }

        // 4) VERIFY 1-HOUR WINDOW (20:00 → 21:00)
        $voteEndAt = Carbon::parse($lastVoting->vote_end_at);
        $deadline  = $voteEndAt->copy()->addHour();
        
        \Log::info('[THEME_PICK_CHECK4]', [
            'vote_end_at' => $voteEndAt->toDateTimeString(),
            'deadline' => $deadline->toDateTimeString(),
            'now' => $now->toDateTimeString(),
            'is_past_deadline' => $now->gt($deadline),
        ]);
        
        if ($now->gt($deadline)) {
            \Log::error('[THEME_PICK_FAIL4]', ['reason' => 'Past deadline']);
            return $this->respondError('Fereastra de alegere a temei s-a închis (după 21:00).');
        }

        // 5) CREATE THEME & NEW SUBMISSION CYCLE
        DB::beginTransaction();
        try {
            // Check if theme already exists (reuse it) or create new one
            $existingTheme = DB::table('contest_themes')->where('name', $themeText)->first();
            
            if ($existingTheme) {
                $themeId = $existingTheme->id;
                \Log::info('[THEME_PICK_REUSE]', [
                    'theme_id' => $themeId,
                    'theme_name' => $themeText,
                    'reason' => 'Theme already exists',
                ]);
            } else {
                $themeId = DB::table('contest_themes')->insertGetId([
                    'name'              => $themeText,
                    'chosen_by_user_id' => auth()->id(),
                    'created_at'        => $now,
                ]);
                \Log::info('[THEME_PICK_CREATE]', [
                    'theme_id' => $themeId,
                    'theme_name' => $themeText,
                ]);
            }

            // Find the NEWEST frozen submission opened at 20:00
            $frozen = DB::table('contest_cycles')
                ->where('lane', 'submission')
                ->where('status', 'open')
                ->whereNull('theme_id')
                ->orderByDesc('id')
                ->first(['id', 'submit_end_at']);

            if (!$frozen) {
                throw new \Exception('Nu am găsit runda înghețată pentru setarea temei.');
            }

            // BULLETPROOF ROTATION: Promote frozen → voting, open new submission with chosen theme
            $tomorrow2000 = Carbon::parse($frozen->submit_end_at, $tz)->addDay()->setTime(20, 0, 0);

            // 1. Promote frozen submission → voting (songs move to vote page)
            // IMPORTANT: poster_url is preserved automatically (not in UPDATE, so it stays)
            DB::table('contest_cycles')
                ->where('id', $frozen->id)
                ->update([
                    'lane'        => 'voting',
                    'status'      => 'open',
                    'vote_end_at' => $tomorrow2000,
                    // Keep original theme_id/theme_text so vote page shows yesterday's theme
                    // poster_url is NOT updated, so it stays from submission phase
                    'updated_at'  => $now,
                ]);

            // 2. Open NEW submission for uploads with chosen theme
            DB::table('contest_cycles')->insert([
                'lane'          => 'submission',
                'status'        => 'open',
                'theme_id'      => $themeId,
                'theme_text'    => $themeText,
                'start_at'      => $now,
                'submit_end_at' => $tomorrow2000,
                'vote_end_at'   => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
            
            \Log::info('[THEME_PICK_ROTATE]', [
                'promoted_cycle' => $frozen->id,
                'new_theme_id' => $themeId,
                'new_theme_text' => $themeText,
                'user_id' => auth()->id(),
            ]);

            // Audit log (optional - skip if table doesn't exist)
            try {
                DB::table('contest_audit_logs')->insert([
                    'event_type' => 'winner_theme_pick',
                    'cycle_id'   => $lastVoting->id,
                    'details'    => json_encode([
                        'theme_id'   => $themeId,
                        'theme_name' => $themeText,
                        'category'   => $category,
                        'user_id'    => auth()->id(),
                    ]),
                    'created_at' => $now,
                ]);
            } catch (\Throwable $e) {
                // Table doesn't exist yet, skip audit log
                \Log::warning('contest_audit_logs table missing: ' . $e->getMessage());
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError('Eroare la salvarea temei: ' . $e->getMessage());
        }

        // Trigger poster inheritance
        try {
            \Artisan::call('concurs:inherit-poster');
        } catch (\Throwable $e) {
            \Log::warning('concurs:inherit-poster failed: ' . $e->getMessage());
        }

        // Clear ALL modal flags to prevent loop
        \Log::info('[THEME_PICK_BEFORE_SESSION]', [
            'user_id' => auth()->id(),
            'session_before' => session()->all(),
        ]);
        
        session()->forget('ap_show_theme_modal');
        session()->forget('force_theme_modal');
        session()->put('winner_chose_theme', true);
        session()->save(); // Force save session immediately
        
        \Log::info('[THEME_PICK_AFTER_SESSION]', [
            'user_id' => auth()->id(),
            'winner_chose_theme' => session('winner_chose_theme'),
            'session_after' => session()->all(),
        ]);
        
        // DEBUG: Log successful theme pick
        \Log::info('[THEME_PICK_SUCCESS]', [
            'user_id' => auth()->id(),
            'theme_id' => $themeId,
            'theme_text' => $themeText,
            'new_submission_created' => true,
            'frozen_cycle_id' => $frozen->id,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok'       => true,
                'message'  => 'Tema a fost salvată.',
                'redirect' => route('concurs'),
            ]);
        }

        return redirect()->route('concurs')->with('success', '✅ Tema a fost salvată. Concursul continuă!');
    }

    /**
     * Helper to respond with error
     */
    private function respondError($message)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['ok' => false, 'message' => $message], 400);
        }
        return redirect()->route('concurs')->with('error', $message);
    }
}
