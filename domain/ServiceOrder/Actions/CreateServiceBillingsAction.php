<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Jobs\CreateServiceBillJob;
use Domain\ServiceOrder\Models\ServiceOrder;

class CreateServiceBillingsAction
{
    public function execute(): void
    {
        $customers = Customer::query()
            ->with([
                'serviceOrders' => fn ($query) => $query->active(),
                'serviceOrders.serviceBills',
            ])
            ->whereActive()
            ->whereRegistered()
            ->whereActiveServiceOrder()
            ->get();

        $customers
            ->each(function (Customer $customer) {
                $customer
                    ->serviceOrders
                    ->each(function (ServiceOrder $serviceOrder) use ($customer) {
                        /** @var \Domain\ServiceOrder\Models\ServiceBill|null $latestPaidServiceBill */
                        $latestPaidServiceBill = $serviceOrder->latestPaidServiceBill();

                        if ($latestPaidServiceBill && $latestPaidServiceBill->bill_date <= now()) {
                            CreateServiceBillJob::dispatch(
                                $customer,
                                $latestPaidServiceBill
                            );
                        }
                    });
            });
    }
}
