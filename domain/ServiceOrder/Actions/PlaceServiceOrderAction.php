<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\PlaceServiceOrderData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Models\ServiceOrder;

class PlaceServiceOrderAction
{
    public function __construct(
        private CreateServiceOrderAction $createServiceOrderAction,
        private CreateServiceOrderAddressAction $createServiceOrderAddressAction,
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

        $this->notifyCustomerServiceOrderStatusAction
            ->execute($serviceOrder);

        return $serviceOrder;
    }
}
