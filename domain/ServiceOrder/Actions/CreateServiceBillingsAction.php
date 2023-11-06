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
                'serviceOrders.serviceBills.serviceTransactions',
            ])
            ->whereActive()
            ->whereRegistered()
            ->whereHas('serviceOrders', fn ($query) => $query->whereShouldAutoGenerateBill()
                ->has('serviceBills'))
            ->get();

        $customers->each(
            function (Customer $customer) {
                $customer
                    ->serviceOrders
                    ->each(
                        function (ServiceOrder $serviceOrder) {
                            /** @var \Domain\ServiceOrder\Models\ServiceBill $latestServiceBill */
                            $latestServiceBill = $serviceOrder->serviceBills
                                ->sortByDesc('created_at')
                                ->first();

                            $referenceDate = $latestServiceBill->bill_date;

                            /** @var \Illuminate\Database\Eloquent\Collection<int, ServiceTransaction> $transactions */
                            $transactions = $latestServiceBill->serviceTransactions;

                            /** @var \Domain\ServiceOrder\Models\ServiceTransaction|null $serviceTransaction */
                            $serviceTransaction = $transactions->sortByDesc('created_at')->first();

                            $isServiceTransactionStatusPaid = $serviceTransaction instanceof ServiceTransaction &&
                                $serviceTransaction->is_paid;

                            $isInitialServiceBillStatusPaid = is_null($referenceDate) &&
                                $latestServiceBill->is_paid &&
                                $isServiceTransactionStatusPaid;

                            if ($isInitialServiceBillStatusPaid) {
                                /**
                                 * @var \Domain\ServiceOrder\Models\ServiceTransaction $serviceTransaction
                                 * @var \Illuminate\Support\Carbon $createdAt
                                 */
                                $createdAt = $serviceTransaction->created_at;

                                $serviceOrderBillingAndDueDateData = $this->computeServiceBillingCycleAction
                                    ->execute($serviceOrder, $createdAt);

                                $referenceDate = $serviceOrderBillingAndDueDateData->bill_date;
                            }

                            $isBillingDateToday = $referenceDate instanceof Carbon &&
                                (
                                    now()->parse($referenceDate)
                                        ->toDateString() === now()->toDateString()
                                );

                            /** @var \Illuminate\Foundation\Bus\PendingDispatch $createServiceBillJob */
                            $createServiceBillJob = CreateServiceBillJob::dispatchIf(
                                $isBillingDateToday,
                                $serviceOrder,
                                $latestServiceBill
                            );

                            $createServiceBillJob->chain([new NotifyCustomerLatestServiceBillJob($serviceOrder)]);
                        }
                    );
            }
        );
    }
}
