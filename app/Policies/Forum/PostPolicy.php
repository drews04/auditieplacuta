<?php

namespace App\Policies\Forum;

use App\Models\User;
use App\Models\Forum\Post;

class PostPolicy
{
    public function create(User $user)
    {
        return (bool) $user;
    }
    
    public function update(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }
    
    public function delete(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }
}
