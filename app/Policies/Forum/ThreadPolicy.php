<?php

namespace App\Policies\Forum;

use App\Models\User;
use App\Models\Forum\Thread;

class ThreadPolicy
{
    public function create(User $user)
    {
        return (bool) $user;
    }
    
    public function update(User $user, Thread $thread)
    {
        return $user->id === $thread->user_id;
    }
    
    public function delete(User $user, Thread $thread)
    {
        return $user->id === $thread->user_id;
    }
}
