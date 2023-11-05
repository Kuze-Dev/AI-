<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Support\Facades\Log;
use Spatie\QueueableAction\ActionJob;

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

                                $dateToday = now()->toDateString();
                                $dueDate = now()->parse($latestPendingServiceBill->due_date)
                                    ->addDay()
                                    ->toDateString();

                                if ($dateToday === $dueDate) {
                                    /** avoids invoking chain method from the returned result
                                     * @phpstan-ignore-next-line */
                                    app(InactivateServiceOrderStatusAction::class)
                                        ->onQueue()
                                        ->execute($serviceOrder)
                                        ->chain([
                                            new ActionJob(
                                                SendToCustomerServiceOrderStatusEmailAction::class,
                                                [$serviceOrder]
                                            ),
                                        ]);

                                    Log::info(
                                        'Service Order '.$serviceOrder->getRouteKey().'\'s status, will be inactivated. '.
                                        'Unpaid bill '.$latestPendingServiceBill->getRouteKey().', is overdue.'
                                    );
                                }
                            }
                        );
                }
            );
    }
}
