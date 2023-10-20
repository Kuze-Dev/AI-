<?php

declare(strict_types=1);

namespace Domain\Tier\Listeners;

use Domain\Tier\Events\TierRequestRejectedEvent;

class TierRequestRejectedListener
{
    public function handle(TierRequestRejectedEvent $event): void
    {
        $customer = $event->customer;

        $customer->forceDelete();

        redirect('admin/customers');
    }
}
