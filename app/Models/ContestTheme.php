<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestTheme extends Model
{
    protected $fillable = [
        'contest_date',
        'theme_pool_id',
        'picked_by_winner',
    ];

    protected $casts = [
        'contest_date'     => 'date',
        'picked_by_winner' => 'boolean',
    ];

    public function pool()
    {
        return $this->belongsTo(ThemePool::class, 'theme_pool_id');
    }
}
