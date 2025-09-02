<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Only allow authenticated admins
        if (!$user || !(bool) ($user->is_admin ?? 0)) {
            abort(403, 'ADMIN ACCESS REQUIRED');
        }

        return $next($request);
    }
}
