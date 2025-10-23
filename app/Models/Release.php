<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Release extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','slug','release_date','week_key','type','cover_path','description','is_highlight'
    ];

    protected $casts = [
        'release_date' => 'date',
        'is_highlight' => 'boolean',
    ];

    public function artists()
    {
        return $this->belongsToMany(Artist::class);
    }

    public function categories()
    {
        return $this->belongsToMany(ReleaseCategory::class, 'category_release');
    }

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->slug)) $m->slug = Str::slug($m->title.'-'.now()->format('His'));
            if (empty($m->week_key) && $m->release_date) {
                $m->week_key = $m->release_date->format('o\WW'); // e.g. 2025W39
            }
        });
    }
}
