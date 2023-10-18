<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Spatie\QueueableAction\QueueableAction;

class ExpiredServiceOrderAction
{
    use QueueableAction;

    public function __construct(
        private ChangeServiceOrderStatusAction $changeServiceOrderStatusAction
    ) {
    }

    public function execute(
        ServiceBill $serviceBill,
    ): ServiceBill {

        $serviceBill->serviceOrder->update([
            'status' => ServiceOrderStatus::INACTIVE,
        ]);

        $this->changeServiceOrderStatusAction->execute($serviceBill->serviceOrder);

        return $serviceBill;
    }
}
