<?php

declare(strict_types=1);

namespace Domain\Notification\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationRead
{
    use Dispatchable;
    use SerializesModels;
}
