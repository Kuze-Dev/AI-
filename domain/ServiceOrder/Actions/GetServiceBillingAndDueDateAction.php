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
            $referenceDate = now()->parse($serviceData->created_at);
        }

        if ($serviceData instanceof ServiceBill) {
            $referenceDate = now()->parse($serviceData->bill_date);
            $serviceData = $serviceData->service_order;
        }

        $billDate = match ($serviceData->billing_cycle) {
            BillingCycle::DAILY => $referenceDate->addDay(),
            BillingCycle::MONTHLY => $referenceDate->addMonthNoOverflow(),
            BillingCycle::YEARLY => $referenceDate->addYear(),
            /** @phpstan-ignore-next-line  */
            default => throw new InvalidServiceBillingCycleException()
        };

        return new ServiceOrderBillingAndDueDateData(
            bill_date: $billDate,
            due_date: $billDate->addDays($serviceData->due_date_every)
        );
    }
}
