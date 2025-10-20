<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use App\Models\ThemeLike;
use App\Models\ThemePool;
use App\Models\ContestTheme;

class ThemeLikeController extends Controller
{
    public function toggle(Request $request)
{
    try {
        if (!Auth::check()) {
            return response()->json(['ok' => false, 'message' => 'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'likeable_type' => 'required|string|in:pool,contest',
            'likeable_id'   => 'required|integer|min:1',
        ]);

        $uiType = $data['likeable_type'];   // 'pool' | 'contest' (from frontend)
        $themeId = (int) $data['likeable_id'];
        $userId = (int) Auth::id();

        // Verify theme exists
        $target = \App\Models\ContestTheme::find($themeId);
        if (!$target) {
            \Log::warning('Theme like: target not found', ['theme_id' => $themeId]);
            return response()->json(['ok' => false, 'message' => 'Item not found.'], 404);
        }

        // Toggle (delete then maybe create)
        // Note: theme_likes table uses theme_id, not polymorphic likeable_type/likeable_id
        $deleted = DB::table('theme_likes')
            ->where('user_id', $userId)
            ->where('theme_id', $themeId)
            ->delete();

        $liked = false;
        if ($deleted === 0) {
            DB::table('theme_likes')->insert([
                'user_id'    => $userId,
                'theme_id'   => $themeId,
                'created_at' => now(),
            ]);
            $liked = true;
        }

        // Count total likes for this theme
        $count = DB::table('theme_likes')
            ->where('theme_id', $themeId)
            ->count();

        return response()->json(['ok' => true, 'liked' => $liked, 'count' => $count]);
    } catch (\Throwable $e) {
        \Log::error('Theme like toggle FAILED', [
            'msg'     => $e->getMessage(),
            'code'    => $e->getCode(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'payload' => $request->all(),
            'user_id' => Auth::id(),
        ]);
        return response()->json(['ok' => false, 'message' => 'Cannot toggle like.'], 500);
    }
}

}
