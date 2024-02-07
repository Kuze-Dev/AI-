<?php

declare(strict_types=1);

namespace Domain\Order\Events;

use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Illuminate\Queue\SerializesModels;

class AdminOrderStatusUpdatedEvent
{
    use SerializesModels;

    public ?Customer $customer;

    public Order $order;

    public bool $shouldSendEmail;

    public string $status;

    public ?string $emailRemarks;

    public function __construct(
        Order $order,
        bool $shouldSendEmail,
        string $status,
        ?string $emailRemarks,
        ?Customer $customer = null
    ) {
        $this->order = $order;
        $this->shouldSendEmail = $shouldSendEmail;
        $this->status = $status;
        $this->emailRemarks = $emailRemarks;
        $this->customer = $customer;
    }
}
