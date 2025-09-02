<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = ['title', 'slug', 'event_date', 'poster_path', 'body', 'user_id'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->slug = $model->slug ?: Str::slug(Str::limit($model->title, 60)) . '-' . Str::random(6);
        });
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
