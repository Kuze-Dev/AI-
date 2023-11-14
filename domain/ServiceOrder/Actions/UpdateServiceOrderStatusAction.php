<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\UpdateServiceOrderStatusData;
use Domain\ServiceOrder\Models\ServiceOrder;

class UpdateServiceOrderStatusAction
{
    public function execute(
        ServiceOrder $serviceOrder,
        UpdateServiceOrderStatusData $updateServiceOrderStatusData
    ): ServiceOrder {
        $serviceOrder->update([
            'status' => $updateServiceOrderStatusData->service_order_status,
        ]);

        return $serviceOrder;
    }
}
