<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceBill;

class ExpiredServiceOrderAction
{
    public function __construct(
    ) {
    }

    public function execute(
        ServiceBill $serviceBill,
    ): ServiceBill {

        $serviceBill->service_order->update([
            'status' => ServiceOrderStatus::INACTIVE,
        ]);

        return $serviceBill;
    }
}
