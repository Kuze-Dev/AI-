<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\PlaceServiceOrderData;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Models\ServiceOrder;

class PlaceServiceOrderAction
{
    public function __construct(
        private CreateServiceOrderAction $createServiceOrderAction,
        private CreateServiceOrderAddressAction $createServiceOrderAddressAction,
        private CreateServiceBillAction $createServiceBillAction,
        private NotifyCustomerServiceOrderStatusAction $notifyCustomerServiceOrderStatusAction
    ) {
    }

    public function execute(
        PlaceServiceOrderData $placeServiceOrderData
    ): ServiceOrder {

        $serviceOrderData = ServiceOrderData::fromArray(
            (array) $placeServiceOrderData
        );

        $serviceOrder = $this->createServiceOrderAction
            ->execute($serviceOrderData);

        $this->createServiceOrderAddressAction
            ->execute(
                $serviceOrder,
                $serviceOrderData
            );

        if (
            ! $serviceOrder->needs_approval &&
            $this->createServiceBillAction
                ->execute(
                    ServiceBillData::fromCreatedServiceOrder($serviceOrder->toArray())
                )
                ->exists
        ) {
            $this->notifyCustomerServiceOrderStatusAction
                ->execute($serviceOrder);
        }

        return $serviceOrder;
    }
}
