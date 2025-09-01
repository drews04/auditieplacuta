<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ThemePool extends Model
{
    protected $table = 'theme_pools';

    protected $fillable = [
        'category',   // 'csd' | 'it' | 'artisti' | 'genuri'
        'name',
        'active',
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
}
