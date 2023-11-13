<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\UpdateServiceOrderStatusData;
use Domain\ServiceOrder\Exceptions\ServiceOrderNotFoundException;
use Domain\ServiceOrder\Exceptions\ServiceOrderNotYetPaidException;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;

class UpdateServiceOrderStatusAction
{
    public function execute(string $referenceId, UpdateServiceOrderStatusData $updateServiceOrderData): ServiceOrder
    {
        $serviceOrder = ServiceOrder::whereReference($referenceId)->first();

        if (! $serviceOrder) {
            throw new ServiceOrderNotFoundException('Service Order not found!');
        }

        $serviceBill = ServiceBill::query()->whereNonSubPaid($serviceOrder)->first();
        if (! $serviceBill) {
            throw new ServiceOrderNotYetPaidException('Service order not yet paid!');
        }

        $serviceOrder->update([
            'status' => $updateServiceOrderData->status,
        ]);

        return $serviceOrder;
    }
}
