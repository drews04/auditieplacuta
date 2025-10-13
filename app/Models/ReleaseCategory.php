<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ReleaseCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug'];

    public function releases()
    {
        return $this->belongsToMany(Release::class, 'category_release');
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
