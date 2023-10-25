<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Jobs\CreateServiceBillJob;
use Domain\ServiceOrder\Jobs\NotifyCustomerLatestServiceBillJob;
use Domain\ServiceOrder\Models\ServiceOrder;

class CreateServiceBillingsAction
{
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
            ->each(function (Customer $customer) {
                $customer
                    ->serviceOrders
                    ->each(function (ServiceOrder $serviceOrder) use ($customer) {
                        /** @var \Domain\ServiceOrder\Models\ServiceBill $latestServiceBill */
                        $latestServiceBill = $serviceOrder->latestServiceBill();

                        if (
                            now()->parse($latestServiceBill->bill_date)
                                ->toDateString() === now()->toDateString()
                        ) {
                            CreateServiceBillJob::dispatch(
                                $customer,
                                $serviceOrder,
                                $latestServiceBill
                            )->chain([
                                new NotifyCustomerLatestServiceBillJob(
                                    $customer,
                                    $serviceOrder
                                ),
                            ]);
                        }
                    });
            });
    }
}
