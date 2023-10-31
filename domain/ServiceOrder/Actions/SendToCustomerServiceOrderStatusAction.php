<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillException;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Notifications\ActivatedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ClosedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ExpiredServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ForPaymentNotification;
use Throwable;

class SendToCustomerServiceOrderStatusAction
{
    /** @throws Throwable */
    public function execute(ServiceOrder $serviceOrder): void
    {
        /** @var \Domain\ServiceOrder\Models\ServiceBill|null $serviceBill */
        $serviceBill = $serviceOrder->latestServiceBill();

        /** @var \Domain\Customer\Models\Customer|null $customer */
        $customer = $serviceOrder->customer;

        if (is_null($serviceBill) || is_null($customer)) {
            throw new InvalidServiceBillException();
        }

        $notification = match ($serviceOrder->status) {
            ServiceOrderStatus::ACTIVE => new ActivatedServiceOrderNotification($serviceBill),
            ServiceOrderStatus::INACTIVE => new ExpiredServiceOrderNotification($serviceBill),
            ServiceOrderStatus::CLOSED => new ClosedServiceOrderNotification($serviceBill),
            ServiceOrderStatus::FORPAYMENT => new ForPaymentNotification($serviceBill),
            ServiceOrderStatus::INPROGRESS => null,
            default => null
        };

        if ($notification) {
            $customer->notify($notification);
        }
    }
}
