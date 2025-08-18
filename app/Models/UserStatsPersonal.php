<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStatsPersonal extends Model
{
    protected $table = 'v_user_personal_stats'; // DB view name
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'participations' => 'int',
        'wins'           => 'int',
        'votes_made'     => 'int',
        'votes_received' => 'int',
        'last_win_date'  => 'date',
    ];
}