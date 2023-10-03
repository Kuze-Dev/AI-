<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Akaunting\Money\Money;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;

class CalculateServiceOrderAdditionalChargeAction
{
    public function execute(ServiceOrderAdditionalChargeData $additionalCharge): Money
    {
        return money($additionalCharge->price)
            ->multiply($additionalCharge->quantity);
    }
}
