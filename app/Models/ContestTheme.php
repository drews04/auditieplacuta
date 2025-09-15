<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestTheme extends Model
{
    protected $table = 'contest_themes';

    protected $fillable = [
        'name',
        'category',
        'active',
        'contest_date',
        'chosen_by_user_id',
        'theme_pool_id',
    ];

    protected $casts = [
        'active'       => 'boolean',
        'contest_date' => 'date',
    ];

    /* =========================
       Relationships
       ========================= */

    /** Polymorphic likes */
    public function likes(): MorphMany
    {
        return $this->morphMany(ThemeLike::class, 'likeable');
    }

    /** Inverse: who picked it (if winner-picked) */
    public function chooser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chosen_by_user_id');
    }

    /** Optional: linked pool entry this theme was sourced from */
    public function pool(): BelongsTo
    {
        return $this->belongsTo(ThemePool::class, 'theme_pool_id');
    }

    /**
     * Correct relation to cycles:
     * contest_cycles has FK: contest_theme_id → contest_themes.id
     */
    public function cycles(): HasMany
    {
        return $this->hasMany(ContestCycle::class, 'contest_theme_id');
    }

    /* =========================
       Scopes / Helpers
       ========================= */

    public function scopeWithLikesCount($q)
    {
        return $q->withCount('likes');
    }

    public function scopeActive($q, bool $only = true)
    {
        return $only ? $q->where('active', true) : $q;
    }

    public function scopeForDate($q, $date)
    {
        return $q->whereDate('contest_date', \Illuminate\Support\Carbon::parse($date)->toDateString());
    }

    public function likedByUserId(?int $userId): bool
    {
        if (!$userId) return false;
        return $this->likes()->where('user_id', $userId)->exists();
    }
}
