<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ability extends Model
{
    protected $fillable = ['name', 'description', 'cooldown'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_abilities')
            ->withPivot('used_at', 'cooldown_ends_at')
            ->withTimestamps();
    }
}
