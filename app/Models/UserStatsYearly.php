<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStatsYearly extends Model
{
    protected $table = 'v_user_stats_yearly'; // points to the SQL view
    public $timestamps = false;               // no timestamps in view
    protected $primaryKey = null;             // no single PK
    public $incrementing = false;             // PK is not auto-increment

    protected $fillable = [
        'user_id',
        'y',
        'participations',
        'wins',
        'votes_received',
        'votes_given',
    ];
}
