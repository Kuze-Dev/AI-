<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Models\ServiceOrder;

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
                'serviceOrders.serviceBills' => fn ($query) => $query->whereNotifiable(),
            ])
            ->whereActive()
            ->whereRegistered()
            ->whereHas('serviceOrders', function ($query) {
                $query->whereHas('serviceBills', function ($nestedQuery) {
                    $nestedQuery->whereNotifiable();
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
                                );
                        }
                    });
            });
    }
}
