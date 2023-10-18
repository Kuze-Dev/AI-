<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Notifications\ActivatedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ClosedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ExpiredServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ForPaymentNotification;

class ChangeServiceOrderStatusAction
{
    private $serviceBill;

    public function execute(ServiceOrder $serviceOrder): void
    {
        $this->serviceBill = $serviceOrder->serviceBills()->latest()->first();
        match ($serviceOrder->status) {
            ServiceOrderStatus::ACTIVE => $this->onActive($serviceOrder),
            ServiceOrderStatus::INACTIVE => $this->onInactive($serviceOrder),
            ServiceOrderStatus::CLOSED => $this->onClosed($serviceOrder),
            ServiceOrderStatus::FORPAYMENT => $this->onPayment($serviceOrder),
            ServiceOrderStatus::INPROGRESS => $this->onProgress(),
            default => null
        };
    }

    private function onActive($serviceOrder)
    {
        $serviceOrder?->customer->notify(new ActivatedServiceOrderNotification($this->serviceBill));
    }

    private function onInactive($serviceOrder)
    {
        $serviceOrder?->customer->notify(new ExpiredServiceOrderNotification($this->serviceBill));
    }

    private function onClosed($serviceOrder)
    {
        $serviceOrder?->customer->notify(new ClosedServiceOrderNotification($this->serviceBill));
    }

    private function onPayment($serviceOrder)
    {
        $serviceOrder?->customer->notify(new ForPaymentNotification($this->serviceBill));
    }

    private function onProgress()
    {
    }
}
