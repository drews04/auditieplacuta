<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
