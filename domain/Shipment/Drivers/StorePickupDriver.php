<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Shipment\API\USPS\Contracts\RateResponse;
use Domain\Shipment\DataTransferObjects\StorePickupResponseData;

class StorePickupDriver
{
    public function getRate(): RateResponse
    {
        return app(StorePickupResponseData::class);
    }

    public function getInternationalRate(): RateResponse
    {
        return app(StorePickupResponseData::class);
    }
}
