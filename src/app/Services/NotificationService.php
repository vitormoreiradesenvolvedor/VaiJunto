<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use App\Contracts\NotificationChannel;

class NotificationService
{
    /** @param NotificationChannel[] $channels */
    public function __construct(private array $channels) {}

    public function notify(User $user, string $type, array $payload): void
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'payload' => $payload,
        ]);

        foreach ($this->channels as $channel) {
            $channel->send($user, $notification);
        }
    }
}
