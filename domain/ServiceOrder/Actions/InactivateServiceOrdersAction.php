<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Jobs\InactivateServiceOrderStatusJob;
use Domain\ServiceOrder\Jobs\NotifyCustomerServiceOrderStatusJob;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\Tenant\TenantSupport;
use Illuminate\Support\Facades\Log;

class InactivateServiceOrdersAction
{
    public function execute(): void
    {
        TenantSupport::model()->run(function () {
            $serviceOrders = ServiceOrder::query()
                ->whereCanBeInactivated()
                ->with(['serviceBills' => fn ($query) => $query->whereNotifiable()])
                ->whereHas('serviceBills', fn ($query) => $query->whereNotifiable())
                ->get();

            $serviceOrders->each(
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
        });
    }
}
