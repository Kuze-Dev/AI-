<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Akaunting\Money\Money;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;

class CalculateServiceOrderTotalPriceAction
{
    public function execute(int $servicePrice, array $additionalCharges = []): Money
    {
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
