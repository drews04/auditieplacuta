<?php

namespace App\Http\Middleware;

use Closure;

class MarkForumSeen
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $user = $request->user();
        if (!$user) return $response;

        // ✅ Only mark as seen on GET requests to /forum or /forum/*
        $path = trim($request->path(), '/');
        $isForumPage = $request->isMethod('GET') && ($path === 'forum' || str_starts_with($path, 'forum/'));

        if ($isForumPage) {
            $user->forceFill(['forum_seen_at' => now()])->save();
        }

        return $response;
    }
}
