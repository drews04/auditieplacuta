<?php

namespace App\Http\Controllers\Header\Concurs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ContestCycle;
use App\Models\Song;

class ConcursController extends Controller
{
    /* -----------------------------------------------------------------------
     | MAIN PAGE
     |-----------------------------------------------------------------------*/
    public function index()
    {
        $now = Carbon::now();

        // Find current submission cycle (today's)
        $cycleSubmit = ContestCycle::where('start_at', '<=', $now)
            ->where('submit_end_at', '>', $now)
            ->orderByDesc('start_at')
            ->first();

        // Find current voting cycle (yesterday's)
        $cycleVote = ContestCycle::where('vote_start_at', '<=', $now)
            ->where('vote_end_at', '>', $now)
            ->orderByDesc('vote_start_at')
            ->first();

        // Load songs
        $songsSubmit = $cycleSubmit
            ? Song::where('cycle_id', $cycleSubmit->id)->orderBy('id')->get()
            : collect();

        $songsVote = $cycleVote
            ? Song::where('cycle_id', $cycleVote->id)->orderBy('id')->get()
            : collect();

        $submissionsOpen = (bool) $cycleSubmit;
        $votingOpen      = (bool) $cycleVote;

        return view('concurs.concurs', compact(
            'cycleSubmit', 'cycleVote', 'songsSubmit', 'songsVote',
            'submissionsOpen', 'votingOpen'
        ));
    }

    /* -----------------------------------------------------------------------
     | WINNER THEME PICK (free text + auto promote)
     |-----------------------------------------------------------------------*/
    public function pickTheme(Request $request)
    {
        if (!auth()->check()) abort(403);

        $data = $request->validate([
            'theme' => ['required', 'string', 'min:3', 'max:120'],
        ]);

        $tz   = config('app.timezone', 'Europe/Bucharest');
        $user = auth()->user();

        // BULLETPROOF: Must be frozen submission (theme_id=NULL)
        $submissionCycle = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->first();
            
        if (!$submissionCycle || !is_null($submissionCycle->theme_id)) {
            return back()->with('error', 'Nu este fereastră de alegere a temei.');
        }

        // Latest finished voting cycle
        $finished = DB::table('contest_cycles')
            ->where('lane', 'voting')
            ->whereNotNull('vote_end_at')
            ->orderByDesc('vote_end_at')
            ->first();

        if (!$finished) {
            return back()->with('error', 'Nu există o rundă de vot închisă.');
        }

        // Must be the winner
        $winner = DB::table('winners')->where('cycle_id', $finished->id)->first();
        if (!$winner || (int) $winner->user_id !== (int) $user->id) {
            return back()->with('error', 'Doar câștigătorul poate alege tema.');
        }

        // Open submission cycle without theme
        $submission = DB::table('contest_cycles')
            ->where('lane', 'submission')
            ->where('status', 'open')
            ->whereNull('theme_id')
            ->whereNull('theme_text')
            ->orderByDesc('id')
            ->first();

        if (!$submission) {
            return back()->with('error', 'Nu există o fereastră de încărcare fără temă.');
        }

        $themeText = trim($data['theme']);

        DB::beginTransaction();
        try {
            // 1️⃣ Persist new theme (free text, no category)
            $themeId = DB::table('contest_themes')->insertGetId([
                'name'              => $themeText,
                'category'          => null,
                'chosen_by_user_id' => $user->id,
                'created_at'        => now($tz),
                'updated_at'        => now($tz),
            ]);

            // 2️⃣ Write to submission cycle
            DB::table('contest_cycles')->where('id', $submission->id)->update([
                'theme_id'   => $themeId,
                'theme_text' => $themeText,
                'updated_at' => now($tz),
            ]);

            // 3️⃣ BULLETPROOF UNFREEZE: Set theme_id (the unfreeze switch)
            DB::table('contest_cycles')
                ->where('id', $submissionCycle->id)
                ->update([
                    'theme_id'   => $themeId,
                    'theme_text' => $themeText,
                    'updated_at' => now($tz),
                ]);

            // 4️⃣ Audit
            DB::table('contest_audit_logs')->insert([
                'event_type' => 'winner_theme_manual',
                'cycle_id'   => $submission->id,
                'seed'       => null,
                'details'    => json_encode([
                    'user_id'    => $user->id,
                    'theme_id'   => $themeId,
                    'theme_name' => $themeText,
                ]),
                'created_at' => now($tz),
                'updated_at' => now($tz),
            ]);

            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            if (($e->errorInfo[1] ?? null) === 1062) {
                // BULLETPROOF UNFREEZE: Set theme_id (the unfreeze switch)
                DB::table('contest_cycles')
                    ->where('id', $submissionCycle->id)
                    ->update([
                        'theme_id'   => $themeId,
                        'theme_text' => $themeText,
                        'updated_at' => now($tz),
                    ]);
                return back()->with('status', 'Tema a fost deja setată.');
            }
            throw $e;
        }

        // 5️⃣ Instantly promote + open new cycle
        $adminCtrl = new \App\Http\Controllers\Admin\ConcursAdminController();
        $adminCtrl->promoteAndOpenNewCycle();

        return back()->with('status', "Tema setată: {$themeText}");
    }
}
