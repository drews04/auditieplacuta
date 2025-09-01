<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Winner extends Model
{
    protected $table = 'winners';
    public $timestamps = true;

    // System/admin writes only; keep open for mass assignment
    protected $guarded = [];

    protected $casts = [
        'contest_date' => 'date',
        'win_date'     => 'date',
        'was_tie'      => 'boolean',
        'theme_chosen' => 'boolean',
    ];

    protected $fillable = [
        'cycle_id',
        'user_id',
        'song_id',
        'vote_count',
        'was_tie',
        'theme_chosen',
        'contest_date',
        'win_date',
        'competition_theme_id',
    ];

    // Eager-load so Blade can do $todayWinner->song->title and ->user->name safely
    protected $with = ['song', 'user', 'theme'];

    /* ---------------- Relations ---------------- */

    public function song()
    {
        return $this->belongsTo(Song::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Theme assigned for that contest day.
     * FK column on winners: competition_theme_id → contest_themes.id
     */
    public function theme()
    {
        return $this->belongsTo(ContestTheme::class, 'competition_theme_id');
    }

    public function cycle()
    {
        return $this->belongsTo(ContestCycle::class, 'cycle_id');
    }
}
