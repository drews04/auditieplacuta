<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thread extends Model
{
    use SoftDeletes;
    
    protected $table = 'forum_threads';
    
    protected $fillable = [
        'category_id', 'user_id', 'title', 'slug', 'body', 
        'pinned', 'locked', 'is_hidden'
    ];
    
    public function getRouteKeyName()
    {
        return 'slug';
    }
    
    protected $casts = [
        'pinned' => 'boolean',
        'locked' => 'boolean',
        'is_hidden' => 'boolean',
        'last_posted_at' => 'datetime',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    
    public function posts()
    {
        return $this->hasMany(Post::class)->orderBy('created_at');
    }
    
    public function lastPostUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'last_post_user_id');
    }
    
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }
    
    public function scopePinned($query)
    {
        return $query->where('pinned', true);
    }
    
    public function scopeUnlocked($query)
    {
        return $query->where('locked', false);
    }
}
