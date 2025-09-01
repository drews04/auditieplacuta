<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ContestCycle extends Model
{
    protected $fillable = [
        'start_at',
        'submit_end_at',
        'vote_start_at',
        'vote_end_at',
        'theme_text',
        'winner_song_id',
        'winner_user_id',
        'winner_decided_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'submit_end_at' => 'datetime',
        'vote_start_at' => 'datetime',
        'vote_end_at' => 'datetime',
        'winner_decided_at' => 'datetime',
    ];

    /* ---------------- Relations ---------------- */

    public function songs()
    {
        return $this->hasMany(Song::class, 'cycle_id');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, 'cycle_id');
    }

    public function winnerSong()
    {
        return $this->belongsTo(Song::class, 'winner_song_id');
    }

    public function winnerUser()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    /* ---------------- Scopes ---------------- */

    public function scopeSubmissionOpen($query)
    {
        $now = Carbon::now();
        return $query->where('start_at', '<=', $now)
                    ->where('submit_end_at', '>', $now);
    }

    public function scopeVotingOpen($query)
    {
        $now = Carbon::now();
        return $query->where('vote_start_at', '<=', $now)
                    ->where('vote_end_at', '>', $now);
    }

    /* ---------------- Helpers ---------------- */

    public function isSubmissionOpen(): bool
    {
        $now = Carbon::now();
        return $now->between($this->start_at, $this->submit_end_at);
    }

    public function isVotingOpen(): bool
    {
        $now = Carbon::now();
        return $now->between($this->vote_start_at, $this->vote_end_at);
    }

    public function getSubmissionTimeLeftAttribute(): string
    {
        $now = Carbon::now();
        if ($now->gte($this->submit_end_at)) {
            return 'Închis';
        }
        
        $diff = $now->diff($this->submit_end_at);
        if ($diff->h > 0) {
            return "{$diff->h}h {$diff->i}m";
        }
        return "{$diff->i}m";
    }

    public function getVotingTimeLeftAttribute(): string
    {
        $now = Carbon::now();
        if ($now->gte($this->vote_end_at)) {
            return 'Închis';
        }
        
        $diff = $now->diff($this->vote_end_at);
        if ($diff->h > 0) {
            return "{$diff->h}h {$diff->i}m";
        }
        return "{$diff->i}m";
    }
}
