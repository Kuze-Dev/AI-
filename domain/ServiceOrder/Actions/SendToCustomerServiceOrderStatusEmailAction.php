<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Notifications\ActivatedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ClosedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\CompletedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ConfirmationServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ExpiredServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ForPaymentNotification;
use Domain\ServiceOrder\Notifications\InProgressServiceOrderNotification;
use Throwable;

class SendToCustomerServiceOrderStatusEmailAction
{
    /** @throws Throwable */
    public function execute(ServiceOrder $serviceOrder): void
    {
        $notification = match ($serviceOrder->status) {
            ServiceOrderStatus::PENDING => new ConfirmationServiceOrderNotification($serviceOrder),
            ServiceOrderStatus::INPROGRESS => new InProgressServiceOrderNotification($serviceOrder),
            ServiceOrderStatus::COMPLETED => new CompletedServiceOrderNotification($serviceOrder),
            ServiceOrderStatus::CLOSED => new ClosedServiceOrderNotification($serviceOrder),
            default => null
        };

        /** @var \Domain\ServiceOrder\Models\ServiceBill|null $serviceBill */
        $serviceBill = $serviceOrder->latestServiceBill();

        if ($serviceBill && is_null($notification)) {
            $notification = match ($serviceOrder->status) {
                ServiceOrderStatus::ACTIVE => new ActivatedServiceOrderNotification($serviceBill),
                ServiceOrderStatus::INACTIVE => new ExpiredServiceOrderNotification($serviceBill),
                ServiceOrderStatus::FORPAYMENT => new ForPaymentNotification($serviceBill),
                default => null
            };
        }

        $serviceOrder->customer?->notify($notification);
    }
}
