<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Service\Enums\BillingCycleEnum;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillingCycleException;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Support\Carbon;
use Throwable;

class ComputeServiceBillingCycleAction
{
    /** @throws Throwable */
    public function execute(
        ServiceOrder $serviceOrder,
        Carbon $startDate
    ): ServiceOrderBillingAndDueDateData {

        $billDate = match ($serviceOrder->billing_cycle) {
            BillingCycleEnum::DAILY => $startDate->addDay(),
            BillingCycleEnum::MONTHLY => $startDate->addMonthNoOverflow(),
            BillingCycleEnum::YEARLY => $startDate->addYearNoOverflow(),
            default => throw new InvalidServiceBillingCycleException(),
        };

        return new ServiceOrderBillingAndDueDateData(
            bill_date: $billDate,
            due_date: now()
                ->parse($billDate)
                ->addDays($serviceOrder->due_date_every ?? 0)
        );

    }
}
