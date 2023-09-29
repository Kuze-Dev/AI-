<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Models\ServiceOrder;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\MetaData\Actions\CreateMetaDataAction;

class CreateServiceOrderAction
{
    public function __construct(
        
    ) {
    }

    public function execute(ServiceOrderData $serviceData): ServiceOrder
    {

            $serviceOrder = ServiceOrder::create([
                'customer_id' => $serviceData->customer_id,
                'service_id' => $serviceData->service_id,
                'schedule' => $serviceData->schedule,
                'additional_charges' => $serviceData->additional_charges,
                'data' => $serviceData->data,
            ]);



        return $serviceOrder;
    }
}
