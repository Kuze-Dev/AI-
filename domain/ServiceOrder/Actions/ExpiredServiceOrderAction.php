<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Spatie\QueueableAction\QueueableAction;

/** TODO: to be removed */
class ExpiredServiceOrderAction
{
    use QueueableAction;

    public function __construct(
        private ChangeServiceOrderStatusAction $changeServiceOrderStatusAction
    ) {
    }

    public function execute(
        ServiceOrder $serviceOrder,
    ): void {

        $serviceOrder->update([
            'status' => ServiceOrderStatus::INACTIVE,
        ]);

        $this->changeServiceOrderStatusAction->execute($serviceOrder);
    }
}
