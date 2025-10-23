<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Events\Event;

class EventPolicy
{
    public function create(User $user)
    {
        return (bool) $user; // Any authenticated user can create events
    }

    public function update(User $user, Event $event)
    {
        return $user->id === $event->user_id;
    }

    public function delete(User $user, Event $event)
    {
        return $user->id === $event->user_id;
    }
}
