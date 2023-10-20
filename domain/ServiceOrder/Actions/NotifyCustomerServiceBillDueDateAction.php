<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Models\ServiceOrder;

class NotifyCustomerServiceBillDueDateAction
{
    public function __construct(
        private SendToCustomerServiceBillDueDateEmailAction $sendToCustomerServiceBillDueDateAction,
        private ExpiredServiceOrderAction $expiredServiceOrderAction
    ) {
    }

    public function execute(): void
    {
        $customers = Customer::query()
            ->with([
                'serviceOrders' => fn ($query) => $query->whereActive()->whereSubscriptionBased(),
                'serviceOrders.serviceBills' => fn ($query) => $query->whereNotifiable(),
            ])
            ->whereActive()
            ->whereRegistered()
            ->whereHas('serviceOrders.serviceBills', fn ($query) => $query->whereNotifiable())
            ->get();

        $customers
            ->each(function (Customer $customer) {
                $customer
                    ->serviceOrders
                    ->each(function (ServiceOrder $serviceOrder) use ($customer) {
                        /** @var \Domain\ServiceOrder\Models\ServiceBill $latestForPaymentServiceBill */
                        $latestForPaymentServiceBill = $serviceOrder->serviceBills->first();

                        if (
                            $latestForPaymentServiceBill->bill_date
                                ->toDateString() === now()->toDateString()
                        ) {
                            $this->sendToCustomerServiceBillDueDateAction
                                ->onQueue()
                                ->execute(
                                    $customer,
                                    $latestForPaymentServiceBill
                                );
                        }

                        if ($latestForPaymentServiceBill->due_date
                            ->toDateString() < now()->toDateString()
                        ) {
                            $this->expiredServiceOrderAction
                                ->onQueue()
                                ->execute($latestForPaymentServiceBill);
                        }
                    });
            });
    }
}
