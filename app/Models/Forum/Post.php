<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    
    protected $table = 'forum_posts';
    
    protected $fillable = ['thread_id', 'user_id', 'body', 'parent_id'];
    
    protected $casts = [
        'edited_at' => 'datetime',
    ];
    
    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }
    
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    
    public function likes()
    {
        return $this->morphMany(\App\Models\Forum\Like::class, 'likeable');
    }
    
    public function likedBy(?int $userId): bool
    {
        return $userId ? $this->likes()->where('user_id', $userId)->exists() : false;
    }
    
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at');
    }
    
    public function isEdited()
    {
        return !is_null($this->edited_at);
    }
}
