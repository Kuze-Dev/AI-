<?php

namespace  Domain\ServiceOrder\Events;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
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
