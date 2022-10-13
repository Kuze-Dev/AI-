<?php

declare(strict_types=1);

namespace Domain\Notification\Actions;

use Domain\Notification\Exceptions\CantUnReadNotificationException;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\DatabaseNotification;

class MarkAsUnReadNotificationAction
{
    public function execute(User $user, DatabaseNotification $databaseNotification): void
    {
        if ( ! $databaseNotification->notifiable()->is($user)) {
            throw new CantUnReadNotificationException();
        }

        $databaseNotification->markAsUnread();
    }
}
