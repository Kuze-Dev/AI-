<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Akaunting\Money\Money;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;

class CalculateServiceOrderTotalPriceAction
{
    /** @param  \Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData[]  $additionalCharges*/
    public function execute(float $servicePrice, array $additionalCharges = []): Money
    {
        /** @var \Akaunting\Money\Money $result */
        $result = money($servicePrice);

        if (empty($additionalCharges)) {
            return $result;
        }

        foreach ($additionalCharges as $additionalCharge) {
            $result = $result
                ->add(
                    $this->calculateServiceOrderAdditionalCharge($additionalCharge)
                );
        }

        return $result;
    }

    public function calculateServiceOrderAdditionalCharge(ServiceOrderAdditionalChargeData $additionalCharge): Money
    {
        return money($additionalCharge->price)
            ->multiply($additionalCharge->quantity);
    }
}
