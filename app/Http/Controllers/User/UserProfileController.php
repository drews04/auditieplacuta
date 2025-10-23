<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        // Today's active song (optional)
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

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB max
        ]);

        $user = Auth::user();

        // Delete old photo if exists
        if ($user->profile_photo_url && file_exists(public_path($user->profile_photo_url))) {
            @unlink(public_path($user->profile_photo_url));
        }

        // Store new photo
        $file = $request->file('profile_photo');
        $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/profiles'), $filename);

        // Update user
        $user->profile_photo_url = '/uploads/profiles/' . $filename;
        $user->save();

        return redirect()->route('user.user_profile')->with('success', 'Poza de profil a fost actualizată cu succes!');
    }
}
