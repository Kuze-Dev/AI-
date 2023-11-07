<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Jobs\CreateServiceBillJob;
use Domain\ServiceOrder\Jobs\NotifyCustomerLatestServiceBillJob;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Illuminate\Support\Carbon;

class CreateServiceBillingsAction
{
    public function __construct(
        private ComputeServiceBillingCycleAction $computeServiceBillingCycleAction
    ) {
    }

    public function execute(): void
    {
        $customers = Customer::query()
            ->with([
                'serviceOrders' => fn ($query) => $query->whereShouldAutoGenerateBill(),
                'serviceOrders.serviceBills',
            ])
            ->whereActive()
            ->whereRegistered()
            ->whereHas('serviceOrders', fn ($query) => $query->has('serviceBills'))
            ->get();

        $customers
            ->each(
                function (Customer $customer) {
                    $customer
                        ->serviceOrders
                        ->each(
                            function (ServiceOrder $serviceOrder) use ($customer) {
                                /** @var \Domain\ServiceOrder\Models\ServiceBill $latestServiceBill */
                                $latestServiceBill = $serviceOrder->latestServiceBill();

                                $referenceDate = $latestServiceBill->bill_date;

                                /** @var \Domain\ServiceOrder\Models\ServiceTransaction|null $serviceTransaction */
                                $serviceTransaction = $latestServiceBill->serviceTransaction;

                                if (
                                    is_null($referenceDate) &&
                                    $serviceTransaction instanceof ServiceTransaction
                                ) {
                                    /** @var \Illuminate\Support\Carbon $createdAt */
                                    $createdAt = $serviceTransaction->created_at;

                                    $serviceOrderBillingAndDueDateData = $this->computeServiceBillingCycleAction
                                        ->execute(
                                            $serviceOrder,
                                            $createdAt
                                        );

                                    $referenceDate = $serviceOrderBillingAndDueDateData->bill_date;
                                }

                                $isBillingDateToday = now()->parse($referenceDate)
                                    ->toDateString() === now()->toDateString();

                                if ($referenceDate instanceof Carbon && $isBillingDateToday) {
                                    CreateServiceBillJob::dispatch(
                                        $serviceOrder,
                                        $latestServiceBill
                                    )->chain([
                                        new NotifyCustomerLatestServiceBillJob(
                                            $customer,
                                            $serviceOrder
                                        ),
                                    ]);
                                }
                            }
                        );
                }
            );
    }
}
