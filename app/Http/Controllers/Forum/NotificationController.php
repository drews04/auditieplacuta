<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forum\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    /**
     * GET /forum/alerts/unread-count
     *
     * Rules (simple):
     * - Show pill if there are notifications AND last pill was >= 30 minutes ago (or never shown).
     * - Visiting any /forum* page stamps users.forum_seen_at (middleware) so ONLY new items after that are counted.
     * - Logging out/in does NOT affect cooldown (we removed the login listener).
     *
     * What counts as a "notification":
     * - New replies in threads I own
     * - Replies to my comments
     *
     * Not included:
     * - Generic "threads I participated in" (kept simple on purpose)
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Lower bound for "new"
        $sinceDate = $user->forum_seen_at
            ? Carbon::parse($user->forum_seen_at)
            : Carbon::now()->subDays(30);

        // 30-minute cooldown
        $cooldownOk = !$user->forum_pill_last_shown_at
            || Carbon::parse($user->forum_pill_last_shown_at)->lte(Carbon::now()->subMinutes(30));

        // Find posts that target me (threads I own OR replies to my comments), created after "seen"
        $posts = Post::with('thread:id,slug,title')
            ->where('user_id', '!=', $user->id)
            ->where('created_at', '>', $sinceDate)
            ->where(function ($q) use ($user) {
                $q->whereHas('thread', fn($t) => $t->where('user_id', $user->id))   // replies in threads I own
                  ->orWhereHas('parent', fn($p) => $p->where('user_id', $user->id)); // replies to my comments
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $actualCount   = $posts->count();
        $throttled     = ($actualCount > 0 && !$cooldownOk);
        $countToReturn = $throttled ? 0 : $actualCount;

        // Small thread preview list (up to 3)
        $uniqueThreads = $posts->pluck('thread')->filter()->unique('id')->values();
        $threadsMini   = $uniqueThreads->take(3)->map(function ($t) {
            return [
                'id'    => $t->id,
                'title' => $t->title,
                'url'   => route('forum.threads.show', $t->slug),
            ];
        })->values();

        return response()
            ->json([
                'has_new'       => $countToReturn > 0,
                'count'         => $countToReturn,
                'threads_count' => $uniqueThreads->count(),
                'threads'       => $threadsMini,
                'since'         => $sinceDate->toIso8601String(),
                'throttled'     => $throttled,
                'cooldown_min'  => 30,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * GET /forum/alerts/unread-detail
     * Debug aid: same filter as unreadCount, but returns grouped details.
     */
    public function unreadDetail(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $sinceDate = $user->forum_seen_at
            ? Carbon::parse($user->forum_seen_at)
            : Carbon::now()->subDays(30);

        $posts = Post::query()
            ->with(['thread:id,slug,title', 'parent:id,user_id', 'user:id,name'])
            ->where('user_id', '!=', $user->id)
            ->where('created_at', '>', $sinceDate)
            ->where(function ($q) use ($user) {
                $q->whereHas('thread', fn($t) => $t->where('user_id', $user->id))   // replies in threads I own
                  ->orWhereHas('parent', fn($p) => $p->where('user_id', $user->id)); // replies to my comments
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $total = $posts->count();

        $threadGroups = $posts
            ->filter(fn($p) => $p->thread)
            ->groupBy('thread_id')
            ->map(function ($group) {
                $t = $group->first()->thread;
                return [
                    'thread_id' => $t->id,
                    'title'     => $t->title,
                    'url'       => route('forum.threads.show', $t->slug),
                    'count'     => $group->count(),
                ];
            })
            ->values()
            ->sortByDesc('count')
            ->values();

        $commentReplies = $posts
            ->filter(fn($p) => $p->parent && (int)$p->parent->user_id === (int)$user->id)
            ->take(5)
            ->map(function ($p) {
                $body = trim(strip_tags($p->body ?? ''));
                if (mb_strlen($body) > 120) $body = mb_substr($body, 0, 120) . '…';
                return [
                    'by_user'      => ['id' => $p->user->id, 'name' => $p->user->name],
                    'excerpt'      => $body,
                    'thread_title' => $p->thread?->title,
                    'url'          => $p->thread ? route('forum.threads.show', $p->thread->slug) . '#post-' . $p->id : url('/forum'),
                    'created_at'   => optional($p->created_at)->toIso8601String(),
                ];
            })
            ->values();

        return response()
            ->json([
                'has_new'         => $total > 0,
                'count'           => $total,
                'threads_count'   => $threadGroups->count(),
                'threads'         => $threadGroups->take(3)->values(),
                'by_threads'      => $threadGroups,
                'comment_replies' => $commentReplies,
                'since'           => $sinceDate->toIso8601String(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * POST /forum/alerts/ack-shown
     * Marks the pill as shown now (starts the 30-minute cooldown).
     */
    public function ackShown(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->forceFill([
            'forum_pill_last_shown_at' => Carbon::now(),
        ])->save();

        return response()->json(['ok' => true, 'at' => Carbon::now()->toIso8601String()]);
    }
}
