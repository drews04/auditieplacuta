<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $table = 'songs';

    protected $fillable = [
        'user_id',
        'youtube_url',
        'title',
        'competition_date', // Y-m-d of the contest day this song belongs to
        'votes',
        'is_winner',
        'theme_id',         // FK → contest_themes.id
    ];

    protected $casts = [
        'competition_date' => 'date',
        'is_winner'        => 'boolean',
        'votes'            => 'integer',
    ];

    /* ---------------- Relations ---------------- */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function theme()
    {
        // Correct relation: Song.theme_id → ContestTheme.id
        return $this->belongsTo(ContestTheme::class, 'theme_id');
    }
}
