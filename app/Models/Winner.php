<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Winner extends Model
{
    protected $table = 'winners';
    public $timestamps = true;

    // allow mass assignment for all fields (safe for our admin-only writes)
    protected $guarded = [];

    protected $casts = [
        'contest_date' => 'date',
        'was_tie'      => 'boolean',
        'theme_chosen' => 'boolean',
    ];
}