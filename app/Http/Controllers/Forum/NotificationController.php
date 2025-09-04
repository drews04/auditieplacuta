<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forum\Post;
use App\Models\Forum\Thread;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function unreadSummary(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Parse since parameter (ISO8601 or epoch ms)
        $since = $request->query('since');
        if ($since) {
            if (is_numeric($since)) {
                // Epoch milliseconds
                $sinceDate = Carbon::createFromTimestampMs($since);
            } else {
                // ISO8601 string
                $sinceDate = Carbon::parse($since);
            }
        } else {
            // Default to 12 hours ago
            $sinceDate = Carbon::now()->subHours(12);
        }

        // Find new replies where:
        // 1. reply.user_id != current user
        // 2. reply belongs to a thread owned by current user OR
        // 3. reply is a child whose parent reply is by current user
        $newReplies = Post::with(['thread', 'user', 'parent.user'])
            ->where('user_id', '!=', $user->id)
            ->where('created_at', '>', $sinceDate)
            ->where(function ($query) use ($user) {
                $query->whereHas('thread', function ($threadQuery) use ($user) {
                    // Replies to threads owned by current user
                    $threadQuery->where('user_id', $user->id);
                })->orWhereHas('parent', function ($parentQuery) use ($user) {
                    // Replies to posts by current user (one-level nesting)
                    $parentQuery->where('user_id', $user->id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($newReplies->isEmpty()) {
            return response()->json([
                'has_new' => false,
                'type' => null,
                'count' => 0,
                'thread' => null,
                'latest_user' => null,
                'since' => $sinceDate->toISOString()
            ]);
        }

        $count = $newReplies->count();
        $latestReply = $newReplies->first();
        $threadIds = $newReplies->pluck('thread_id')->unique();

        // Determine type and build response
        if ($count === 1) {
            // Single reply
            $thread = $latestReply->thread;
            return response()->json([
                'has_new' => true,
                'type' => 'single',
                'count' => 1,
                'thread' => [
                    'id' => $thread->id,
                    'title' => $thread->title,
                    'url' => route('forum.threads.show', $thread->slug)
                ],
                'latest_user' => [
                    'id' => $latestReply->user->id,
                    'name' => $latestReply->user->name
                ],
                'since' => $sinceDate->toISOString()
            ]);
        } elseif ($threadIds->count() === 1) {
            // Multiple replies in one thread
            $thread = $latestReply->thread;
            return response()->json([
                'has_new' => true,
                'type' => 'multi_in_thread',
                'count' => $count,
                'thread' => [
                    'id' => $thread->id,
                    'title' => $thread->title,
                    'url' => route('forum.threads.show', $thread->slug)
                ],
                'latest_user' => [
                    'id' => $latestReply->user->id,
                    'name' => $latestReply->user->name
                ],
                'since' => $sinceDate->toISOString()
            ]);
        } else {
            // Multiple replies across multiple threads
            return response()->json([
                'has_new' => true,
                'type' => 'multi_threads',
                'count' => $count,
                'thread' => null,
                'latest_user' => [
                    'id' => $latestReply->user->id,
                    'name' => $latestReply->user->name
                ],
                'since' => $sinceDate->toISOString()
            ]);
        }
    }
}
