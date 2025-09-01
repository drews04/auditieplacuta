<?php

namespace App\Http\Controllers;

use App\Models\ContestTheme;
use App\Models\ThemeLike;
use App\Models\ThemePool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ThemeLikeController extends Controller
{
    // No constructor middleware here — route handles auth

    public function toggle(Request $request)
    {
        try {
            $data = $request->validate([
                'likeable_type' => 'required|string|in:pool,contest',
                'likeable_id'   => 'required|integer|min:1',
            ]);

            $map = [
                'pool'    => ThemePool::class,
                'contest' => ContestTheme::class,
            ];
            $class  = $map[$data['likeable_type']];
            $id     = (int) $data['likeable_id'];
            $userId = Auth::id();

            // Ensure target exists (ThemePool must be active)
            if ($class === ThemePool::class) {
                $target = $class::query()->whereKey($id)->where('active', true)->first();
            } else {
                $target = $class::query()->find($id);
            }
            if (!$target) {
                Log::warning('Theme like: target not found', ['type' => $class, 'id' => $id]);
                return response()->json(['ok' => false, 'message' => 'Item not found.'], 404);
            }

            // Toggle
            $existing = ThemeLike::query()
                ->where('user_id', $userId)
                ->where('likeable_type', $class)
                ->where('likeable_id', $id)
                ->first();

            $liked = false;
            if ($existing) {
                $existing->delete();
            } else {
                ThemeLike::create([
                    'user_id'       => $userId,
                    'likeable_type' => $class,
                    'likeable_id'   => $id,
                ]);
                $liked = true;
            }

            $count = ThemeLike::query()
                ->where('likeable_type', $class)
                ->where('likeable_id', $id)
                ->count();

            return response()->json(['ok' => true, 'liked' => $liked, 'count' => $count]);

        } catch (\Throwable $e) {
            Log::error('Theme like toggle FAILED', [
                'msg'     => $e->getMessage(),
                'code'    => $e->getCode(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'payload' => $request->all(),
                'user_id' => Auth::id(),
            ]);
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
