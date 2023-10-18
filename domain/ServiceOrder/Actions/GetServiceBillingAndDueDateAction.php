<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Carbon\Carbon;
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

        /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
        $serviceOrder = $serviceBill->serviceOrder;

        /** @var \Domain\Service\Enums\BillingCycleEnum $billingCycle */
        $billingCycle = $serviceOrder->billing_cycle;

        /** @var int $dueDateEvery */
        $dueDateEvery = $serviceOrder->due_date_every;

        /** @var \Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData $serviceTransactionComputedBillingCycle */
        $serviceTransactionComputedBillingCycle = $this->computeBillingCycle(
            $billingCycle,
            $dueDateEvery,
            /** @phpstan-ignore-next-line */
            $serviceTransaction->created_at,
        );

        $referenceDate = $serviceTransactionComputedBillingCycle->bill_date;

        if ($serviceBill->due_date && $serviceBill->due_date >= now()) {
            $referenceDate = $serviceBill->bill_date;
        }

        return $this->computeBillingCycle(
            $billingCycle,
            $dueDateEvery,
            now()->parse($referenceDate)
        );
    }

    private function computeBillingCycle(
        BillingCycleEnum $billingCycleEnum,
        int $dueDateEvery,
        Carbon $startDate
    ): ServiceOrderBillingAndDueDateData {
        /** @var \Illuminate\Support\Carbon $billDate */
        $billDate = match ($billingCycleEnum) {
            BillingCycleEnum::DAILY => $startDate->addDay(),
            BillingCycleEnum::MONTHLY => $startDate->addMonthNoOverflow(),
            BillingCycleEnum::YEARLY => $startDate->addYearNoOverflow(),
            /** @phpstan-ignore-next-line  */
            default => throw new InvalidServiceBillingCycleException()
        };

        return new ServiceOrderBillingAndDueDateData(
            bill_date: $billDate,
            due_date: now()
                ->parse($billDate)
                ->addDays($dueDateEvery)
        );
    }
}
