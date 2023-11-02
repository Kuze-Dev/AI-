<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\PlaceServiceOrderData;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAddressActionData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Models\ServiceOrder;

class PlaceServiceOrderAction
{
    public function __construct(
        private CreateServiceOrderAction $createServiceOrderAction,
        private CreateServiceOrderAddressAction $createServiceOrderAddressAction,
        private CreateServiceBillAction $createServiceBillAction,
        private SendToCustomerServiceOrderStatusEmailAction $sendToCustomerServiceOrderStatusEmailAction
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
                new ServiceOrderAddressActionData(
                    serviceOrder: $serviceOrder,
                    service_address_id: $placeServiceOrderData->service_address_id,
                    billing_address_id: $placeServiceOrderData->billing_address_id,
                    is_same_as_billing: $placeServiceOrderData->is_same_as_billing
                )
            );

        if (
            ! $serviceOrder->needs_approval &&
            $this->createServiceBillAction
                ->execute(ServiceBillData::initialFromServiceOrder($serviceOrder))
                ->exists
        ) {
            $this->sendToCustomerServiceOrderStatusEmailAction
                ->execute($serviceOrder);
        }

        return $serviceOrder;
    }
}
