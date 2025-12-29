<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    public function view(User $user, Notification $notification)
    {
        return $user->id === $notification->notifiable_id
            && $notification->notifiable_type === User::class;
    }

    public function update(User $user, Notification $notification)
    {
        return $user->id === $notification->notifiable_id
            && $notification->notifiable_type === User::class;
    }

    public function delete(User $user, Notification $notification)
    {
        return $user->id === $notification->notifiable_id
            && $notification->notifiable_type === User::class;
    }
}
