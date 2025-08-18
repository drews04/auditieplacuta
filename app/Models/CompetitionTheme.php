<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionTheme extends Model
{
    protected $fillable = [
        'applies_on', 'category_code', 'title', 'chosen_by', 'chosen_at',
    ];

    protected $casts = [
        'applies_on' => 'date',
        'chosen_at'  => 'datetime',
    ];

    public function chooser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'chosen_by');
    }
}