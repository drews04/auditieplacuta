<?php

namespace App\Http\Controllers\Concurs\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Admin controls for managing contest cycles
 * REBUILT PER COMPENDIUM V2 (2025-10-20)
 */
class CycleController extends Controller
{
    /**
     * Admin dashboard widget (shows current state)
     */
    public function dashboard()
    {
        $now = Carbon::now()->timezone(config('app.timezone'));

        $cycleSubmit = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->orderByDesc('start_at')
            ->first();

        $cycleVote = DB::table('contest_cycles')
            ->where('lane', 'voting')
            ->where('status', 'open')
            ->orderByDesc('start_at')
            ->first();

        return view('concurs.admin.dashboard', compact('cycleSubmit', 'cycleVote'));
    }

    /**
     * START button - Initialize contest with first theme
     * 
     * LOGIC PER COMPENDIUM V2:
     * 1. WIPE all active cycles (hard reset)
     * 2. Pick random theme from theme_pools (or use fallback)
     * 3. Create submission cycle (opens immediately)
     * 4. Vote page stays empty until first 20:00
     * 5. At first 20:00: no winner (no votes yet), songs move to vote, new upload opens
     */
    public function start(Request $request)
    {
        if (!auth()->check() || !optional(auth()->user())->is_admin) {
            abort(403, 'Admin access required');
        }

        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        // Calculate next 20:00
        $next2000 = $now->copy()->setTime(20, 0, 0);
        if ($now->hour >= 20) {
            $next2000->addDay();
        }

        DB::beginTransaction();
        try {
            // 1) HARD RESET - WIPE **ALL** CYCLES (NO EXCEPTIONS)
            $allCycleIds = DB::table('contest_cycles')->pluck('id');

            if ($allCycleIds->isNotEmpty()) {
                DB::table('votes')->whereIn('cycle_id', $allCycleIds)->delete();
                DB::table('songs')->whereIn('cycle_id', $allCycleIds)->delete();
                DB::table('winners')->whereIn('cycle_id', $allCycleIds)->delete();
                DB::table('contest_cycles')->whereIn('id', $allCycleIds)->delete();
            }

            // 2) GET BOTH THEMES FROM FORM
            $categoryMap = [
                'csd'     => 'CSD',
                'itc'     => 'ITC',
                'artisti' => 'Artiști',
                'genuri'  => 'Genuri',
            ];
            
            // Helper: pick a random theme text from theme_pools (fallback to wordbank)
            $pickRandomTheme = function (): string {
                $pool = DB::table('theme_pools')
                    ->where('is_active', 1)
                    ->inRandomOrder()
                    ->value('text');

                if ($pool) return $pool;

                $wordbank = [
                    'Neon Dreams', 'Lost Frequencies', 'Silent Waves', 'Echoes of Time',
                    'Retro Pulse', 'Urban Nights', 'Velvet Sky', 'Digital Mirage',
                    'Parallel Lines', 'Infinite Loop', 'Golden Hour', 'Electric Soul',
                    'Midnight City', 'Crystal Castles', 'Analog Sunset', 'Moonlight Drive'
                ];
                return $wordbank[array_rand($wordbank)];
            };

            // THEME A (opens NOW) — allow empty name → random from pools
            $catA = strtolower(trim($request->input('theme_a_category', '')));
            $nameA = trim($request->input('theme_a_name', ''));
            $categoryA = $categoryMap[$catA] ?? 'CSD';
            if ($nameA === '') { $nameA = $pickRandomTheme(); }
            $themeTextA = "{$categoryA} - {$nameA}";
            
            // THEME B (will be used at 20:00) — allow empty name → random from pools
            $catB = strtolower(trim($request->input('theme_b_category', '')));
            $nameB = trim($request->input('theme_b_name', ''));
            $categoryB = $categoryMap[$catB] ?? 'CSD';
            if ($nameB === '') { $nameB = $pickRandomTheme(); }
            $themeTextB = "{$categoryB} - {$nameB}";

            // 3) CREATE THEME A IN contest_themes
            $themeAId = DB::table('contest_themes')->insertGetId([
                'name'              => $themeTextA,
                'chosen_by_user_id' => null,
                'created_at'        => $now,
            ]);
            
            // 4) CREATE THEME B IN contest_themes
            $themeBId = DB::table('contest_themes')->insertGetId([
                'name'              => $themeTextB,
                'chosen_by_user_id' => null,
                'created_at'        => $now,
            ]);

            // 5) CREATE SUBMISSION CYCLE FOR THEME A (opens immediately)
            DB::table('contest_cycles')->insert([
                'theme_id'      => $themeAId,
                'theme_text'    => $themeTextA,
                'lane'          => 'submission',
                'status'        => 'open',
                'start_at'      => $now,
                'submit_end_at' => $next2000,
                'vote_end_at'   => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
            
            // 6) STORE THEME B for use at 20:00
            cache()->put('concurs_next_theme_id', $themeBId, now()->addDays(2));
            cache()->put('concurs_next_theme_text', $themeTextB, now()->addDays(2));

            // 5) RESET WINDOW FLAG
            // BULLETPROOF: No more contest_flags needed - theme_id is the switch

            // 7) AUDIT LOG (optional - skip if table doesn't exist)
            try {
                DB::table('contest_audit_logs')->insert([
                    'event_type' => 'start_button',
                    'cycle_id'   => null,
                    'details'    => json_encode([
                        'theme_a' => $themeTextA,
                        'theme_b' => $themeTextB,
                        'next_20' => $next2000->toDateTimeString(),
                        'is_first_iteration' => true,
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
            return back()->with('error', 'Eroare la pornirea concursului: ' . $e->getMessage());
        }

        return back()->with('status', "✅ Concurs pornit! Tema A: {$themeTextA} (upload până la {$next2000->format('H:i')}). Tema B: {$themeTextB} (la 20:00).");
    }

    /**
     * Health check JSON endpoint
     */
    public function health()
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz);

        // BULLETPROOF: Check if submission is frozen (theme_id=NULL)
        $submissionCycle = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->first();
        $isFrozen = $submissionCycle && is_null($submissionCycle->theme_id);

        $submit = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->orderByDesc('id')->first();

        $voting = DB::table('contest_cycles')
            ->where('lane', 'voting')
            ->where('status', 'open')
            ->orderByDesc('id')->first();

        $finished = DB::table('contest_cycles')
            ->where('status', 'closed')
            ->orderByDesc('vote_end_at')->first();

        $winner = $finished
            ? DB::table('winners')->where('cycle_id', $finished->id)->first()
            : null;

        $audit = DB::table('contest_audit_logs')->orderByDesc('id')->first();

        return response()->json([
            'time'   => $now->toDateTimeString(),
            'frozen' => $isFrozen ? 'YES (theme_id=NULL)' : 'NO',
            'lanes'  => [
                'submission' => $submit ? [
                    'id'          => $submit->id,
                    'theme'       => $submit->theme_text,
                    'start_at'    => $submit->start_at,
                    'submit_end'  => $submit->submit_end_at,
                    'status'      => $submit->status,
                ] : null,
                'voting' => $voting ? [
                    'id'          => $voting->id,
                    'theme'       => $voting->theme_text,
                    'start_at'    => $voting->start_at,
                    'vote_end'    => $voting->vote_end_at,
                    'status'      => $voting->status,
                ] : null,
            ],
            'last_finished' => $finished ? [
                'id'          => $finished->id,
                'vote_end_at' => $finished->vote_end_at,
            ] : null,
            'winner' => $winner ? [
                'song_id'       => $winner->song_id,
                'user_id'       => $winner->user_id,
                'decide_method' => $winner->decide_method ?? 'normal',
            ] : null,
            'last_audit' => $audit ? [
                'id'        => $audit->id,
                'type'      => $audit->event_type,
                'cycle_id'  => $audit->cycle_id,
                'created_at'=> $audit->created_at,
            ] : null,
        ]);
    }

    /**
     * Manual close at 20:00 (for testing)
     */
    public function close()
    {
        $tz  = config('app.timezone', 'Europe/Bucharest');
        $now = Carbon::now($tz)->setTime(20, 0, 0);

        // BULLETPROOF: Freeze submission (theme_id = NULL)
        DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->update([
                'theme_id'   => null,
                'theme_text' => null,
                'updated_at' => $now,
            ]);

        return back()->with('status', 'Submission frozen (theme_id=NULL). Winner can pick theme.');
    }
}
