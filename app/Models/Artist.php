<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug'];

    public function releases()
    {
        return $this->belongsToMany(Release::class);
    }

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->name);
            }
        });
    }
}
