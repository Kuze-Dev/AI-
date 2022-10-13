<?php

declare(strict_types=1);

namespace Domain\Notification\Actions;

use App\Enums\Api\NotificationType;
use App\Notifications\Api\GeneralApiNotification;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\RoutesNotifications;

class SendNotificationAction
{
    public function execute(User $user, string $message, NotificationType $type = NotificationType::INFORMATION): void
    {
        if ( ! in_array(RoutesNotifications::class, class_uses_recursive($user))) {
            abort(500, 'Class must uses '.RoutesNotifications::class.' trait.');
        }
        /** @var  User|RoutesNotifications $user */

        /** @phpstan-ignore-next-line  */
        $user->notify(new GeneralApiNotification($message, $type));
    }
}
