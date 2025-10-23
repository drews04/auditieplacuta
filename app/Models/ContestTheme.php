<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'active' => 'boolean',
    ];

    /* Likes (polymorphic) */
    public function likes(): MorphMany
    {
        return $this->morphMany(\App\Models\ThemeLike::class, 'likeable');
    }

    /* Helpers */
    public function scopeWithLikesCount($q)
    {
        return $q->withCount('likes');
    }

    public function likedByUserId(?int $userId): bool
    {
        if (!$userId) return false;
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /* Relations */
    public function cycle()
    {
        return $this->belongsTo(ContestCycle::class, 'contest_cycle_id');
    }
}
