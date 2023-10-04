<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Carbon\Carbon;
use Domain\Service\Enums\BillingCycle;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillingCycleException;
use Domain\ServiceOrder\Models\ServiceOrder;
use Throwable;

class GetServiceOrderBillingAndDueDateAction
{
    /** @throws Throwable */
    public function execute(
        ServiceOrder $serviceOrder,
        Carbon $date
    ): mixed {
        $referenceDate = now()->parse($date);

        $billDate = match ($serviceOrder->billing_cycle) {
            BillingCycle::DAILY => $referenceDate->addDay(),
            BillingCycle::MONTHLY => $referenceDate->addMonthNoOverflow(),
            BillingCycle::YEARLY => $referenceDate->addYear(),
            /** @phpstan-ignore-next-line  */
            default => throw new InvalidServiceBillingCycleException()
        };

        return new ServiceOrderBillingAndDueDateData(
            bill_date: $billDate,
            due_date: $billDate->addDays($serviceOrder->due_date_every)
        );
    }
}
