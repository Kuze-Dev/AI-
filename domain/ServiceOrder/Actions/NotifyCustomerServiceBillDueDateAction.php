<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Models\ServiceOrder;
use Spatie\QueueableAction\ActionJob;

class NotifyCustomerServiceBillDueDateAction
{
    public function __construct(
        private SendToCustomerServiceBillDueDateEmailAction $sendToCustomerServiceBillDueDateAction
    ) {
    }

    public function execute(): void
    {
        $customers = Customer::query()
            ->with([
                'serviceOrders' => fn ($query) => $query->whereActive(),
                'serviceOrders.serviceBills' => fn ($query) => $query
                    ->whereForPaymentStatus()
                    ->whereHasNotSentEmailNotification(),
            ])
            ->whereActive()
            ->whereRegistered()
            ->whereHas('serviceOrders', function ($query) {
                $query->whereHas('serviceBills', function ($nestedQuery) {
                    $nestedQuery
                        ->whereForPaymentStatus()
                        ->whereHasNotSentEmailNotification();
                });
            })
            ->get();

        $customers
            ->each(function (Customer $customer) {
                $customer
                    ->serviceOrders
                    ->each(function (ServiceOrder $serviceOrder) use ($customer) {
                        /** @var \Domain\ServiceOrder\Models\ServiceBill|null $latestForPaymentServiceBill */
                        $latestForPaymentServiceBill = $serviceOrder->serviceBills->first();

                        if (
                            $latestForPaymentServiceBill &&
                            now()->parse($latestForPaymentServiceBill->bill_date)
                                ->toDateString() <= now()->toDateString()
                        ) {
                            $this->sendToCustomerServiceBillDueDateAction
                                ->onQueue()
                                ->execute(
                                    $customer,
                                    $latestForPaymentServiceBill
                                )
                                ->chain([
                                    new ActionJob(SaveServiceBillEmailSentTimestampAction::class)
                                ]);
                        }
                    });
            });
    }
}
