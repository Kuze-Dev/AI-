<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Jobs\CreateServiceBillJob;
use Domain\ServiceOrder\Models\ServiceOrder;

class CreateServiceBillingsAction
{
    public function execute()
    {
        $customers = Customer::query()
            ->with([
                'serviceOrders' => fn ($query) => $query->active(),
                'serviceOrders.serviceBills'
            ])
            ->active()
            ->registered()
            ->withActiveServiceOrder()
            ->get();

        $customers
            ->each( function (Customer $customer) {
                $customer
                    ->serviceOrders
                    ->each(function (ServiceOrder $serviceOrder) use ($customer) {
                        /** @var \Domain\ServiceOrder\Models\ServiceBill $latestPaidServiceBill */
                        $latestServiceBill = $serviceOrder->latestServiceBill();

                        if (
                            $latestServiceBill->status === ServiceBillStatus::PAID &&
                            $latestServiceBill->bill_date <= now()
                        ) {
                            CreateServiceBillJob::dispatch(
                                $customer,
                                $latestServiceBill
                            );
                        }
                    });
            });
    }
}
