<?php

namespace App\Contracts;

use App\Models\User;
use App\Models\Notification;

interface NotificationChannel
{
    public function send(User $user, Notification $notification): void;
}
