<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Service\Enums\BillingCycleEnum;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillingCycleException;
use Domain\ServiceOrder\Exceptions\ServiceBillStatusMusBePaidException;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Throwable;

class GetServiceBillingAndDueDateAction
{
    /** @throws Throwable */
    public function execute(
        ServiceBill $serviceBill,
        ServiceTransaction $serviceTransaction
    ): mixed {

        if ($serviceBill->status != ServiceBillStatus::PAID) {
            throw new ServiceBillStatusMusBePaidException();
        }

        /** @var \Illuminate\Support\Carbon $referenceDate */
        $referenceDate = $serviceTransaction->created_at;

        if ($serviceBill->due_date > now()) {
            $referenceDate = $serviceBill->due_date;
        }

        $referenceDate = now()->parse($referenceDate);

        /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
        $serviceOrder = $serviceBill->serviceOrder;

        /** @var \Illuminate\Support\Carbon $billDate */
        $billDate = match ($serviceOrder->billing_cycle) {
            BillingCycleEnum::DAILY => $referenceDate->addDay(),
            BillingCycleEnum::MONTHLY => $referenceDate->addMonthNoOverflow(),
            BillingCycleEnum::YEARLY => $referenceDate->addYearNoOverflow(),
            /** @phpstan-ignore-next-line  */
            default => throw new InvalidServiceBillingCycleException()
        };

        return new ServiceOrderBillingAndDueDateData(
            bill_date: $billDate,
            due_date: now()
                ->parse($billDate)
                ->addDays($serviceOrder->due_date_every)
        );
    }
}
