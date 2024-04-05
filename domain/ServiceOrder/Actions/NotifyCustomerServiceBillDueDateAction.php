<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use App\Settings\ServiceSettings;
use Domain\ServiceOrder\Jobs\NotifyCustomerServiceBillDueDateJob;
use Domain\ServiceOrder\Models\ServiceOrder;

class NotifyCustomerServiceBillDueDateAction
{
    public function __construct(private ServiceSettings $serviceSettings)
    {
    }

    public function execute(): void
    {

        $serviceOrders = ServiceOrder::query()
            ->whereActive()
            ->whereSubscriptionBased()
            ->with(['serviceBills' => fn ($query) => $query->whereNotifiable()])
            ->whereHas('serviceBills', fn ($query) => $query->whereNotifiable())
            ->get();

        /** @var int $daysBeforeDueDateNotification */
        $daysBeforeDueDateNotification = $this->serviceSettings
            ->days_before_due_date_notification ?? config('domain.service-order.days_before_due_date_notification');

        $serviceOrders->each(
            function (ServiceOrder $serviceOrder) use ($daysBeforeDueDateNotification) {
                /** @var \Domain\ServiceOrder\Models\ServiceBill $latestPendingServiceBill */
                $latestPendingServiceBill = $serviceOrder->latestPendingServiceBill();

                $dateOfNotification = now()->parse($latestPendingServiceBill->due_date)
                    ->subDays($daysBeforeDueDateNotification)
                    ->toDateString();

                $dateToday = now()->toDateString();

                $isDateOfNotificationToday = $dateOfNotification === $dateToday;

                $billDate = now()->parse($latestPendingServiceBill->bill_date)
                    ->toDateString();

                $overeachedBillDate = $dateOfNotification < $billDate;

                $isBillingDateToday = $billDate === $dateToday;

                $shouldNotifyOnBillingDate = $overeachedBillDate && $isBillingDateToday;

                NotifyCustomerServiceBillDueDateJob::dispatchIf(
                    $shouldNotifyOnBillingDate || $isDateOfNotificationToday,
                    $serviceOrder,
                    $latestPendingServiceBill
                );
            }
        );
    }
}
