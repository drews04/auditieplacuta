<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = [
        'user_id',
        'youtube_url',
        'title',
        'competition_date',
        'votes',
        'is_winner',
        'theme_id'
    ];

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function theme()
    {
        return $this->belongsTo(CompetitionTheme::class, 'theme_id');
    }
}