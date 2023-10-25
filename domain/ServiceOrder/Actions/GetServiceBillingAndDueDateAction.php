<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Carbon\Carbon;
use Domain\Service\Enums\BillingCycleEnum;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillException;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillingCycleException;
use Domain\ServiceOrder\Exceptions\NonSubscriptionNotAllowedException;
use Domain\ServiceOrder\Models\ServiceBill;
use Throwable;

class GetServiceBillingAndDueDateAction
{
    /** @throws Throwable */
    public function execute(ServiceBill $serviceBill): mixed
    {
        /** @var \Domain\Service\Models\Service $service */
        $service = $serviceBill->serviceOrder
            ->service;

        if ((bool) $service->is_subscription === false) {
            throw new NonSubscriptionNotAllowedException();
        }

        /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
        $serviceOrder = $serviceBill->serviceOrder;

        /** @var \Domain\Service\Enums\BillingCycleEnum $billingCycle */
        $billingCycle = $serviceOrder->billing_cycle;

        /** @var int $dueDateEvery */
        $dueDateEvery = $serviceOrder->due_date_every ?? 0;

        /** @var \Domain\ServiceOrder\Models\ServiceTransaction|null $serviceTransaction */
        $serviceTransaction = $serviceBill->serviceTransaction;

        /** @var \Illuminate\Support\Carbon|null $referenceDate */
        $referenceDate = $serviceBill->bill_date;

        if ($serviceTransaction) {
            /** @var \Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData $serviceTransactionComputedBillingCycle */
            $serviceTransactionComputedBillingCycle = $this->computeBillingCycle(
                $billingCycle,
                $dueDateEvery,
                /** @phpstan-ignore-next-line */
                $serviceTransaction->created_at,
            );

            if (
                is_null($serviceBill->due_date) ||
                $serviceBill->due_date < now()
            ) {
                $referenceDate = $serviceTransactionComputedBillingCycle
                    ->bill_date;
            }
        }

        if (is_null($referenceDate)) {
            throw new InvalidServiceBillException();
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
