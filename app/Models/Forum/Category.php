<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'forum_categories';
    
    protected $fillable = ['name', 'slug', 'description'];
    
    public function getRouteKeyName()
    {
        return 'slug';
    }
    
    public function threads()
    {
        return $this->hasMany(Thread::class);
    }
    
    public function getThreadsCountAttribute($value)
    {
        return $value ?? $this->threads()->count();
    }
}
