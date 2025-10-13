<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MarkForumSeenAt
{
    public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);

    $user = $request->user();
    if ($user && $request->is('forum*')) {
        // Hard reset: mark as seen *and* start cooldown now
        $now = now();
        $user->forceFill([
            'forum_seen_at'            => $now, // future checks only count posts after this
            'forum_pill_last_shown_at' => $now, // hide pill for the next 30 min
        ])->saveQuietly();
    }

    return $response;
}

}
