<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Events;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Queue\SerializesModels;

class AdminServiceOrderStatusUpdatedEvent
{
    use SerializesModels;

    public function __construct(
        public Customer $customer,
        public ServiceOrder $serviceOrder,
        public bool $shouldNotifyCustomer
    ) {
    }
}
