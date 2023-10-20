<?php

declare(strict_types=1);

namespace Domain\Order\Events;

use Illuminate\Queue\SerializesModels;

class AdminOrderFailedNotificationEvent
{
    use SerializesModels;

    public string $body;

    public string $permission;

    public function __construct(
        string $body,
        string $permission
    ) {
        $this->body = $body;
        $this->permission = $permission;
    }
}
