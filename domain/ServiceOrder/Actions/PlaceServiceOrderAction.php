<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;

class PlaceServiceOrderAction
{
    public function __construct(
        private CreateServiceOrderAction $createServiceOrderAction,
        private CreateServiceOrderAddressAction $createServiceOrderAddressAction,
        private CreateServiceBillAction $createServiceBillAction,
    ) {
    }

    public function execute(array $data, int|null $customer_id, int|null $adminId): ServiceOrder|ServiceBill
    {
        $serviceOrderData = ServiceOrderData::fromArray($data, $customer_id);

        $serviceOrder = $this->createServiceOrderAction->execute($serviceOrderData, $adminId);

        $this->createServiceOrderAddressAction->execute($serviceOrder, $serviceOrderData);

        $serviceBillData = ServiceBillData::fromArray($serviceOrder->toArray());

        $serviceBill = $this->createServiceBillAction->execute($serviceOrder, $serviceBillData);

        if( ! $adminId) {
            return $serviceBill;
        } else {
            return $serviceOrder;
        }
    }
}
