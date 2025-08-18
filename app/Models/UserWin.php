<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserWin extends Model
{
    // This model is ONLY for reading/paginating user wins on the profile.
    // It points to the existing 'winners' table but doesn't touch your competition logic.
    protected $table = 'winners';

    protected $fillable = [];        // read-only usage from app layer
    protected $guarded  = ['id'];

    protected $casts = [
        'won_on' => 'date',
    ];

    // --- Relationships (lightweight for listing) ---
    public function song()
    {
        return $this->belongsTo(Song::class);
    }

    public function competitionTheme()
    {
        return $this->belongsTo(CompetitionTheme::class);
    }

    // If you need the theme name directly
    public function theme()
    {
        return $this->hasOneThrough(
            Theme::class,
            CompetitionTheme::class,
            'id',                   // PK on competition_themes
            'id',                   // PK on themes
            'competition_theme_id', // FK on winners
            'theme_id'              // FK on competition_themes
        );
    }

    // --- Scopes for clean controller code ---
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNewestFirst($query)
    {
        return $query->orderByDesc(DB::raw('COALESCE(won_on, created_at)'));
    }
}
