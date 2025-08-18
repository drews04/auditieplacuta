<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tiebreak extends Model
{
    protected $table = 'tiebreaks';

    protected $fillable = [
        'contest_date',   // date (Y-m-d) of the contest
        'starts_at',      // datetime
        'ends_at',        // datetime
        'song_ids',       // json array of song IDs
        'resolved',       // bool
    ];

    protected $casts = [
        'contest_date' => 'date',
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'song_ids'     => 'array',
        'resolved'     => 'boolean',
    ];

    // Optional relations
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
