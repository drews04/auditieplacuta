<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPoint extends Model
{
    protected $table = 'user_points';

    protected $fillable = [
        'user_id',
        'points',
        'contest_date',   // DATE of the contest (used for YTD vs All-time)
        'song_id',        // nullable
        'reason',         // e.g. 'position', 'participation', 'bonus'
        'meta',           // json: { "position": 1, "votes": 42 }
    ];

    protected $casts = [
        'contest_date' => 'date',
        'meta' => 'array',
    ];
}
