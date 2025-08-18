<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserWinsController extends Controller
{
    public function index(Request $request, ?int $userId = null)
    {
        $userId = $userId ?: Auth::id();

        // Paginated wins: Song Title, Win Date, Theme Title
        $wins = DB::table('winners as w')
            ->join('songs as s', 's.id', '=', 'w.song_id')
            // primary path: songs.theme_id -> competition_themes.id
            ->leftJoin('competition_themes as ct', 'ct.id', '=', 's.theme_id')
            // fallback path: theme by contest date if theme_id is null
            ->leftJoin('competition_themes as ctf', function ($join) {
                $join->on('ctf.applies_on', '=', 'w.contest_date');
            })
            ->where('w.user_id', $userId)
            ->orderByDesc(DB::raw('COALESCE(w.contest_date, w.created_at)'))
            ->select([
                'w.id',
                DB::raw('DATE(COALESCE(w.contest_date, w.created_at)) as won_on'),
                's.title as song_title',
                DB::raw('COALESCE(ct.title, ctf.title, "") as theme_title'),
            ])
            ->paginate(15)
            ->withQueryString();

        // also pull the viewer user for header
        $user = DB::table('users')->where('id', $userId)->first();

        return view('user.wins', compact('wins','user'));
    }
}
