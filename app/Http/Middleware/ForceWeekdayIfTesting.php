<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceWeekdayIfTesting
{
    public function handle(Request $request, Closure $next)
    {
        $restore = false;

        if ($request->session()->get('ap_force_weekday') === true) {
            // Fake Monday 10:00 for THIS request only
            $fake = \Carbon\Carbon::now()->next(\Carbon\Carbon::MONDAY)->setTime(10, 0, 0);
            \Carbon\Carbon::setTestNow($fake);
            \Illuminate\Support\Carbon::setTestNow($fake);
            $restore = true;
        }

        $response = $next($request);

        if ($restore) {
            \Carbon\Carbon::setTestNow(null);
            \Illuminate\Support\Carbon::setTestNow(null);
        }

        return $response;
    }
}
