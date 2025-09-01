<?php

namespace App\Http\Controllers\Header\Clasamente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TemaLuniiController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int)($request->query('y') ?: now()->year);
        $month = (int)($request->query('m') ?: now()->month);

        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = $start->copy()->endOfMonth();

        $rows = DB::table('contest_themes as ct')
            ->leftJoin('users as u', 'u.id', '=', 'ct.chosen_by_user_id')
            ->leftJoin('theme_likes as tl', function ($j) {
                $j->on('tl.likeable_id', '=', 'ct.id')
                  ->where('tl.likeable_type', '=', \App\Models\ContestTheme::class);
            })
            ->selectRaw('
                ct.id,
                ct.name,
                ct.category,
                ct.contest_date,
                ct.created_at,
                ct.chosen_by_user_id,
                COALESCE(u.name, "—") as chooser_name,
                COUNT(tl.id) as likes_count
            ')
            ->whereBetween(DB::raw('COALESCE(ct.contest_date, DATE(ct.created_at))'),
                           [$start->toDateString(), $end->toDateString()])
            ->groupBy(
                'ct.id', 'ct.name', 'ct.category', 'ct.contest_date', 'ct.created_at',
                'ct.chosen_by_user_id', 'u.name'
            )
            ->orderByDesc('likes_count')
            ->orderByRaw('COALESCE(ct.contest_date, DATE(ct.created_at)) ASC') // older wins
            ->orderBy('ct.id', 'ASC') // stable tie-breaker
            ->limit(10)
            ->get();

        $top = $rows->first();

        return view('clasamente.tema-lunii', compact('year','month','rows','top'));
    }
}
