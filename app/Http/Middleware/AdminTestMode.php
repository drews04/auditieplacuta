<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminTestMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if admin is in test mode
        if (auth()->check() && auth()->user()->is_admin && session('ap_test_mode')) {
            // Set test mode config for this request
            config(['ap.test_mode' => true]);
            
            // Add test mode info to request for easy access
            $request->merge(['ap_test_mode' => true]);
        }
        
        return $next($request);
    }
}
