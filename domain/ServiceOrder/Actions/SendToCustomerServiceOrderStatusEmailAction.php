<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Notifications\ActivatedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ClosedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\CompletedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ConfirmationServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ExpiredServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ForPaymentNotification;
use Domain\ServiceOrder\Notifications\InProgressServiceOrderNotification;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Spatie\QueueableAction\QueueableAction;
use Throwable;

class SendToCustomerServiceOrderStatusEmailAction
{
    use QueueableAction;

    /** @throws Throwable */
    public function execute(ServiceOrder $serviceOrder): void
    {
        /** @var Notification|null $notification */
        $notification = match ($serviceOrder->status) {
            ServiceOrderStatus::PENDING => new ConfirmationServiceOrderNotification($serviceOrder),
            ServiceOrderStatus::INPROGRESS => new InProgressServiceOrderNotification($serviceOrder),
            ServiceOrderStatus::COMPLETED => new CompletedServiceOrderNotification($serviceOrder),
            ServiceOrderStatus::CLOSED => new ClosedServiceOrderNotification($serviceOrder),
            default => null
        };

        $serviceBill = $serviceOrder->latestServiceBill();

        if ($serviceBill instanceof ServiceBill && is_null($notification)) {
            $notification = match ($serviceOrder->status) {
                ServiceOrderStatus::ACTIVE => new ActivatedServiceOrderNotification($serviceBill),
                ServiceOrderStatus::INACTIVE => new ExpiredServiceOrderNotification($serviceBill),
                ServiceOrderStatus::FORPAYMENT => new ForPaymentNotification($serviceBill),
                default => null
            };
        }

        $customer = $serviceOrder->customer;

        if ($customer instanceof Customer && $notification instanceof Notification) {
            $customer->notify($notification);

            Log::info('Service Order status email notification sent to '.$customer->email);
        }
    }
}
