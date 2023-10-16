<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Jobs\CreateServiceBillJob;
use Domain\ServiceOrder\Models\ServiceOrder;

class CreateServiceBillingsAction
{
    public function __construct(private SendToCustomerServiceBillEmailAction $sendToCustomerServiceBillEmailAction)
    {
    }

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
            ->whereHas('serviceOrders')
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
                                $serviceOrder,
                                $latestPaidServiceBill
                            );

                            /** @var \Domain\ServiceOrder\Models\ServiceBill $serviceBill */
                            $serviceBill = $serviceOrder->refresh()->latestForPaymentServiceBill();

                            $this->sendToCustomerServiceBillEmailAction
                                ->onQueue()
                                ->execute(
                                    $customer,
                                    $serviceBill
                                );
                        }
                    });
            });
    }
}
