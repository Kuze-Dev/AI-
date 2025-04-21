<?php

declare(strict_types=1);

namespace Domain\Order\Events;

use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Illuminate\Queue\SerializesModels;

class AdminOrderStatusUpdatedEvent
{
    use SerializesModels;

    public function __construct(public Order $order, public bool $shouldSendEmail, public string $status, public ?string $emailRemarks, public ?Customer $customer = null) {}
}
