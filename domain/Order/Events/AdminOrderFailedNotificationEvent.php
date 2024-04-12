<?php

declare(strict_types=1);

namespace Domain\Order\Events;

use Illuminate\Queue\SerializesModels;

class AdminOrderFailedNotificationEvent
{
    use SerializesModels;

    public function __construct(public string $body, public string $permission)
    {
    }
}
