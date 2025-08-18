<?php

namespace App\Http\Controllers;

use App\Models\UserStatsWeekly;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $yw = now()->format('oW');

        $top3Weekly = UserStatsWeekly::query()
            ->join('users as u', 'u.id', '=', 'v_user_stats_weekly.user_id')
            ->where('v_user_stats_weekly.yw', $yw)
            ->select(
                'u.id as user_id',
                'u.name',
                'v_user_stats_weekly.participations',
                'v_user_stats_weekly.wins',
                'v_user_stats_weekly.votes_received',
                'v_user_stats_weekly.votes_given',
                DB::raw("(v_user_stats_weekly.wins * 3) 
                       + (v_user_stats_weekly.votes_received * 1) 
                       + (v_user_stats_weekly.participations * 0.5) as points")
            )
            ->orderByDesc('points')
            ->limit(3)
            ->get();

            return view('home', compact('top3Weekly'));
    }
}
