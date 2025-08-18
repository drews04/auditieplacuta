<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserStatsPersonal;
use App\Models\Song;

class UserProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Personal stats (from v_user_personal_stats)
        $stats = UserStatsPersonal::find($user->id);

        // Today’s active song (optional)
        $activeSong = Song::query()
            ->where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->latest('created_at')
            ->first();

        // Active abilities (placeholder cooldown logic)
        $activeAbilities = $user->abilities()
            ->wherePivot('cooldown_ends_at', '<=', now())
            ->get()
            ->map(function ($ability) {
                $ability->cooldown_remaining = 0;
                return $ability;
            });

        // NEW: Points totals from ledger view
        $totals = DB::table('v_user_points_totals')
            ->where('user_id', $user->id)
            ->first();

        $allTimePoints = (int) ($totals->all_time_points ?? 0);
        $yearPoints    = (int) ($totals->year_points ?? 0);

        return view('user.user_profile', [
            'user'            => $user,
            'stats'           => $stats,
            'activeSong'      => $activeSong,
            'activeAbilities' => $activeAbilities,
            'allTimePoints'   => $allTimePoints,
            'yearPoints'      => $yearPoints,
        ]);
    }
}
