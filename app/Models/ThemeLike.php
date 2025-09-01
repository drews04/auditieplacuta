<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ThemeLike extends Model
{
    protected $table = 'theme_likes';

    // IMPORTANT: the table does not have created_at / updated_at
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'likeable_type',
        'likeable_id',
    ];

    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
