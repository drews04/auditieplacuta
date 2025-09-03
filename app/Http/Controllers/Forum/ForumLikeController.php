<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forum\Thread;
use App\Models\Forum\Post;
use Illuminate\Http\Request;

class ForumLikeController extends Controller
{
    public function toggleThread(Request $request, Thread $thread)
    {
        $userId = $request->user()->id;

        $existing = $thread->likes()->where('user_id', $userId)->first();
        if ($existing) {
            $existing->delete();
        } else {
            $thread->likes()->create(['user_id' => $userId]);
        }

        return response()->json([
            'liked' => !$existing,
            'count' => $thread->likes()->count(),
        ]);
    }

    public function togglePost(Request $request, Post $post)
    {
        $userId = $request->user()->id;

        $existing = $post->likes()->where('user_id', $userId)->first();
        if ($existing) {
            $existing->delete();
        } else {
            $post->likes()->create(['user_id' => $userId]);
        }

        return response()->json([
            'liked' => !$existing,
            'count' => $post->likes()->count(),
        ]);
    }
}
