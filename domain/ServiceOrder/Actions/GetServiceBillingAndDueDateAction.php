<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillException;
use Domain\ServiceOrder\Exceptions\NonSubscriptionNotAllowedException;
use Domain\ServiceOrder\Models\ServiceBill;
use Throwable;

class GetServiceBillingAndDueDateAction
{
    public function __construct(
        private ComputeServiceBillingCycle $computeServiceBillingCycle
    ) {
    }

    /** @throws Throwable */
    public function execute(ServiceBill $serviceBill): ServiceOrderBillingAndDueDateData
    {
        /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
        $serviceOrder = $serviceBill->serviceOrder;

        if (! $serviceOrder->is_subscription) {
            throw new NonSubscriptionNotAllowedException();
        }

        /** @var \Domain\ServiceOrder\Models\ServiceTransaction|null $serviceTransaction */
        $serviceTransaction = $serviceBill->serviceTransaction;

        /** @var \Illuminate\Support\Carbon|null $referenceDate */
        $referenceDate = $serviceBill->bill_date;

        if ($serviceTransaction) {
            /** @var \Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData
             *  $serviceTransactionComputedBillingCycle
             */
            $serviceTransactionComputedBillingCycle = $this->computeServiceBillingCycle
                ->execute(
                    $serviceOrder,
                    /** @phpstan-ignore-next-line */
                    $serviceTransaction->created_at,
                );

            if (
                is_null($serviceBill->due_date) ||
                now()->parse($serviceBill->due_date)
                    ->toDateString() < now()->toDateString()
            ) {
                $referenceDate = $serviceTransactionComputedBillingCycle
                    ->bill_date;
            }
        }

        if (is_null($referenceDate)) {
            throw new InvalidServiceBillException();
        }

        return $this->computeServiceBillingCycle
            ->execute(
                $serviceOrder,
                now()->parse($referenceDate)
            );
    }
}
