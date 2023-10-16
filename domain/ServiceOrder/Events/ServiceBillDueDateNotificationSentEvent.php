<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Events;

use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceBillDueDateNotificationSentEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public ServiceBill $serviceBill
    ) {
        $this->serviceBill = $serviceBill;
    }
}
