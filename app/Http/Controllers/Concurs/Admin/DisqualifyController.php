<?php

namespace App\Http\Controllers\Concurs\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin controller to manually disqualify/re-enable songs
 */
class DisqualifyController extends Controller
{
    /**
     * Toggle disqualification status of a song
     * 
     * POST /concurs/admin/disqualify/{songId}
     * Body: { action: 'disqualify' | 'enable', reason: 'optional reason' }
     */
    public function toggle(Request $request, $songId)
    {
        // Admin-only
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $action = $request->input('action'); // 'disqualify' or 'enable'
        $reason = $request->input('reason', 'Descalificat manual de admin');

        $song = DB::table('songs')->where('id', $songId)->first();

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        if ($action === 'disqualify') {
            DB::table('songs')
                ->where('id', $songId)
                ->update([
                    'is_disqualified' => true,
                    'disqualification_reason' => $reason,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'ok' => true,
                'message' => 'Melodie descalificată',
                'is_disqualified' => true,
            ]);
        } else {
            DB::table('songs')
                ->where('id', $songId)
                ->update([
                    'is_disqualified' => false,
                    'disqualification_reason' => null,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'ok' => true,
                'message' => 'Melodie re-activată',
                'is_disqualified' => false,
            ]);
        }
    }
}
