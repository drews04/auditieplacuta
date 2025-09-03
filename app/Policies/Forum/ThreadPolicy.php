<?php

namespace App\Policies\Forum;

use App\Models\User;
use App\Models\Forum\Thread;
use Illuminate\Support\Carbon;

class ThreadPolicy
{
    protected int $editWindowHours = 24;

    public function viewAny(?User $user): bool { return true; }
    public function view(?User $user, Thread $thread): bool { return true; }
    public function create(User $user): bool { return true; }

    public function update(User $user, Thread $thread): bool
    {
        if ($user->is_admin ?? false) return true;
        return $user->id === $thread->user_id
            && $thread->created_at >= Carbon::now()->subHours($this->editWindowHours);
    }

    public function delete(User $user, Thread $thread): bool
    {
        if ($user->is_admin ?? false) return true;
        return $user->id === $thread->user_id
            && $thread->created_at >= Carbon::now()->subHours($this->editWindowHours);
    }

    public function restore(User $user, Thread $thread): bool
    { return ($user->is_admin ?? false); }

    public function forceDelete(User $user, Thread $thread): bool
    { return ($user->is_admin ?? false); }
}
