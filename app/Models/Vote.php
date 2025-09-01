<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        'user_id',
        'song_id',
        'vote_date',
        'tiebreak_id', // 👈 allow 1 vote per tiebreak window
        'cycle_id',    // FK → contest_cycles.id
    ];

    protected $casts = [
        'vote_date' => 'date',
    ];

    // (optional) handy relations
    public function song()     { return $this->belongsTo(Song::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function tiebreak() { return $this->belongsTo(Tiebreak::class); }
    public function cycle()    { return $this->belongsTo(ContestCycle::class); }
}
