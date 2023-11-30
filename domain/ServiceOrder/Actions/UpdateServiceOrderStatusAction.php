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
    public function execute(ServiceOrder $serviceOrder, UpdateServiceOrderStatusData $updateServiceOrderStatusData): ServiceOrder
    {
        $serviceOrder->update([
            'status' => $updateServiceOrderStatusData->status]);

        return $serviceOrder;
    }

    private function validateServiceOrder(string $referenceId): ServiceOrder
    {
        $serviceOrder = ServiceOrder::whereReference($referenceId)->first();

        if (! $serviceOrder) {
            throw new ServiceOrderNotFoundException('Service Order not found!');
        }

        return $serviceOrder;
    }

    public function complete(string $referenceId, UpdateServiceOrderStatusData $updateServiceOrderData): ServiceOrder
    {

        $serviceOrder = $this->validateServiceOrder($referenceId);

        if ($serviceOrder->whereSubscriptionBased()->first() || ! $serviceOrder->whereInProgress()->first()) {
            throw new ServiceOrderNotFoundException('Service Order not found!');
        }

        $serviceBill = ServiceBill::whereServiceOrderId($serviceOrder->id)->whereStatusPaid()->first();

        if (! $serviceBill) {
            throw new ServiceOrderNotYetPaidException('Service order not yet paid!');
        }

        $serviceOrder = $this->execute($serviceOrder, $updateServiceOrderData);

        return $serviceOrder;
    }

    public function close(string $referenceId, UpdateServiceOrderStatusData $updateServiceOrderData): ServiceOrder
    {
        $serviceOrder = $this->validateServiceOrder($referenceId);

        if (! $serviceOrder->whereSubscriptionBased()->first()) {
            throw new ServiceOrderNotFoundException('Service Order not found!');
        }

        if (! $serviceOrder->whereActive()->first() && ! $serviceOrder->whereInactive()->first()) {
            throw new ServiceOrderNotFoundException('Service Order not found!');
        }

        $serviceOrder = $this->execute($serviceOrder, $updateServiceOrderData);

        return $serviceOrder;
    }
}
