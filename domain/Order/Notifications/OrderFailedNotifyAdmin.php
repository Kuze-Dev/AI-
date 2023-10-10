<?php

declare(strict_types=1);

namespace Domain\Order\Notifications;

use Domain\Order\Events\AdminOrderFailedNotificationEvent;

class OrderFailedNotifyAdmin
{
    public function execute(string $body, string $permission): void
    {
        event(new AdminOrderFailedNotificationEvent(
            $body,
            $permission
        ));
    }
}
