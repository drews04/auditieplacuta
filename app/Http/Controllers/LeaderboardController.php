<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Homepage partial (Top 3 from yearly points).
     */
    public function home()
    {
        $yearlyTop3 = DB::table('v_user_points_totals as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->select('u.id','u.name','t.year_points','t.all_time_points')
            ->orderByDesc('t.year_points')
            ->orderBy('u.name')
            ->limit(3)
            ->get();

        return view('partials.home_leaderboards', compact('yearlyTop3'));
    }

    /**
     * Full leaderboard page
     * Scope = positions|alltime|yearly|monthly
     */
    public function index(Request $request)
    {
        $scope = $request->get('scope', 'alltime'); 
        $perPage = 20;
        $page_start = (max((int)$request->get('page', 1), 1) - 1) * $perPage;

        if ($scope === 'positions') {
            $rows = DB::table('v_user_points_totals as t')
                ->join('users as u', 'u.id', '=', 't.user_id')
                ->select('u.id as user_id','u.name','t.all_time_points as points')
                ->orderByDesc('points')
                ->orderBy('u.name')
                ->paginate($perPage);

            $ym = null; $y = null;
            return view('leaderboards', compact('scope','ym','y','rows','page_start'));
        }

        if ($scope === 'yearly') {
            $y = (string) $request->get('y', now()->format('Y'));

            $rows = DB::table('v_user_points_totals as t')
                ->join('users as u', 'u.id', '=', 't.user_id')
                ->select('u.id as user_id','u.name','t.year_points as points')
                ->orderByDesc('points')
                ->orderBy('u.name')
                ->paginate($perPage);

            $ym = null;
            return view('leaderboards', compact('scope','y','ym','rows','page_start'));
        }

        if ($scope === 'monthly') {
            $ym = (string) $request->get('ym', now()->format('Y-m'));
        
            $rows = DB::table('v_user_points_monthly as m')
                ->join('users as u', 'u.id', '=', 'm.user_id')
                ->where('m.ym', $ym)
                ->select(
                    'm.user_id',
                    'u.name',
                    DB::raw('m.points + 0 as points') // cast to numeric for sorting/display
                )
                ->orderByDesc(DB::raw('m.points + 0'))
                ->orderBy('u.name')
                ->paginate($perPage);
        
            $y = null;
            return view('leaderboards', compact('scope','ym','y','rows','page_start'));
        }
        
        // ALL-TIME (default)
        $scope = 'alltime';

        $rows = DB::table('v_user_points_totals as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->select('u.id as user_id','u.name','t.all_time_points as points')
            ->orderByDesc('points')
            ->orderBy('u.name')
            ->paginate($perPage);

        $ym = null; $y = null;
        return view('leaderboards', compact('scope','ym','y','rows','page_start'));
    }
}
