<?php

namespace App\Policies\Forum;

use App\Models\User;
use App\Models\Forum\Post;
use Illuminate\Support\Carbon;

class PostPolicy
{
    protected int $editWindowHours = 24;

    public function viewAny(?User $user): bool { return true; }
    public function view(?User $user, Post $post): bool { return true; }
    public function create(User $user): bool { return true; }

    public function update(User $user, Post $post): bool
    {
        if ($user->is_admin ?? false) return true;

        return $user->id === $post->user_id
            && $post->created_at >= Carbon::now()->subHours($this->editWindowHours);
    }

    public function delete(User $user, Post $post): bool
    {
        if ($user->is_admin ?? false) return true;

        return $user->id === $post->user_id
            && $post->created_at >= Carbon::now()->subHours($this->editWindowHours);
    }

    public function restore(User $user, Post $post): bool
    { return ($user->is_admin ?? false); }

    public function forceDelete(User $user, Post $post): bool
    { return ($user->is_admin ?? false); }
}
