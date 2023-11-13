<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\UpdateServiceOrderData;
use Domain\ServiceOrder\Models\ServiceOrder;

class UpdateServiceOrderAction
{
    public function execute(ServiceOrder $serviceOrder, UpdateServiceOrderData $updateServiceOrderData): ServiceOrder
    {

        $serviceOrder->update([
            'additional_charges' => $updateServiceOrderData->additional_charges,
            'customer_form' => $updateServiceOrderData->customer_form,
            'sub_total' => $updateServiceOrderData->sub_total,
            'tax_total' => $updateServiceOrderData->tax_total,
            'total_price' => $updateServiceOrderData->total_price,
        ]);

        return $serviceOrder;
    }
}
