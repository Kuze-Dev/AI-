<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Jobs\CheckDueDateJob;
use Domain\ServiceOrder\Models\ServiceOrder;

class CheckDueDateAction
{
    public function execute(): void
    {
        $customers = Customer::query()
            ->with([
                'serviceOrders' => function ($query) {
                    $query
                        ->whereActive()
                        ->whereSubscriptionBased();
                },
                'serviceOrders.serviceBills',
            ])
            ->whereActive()
            ->whereRegistered()
            ->whereHasActiveSubscriptionBasedServiceOrder()
            ->get();

        $customers
            ->each(function (Customer $customer) {
                $customer
                    ->serviceOrders
                    ->each(function (ServiceOrder $serviceOrder) {
                        /** @var \Domain\ServiceOrder\Models\ServiceBill|null $latestPaidServiceBill */
                        $latestPaidServiceBill = $serviceOrder->latestPaidServiceBill();

                        if ($latestPaidServiceBill && $latestPaidServiceBill->due_date <= now()) {
                            CheckDueDateJob::dispatch(
                                $latestPaidServiceBill
                            );
                        }
                    });
            });
    }
}
