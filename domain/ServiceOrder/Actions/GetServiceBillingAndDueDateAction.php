<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Service\Enums\BillingCycle;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillingCycleException;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Throwable;

class GetServiceBillingAndDueDateAction
{
    /** @throws Throwable */
    public function execute(
        ServiceOrder|ServiceBill $serviceData
    ): mixed {

        if ($serviceData instanceof ServiceOrder) {
            $referenceDate = $serviceData->created_at;
        }

        if ($serviceData instanceof ServiceBill) {
            $referenceDate = $serviceData->due_date <= now()
                ? $serviceData->created_at
                : $serviceData->due_date;

            /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceData */
            $serviceData = $serviceData->serviceOrder;
        }

        $referenceDate = now()->parse($referenceDate);

        $billDate = match ($serviceData->billing_cycle) {
            BillingCycle::DAILY => $referenceDate->addDay(),
            BillingCycle::MONTHLY => $referenceDate->addMonthNoOverflow(),
            BillingCycle::YEARLY => $referenceDate->addYearNoOverflow(),
            /** @phpstan-ignore-next-line  */
            default => throw new InvalidServiceBillingCycleException()
        };

        return new ServiceOrderBillingAndDueDateData(
            bill_date: $billDate,
            due_date: now()
                ->parse($billDate)
                ->addDays($serviceData->due_date_every)
        );
    }
}
