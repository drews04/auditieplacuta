<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $table = 'forum_likes';

    protected $fillable = ['user_id'];

    public function likeable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
