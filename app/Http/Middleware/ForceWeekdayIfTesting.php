<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ForceWeekdayIfTesting
{
    /**
     * If session('ap_force_weekday') === true, pretend it's a weekday:
     * we set Carbon::setTestNow() to a weekday date at 12:00 so all "today/now"
     * in this request act like a weekday.
     */
    public function handle(Request $request, Closure $next)
    {
        if (session()->get('ap_force_weekday') === true) {
            $now = Carbon::now();
            if ($now->isWeekend()) {
                $fake = $now->copy()->next('Monday')->setTime(12, 0, 0);
            } else {
                $fake = $now->copy()->setTime(12, 0, 0);
            }
            Carbon::setTestNow($fake);
        } else {
            Carbon::setTestNow(null);
        }

        return $next($request);
    }
}
