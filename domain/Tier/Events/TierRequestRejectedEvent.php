<?php

declare(strict_types=1);

namespace Domain\Tier\Events;

use Domain\Customer\Models\Customer;
use Illuminate\Queue\SerializesModels;

class TierRequestRejectedEvent
{
    use SerializesModels;

    public Customer $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }
}
