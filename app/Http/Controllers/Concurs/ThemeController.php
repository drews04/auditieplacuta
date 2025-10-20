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
        return view('concurs.theme.pick', [
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
        $data = $request->validate([
            'category' => ['required', 'string', 'max:40'],
            'theme'    => ['required', 'string', 'max:120'],
        ]);

        $category  = trim($data['category']);
        $theme     = trim($data['theme']);
        $themeText = "{$category} — {$theme}";
        $tz        = config('app.timezone', 'Europe/Bucharest');
        $now       = Carbon::now($tz);

        // 1) VERIFY WINDOW='waiting_theme'
        $window = DB::table('contest_flags')->where('name', 'window')->value('value');
        if ($window !== 'waiting_theme') {
            return $this->respondError('Nu este fereastră de alegere a temei.');
        }

        // 2) FIND LAST CLOSED VOTING CYCLE
        $lastVoting = DB::table('contest_cycles')
            ->where('lane', 'voting')
            ->where('status', 'closed')
            ->orderByDesc('vote_end_at')
            ->first();

        if (!$lastVoting) {
            return $this->respondError('Nu există o rundă încheiată pentru care să alegi tema.');
        }

        // 3) VERIFY USER IS THE WINNER
        $win = DB::table('winners')->where('cycle_id', $lastVoting->id)->first();
        if (!$win || !auth()->check() || (int)$win->user_id !== (int)auth()->id()) {
            return $this->respondError('Nu ai permisiunea să alegi tema.');
        }

        // 4) VERIFY 1-HOUR WINDOW (20:00 → 21:00)
        $voteEndAt = Carbon::parse($lastVoting->vote_end_at);
        $deadline  = $voteEndAt->copy()->addHour();
        
        if ($now->gt($deadline)) {
            return $this->respondError('Fereastra de alegere a temei s-a închis (după 21:00).');
        }

        // 5) CREATE THEME & NEW SUBMISSION CYCLE
        DB::beginTransaction();
        try {
            // Create theme in contest_themes
            $themeId = DB::table('contest_themes')->insertGetId([
                'name'              => $themeText,
                'chosen_by_user_id' => auth()->id(),
                'created_at'        => $now,
            ]);

            // Create new submission cycle (opens immediately)
            $next2000 = $now->copy()->addDay()->setTime(20, 0, 0);
            
            DB::table('contest_cycles')->insert([
                'theme_id'      => $themeId,
                'theme_text'    => $themeText,
                'lane'          => 'submission',
                'status'        => 'open',
                'start_at'      => $now,
                'submit_end_at' => $next2000,
                'vote_end_at'   => null, // Will be set when promoted to voting
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            // Unlock window
            DB::table('contest_flags')->updateOrInsert(
                ['name' => 'window'],
                ['value' => null, 'updated_at' => $now]
            );

            // Audit log
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

        session()->forget('ap_show_theme_modal');
        session()->put('winner_chose_theme', 1);

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
