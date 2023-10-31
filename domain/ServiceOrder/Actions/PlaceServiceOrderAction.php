<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;

class PlaceServiceOrderAction
{
    public function __construct(
        private CreateServiceOrderAction $createServiceOrderAction,
        private CreateServiceOrderAddressAction $createServiceOrderAddressAction,
        private ChangeServiceOrderStatusAction $changeServiceOrderStatusAction
    ) {
    }

    public function execute(array $data, ?int $customer_id): ServiceOrder
    {
        $serviceOrderData = ServiceOrderData::fromArray($data, $customer_id);

        $serviceOrder = $this->createServiceOrderAction->execute($serviceOrderData);

        $this->createServiceOrderAddressAction->execute($serviceOrder, $serviceOrderData);

        $this->changeServiceOrderStatusAction->execute($serviceOrder, true);

        return $serviceOrder;
    }
}
