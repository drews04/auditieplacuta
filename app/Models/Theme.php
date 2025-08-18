<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'competition_date',
        'title',
        'chosen_by_user_id',
    ];
}