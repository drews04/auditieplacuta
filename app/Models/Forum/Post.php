<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    
    protected $table = 'forum_posts';
    
    protected $fillable = ['thread_id', 'user_id', 'body'];
    
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
    
    public function isEdited()
    {
        return !is_null($this->edited_at);
    }
}
