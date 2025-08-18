<?php

namespace App\Http\Controllers;

use App\Models\UserWin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserWinsController extends Controller
{
    /**
     * Paginated list of a user's wins (song, theme, date).
     * If $userId is null, use the authenticated user.
     */
    public function index(Request $request, ?int $userId = null)
    {
        $uid = $userId ?? Auth::id();

        $wins = UserWin::query()
            ->select(['id as win_id','user_id','song_id','competition_theme_id','won_on','created_at'])
            ->forUser($uid)
            ->with([
                'song:id,title,youtube_url',
                // if you used hasOneThrough Theme on the model:
                'theme:id,name',
                // if you prefer via CompetitionTheme instead, comment the above and use:
                // 'competitionTheme.theme:id,name',
            ])
            ->newestFirst()
            ->paginate(20)
            ->withQueryString();

        $totalWins = $wins->total();

        return view('users.wins', [
            'wins'      => $wins,
            'totalWins' => $totalWins,
            'userId'    => $uid,
        ]);
    }
}
