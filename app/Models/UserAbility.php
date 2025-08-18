<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAbility extends Model
{
    use HasFactory;

    protected $table = 'user_abilities';

    protected $fillable = [
        'user_id',
        'ability_id',
        'used_at',
        'cooldown_ends_at',
    ];

    protected $dates = [
        'used_at',
        'cooldown_ends_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ability()
    {
        return $this->belongsTo(Ability::class);
    }
}