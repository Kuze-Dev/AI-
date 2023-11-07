<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Jobs\InactivateServiceOrderStatusJob;
use Domain\ServiceOrder\Jobs\NotifyCustomerServiceOrderStatusJob;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Support\Facades\Log;

class InactivateServiceOrdersAction
{
    public function execute(): void
    {
        $customers = Customer::query()
            ->with([
                'serviceOrders' => fn ($query) => $query->whereCanBeInactivated(),
                'serviceOrders.serviceBills' => fn ($query) => $query->whereNotifiable(),
            ])
            ->whereActive()
            ->whereHas('serviceOrders', function ($query) {
                $query->whereCanBeInactivated()
                    ->whereHas('serviceBills', fn ($nestedQuery) => $nestedQuery->whereNotifiable());
            })
            ->get();

        $customers
            ->each(
                function (Customer $customer) {
                    $customer->serviceOrders
                        ->each(
                            function (ServiceOrder $serviceOrder) {
                                /** @var \Domain\ServiceOrder\Models\ServiceBill $latestPendingServiceBill */
                                $latestPendingServiceBill = $serviceOrder->serviceBills
                                    ->sortByDesc('created_at')
                                    ->first();

                                $isOverdue = now()->toDateString() === now()
                                    ->parse($latestPendingServiceBill->due_date)
                                    ->addDay()
                                    ->toDateString();

                                /** @var \Illuminate\Foundation\Bus\PendingDispatch $inactivateServiceOrderStatusJob */
                                $inactivateServiceOrderStatusJob = InactivateServiceOrderStatusJob::dispatchIf(
                                    $isOverdue,
                                    $serviceOrder
                                );

                                $inactivateServiceOrderStatusJob->chain([
                                    new NotifyCustomerServiceOrderStatusJob($serviceOrder),
                                ]);

                                Log::info(
                                    'Service Order '.$serviceOrder->getRouteKey().'\'s status, will be inactivated. '.
                                    'Unpaid bill '.$latestPendingServiceBill->getRouteKey().', is overdue.'
                                );
                            }
                        );
                }
            );
    }
}
