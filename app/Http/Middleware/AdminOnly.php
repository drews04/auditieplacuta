<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if logged in and admin
        if ($user && ($user->is_admin ?? false)) {
            return $next($request);
        }

        // TEMP: allow user with ID = 1 for testing
        if ($user && (int)$user->id === 1) {
            return $next($request);
        }

        abort(403, 'Admins only.');
    }
}
