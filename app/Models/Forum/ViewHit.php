<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;

class ViewHit extends Model
{
    protected $table = 'forum_views';
    
    protected $fillable = ['thread_id', 'user_id', 'session_id', 'ip'];
    
    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }
}
