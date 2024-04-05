<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
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

        $serviceOrders = ServiceOrder::query()
            ->whereShouldAutoGenerateBill()
            ->with(['serviceBills.serviceTransactions'])
            ->has('serviceBills')
            ->get();

        $serviceOrders->each(
            function (ServiceOrder $serviceOrder) {
                /** @var \Domain\ServiceOrder\Models\ServiceBill $latestServiceBill */
                $latestServiceBill = $serviceOrder->serviceBills
                    ->sortByDesc('created_at')
                    ->first();

                $referenceDate = $latestServiceBill->bill_date;

                /** @var \Illuminate\Database\Eloquent\Collection<int, ServiceTransaction> $transactions */
                $transactions = $latestServiceBill->serviceTransactions;

                /** @var \Domain\ServiceOrder\Models\ServiceTransaction|null $serviceTransaction */
                $serviceTransaction = $transactions->sortByDesc('id')
                    ->where('status', ServiceTransactionStatus::PAID)
                    ->first();

                /** @var \Domain\ServiceOrder\Models\ServiceTransaction|null $serviceTransaction */
                $serviceTransaction ??= $transactions->sortByDesc('id')->first();

                $isServiceTransactionStatusPaid = $serviceTransaction instanceof ServiceTransaction &&
                    $serviceTransaction->is_paid;

                $isInitialServiceBillStatusPaid = $latestServiceBill->is_initial &&
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

                $isBillingDateToday = is_null($referenceDate)
                    ? false
                    : now()->parse($referenceDate)->toDateString() === now()->toDateString();

                if ($referenceDate instanceof Carbon) {
                    /** @var \Illuminate\Foundation\Bus\PendingDispatch $createServiceBillJob */
                    $createServiceBillJob = CreateServiceBillJob::dispatchIf(
                        $isBillingDateToday,
                        $serviceOrder,
                        $this->computeServiceBillingCycleAction->execute($serviceOrder, $referenceDate)
                    );

                    $createServiceBillJob->chain([new NotifyCustomerLatestServiceBillJob($serviceOrder)]);
                }
            }
        );
    }
}
