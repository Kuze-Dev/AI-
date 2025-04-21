<?php

declare(strict_types=1);

namespace Domain\Notification\Actions;

use Domain\Notification\Events\NotificationRead;
use Domain\Notification\Exceptions\CantReadNotificationException;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\DatabaseNotification;

class MarkAsReadNotificationAction
{
    public function execute(User $user, DatabaseNotification $databaseNotification): void
    {
        if (! $databaseNotification->notifiable()->is($user)) {
            throw new CantReadNotificationException;
        }

        $databaseNotification->markAsRead();

        event(new NotificationRead);
    }

    public function markAllAsRead(User $user): void
    {
        if (isset($user->unreadNotifications)) {
            $user->unreadNotifications->markAsRead();
        }
    }
}
