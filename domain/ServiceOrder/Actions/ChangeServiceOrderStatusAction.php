<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Notifications\ActivatedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ClosedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ExpiredServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ForPaymentNotification;

class ChangeServiceOrderStatusAction
{
    public function __construct(
        private CreateServiceBillAction $createServiceBillAction,
    ) {
    }

    private ?ServiceBill $serviceBill;

    public function execute(ServiceOrder $serviceOrder, bool $shouldSendEmail): void
    {
        $this->serviceBill = $serviceOrder->latestServiceBill();

        match ($serviceOrder->status) {
            ServiceOrderStatus::ACTIVE => $this->onActive($serviceOrder, $shouldSendEmail),
            ServiceOrderStatus::INACTIVE => $this->onInactive($serviceOrder, $shouldSendEmail),
            ServiceOrderStatus::CLOSED => $this->onClosed($serviceOrder, $shouldSendEmail),
            ServiceOrderStatus::FORPAYMENT => $this->onPayment($serviceOrder, $shouldSendEmail),
            ServiceOrderStatus::INPROGRESS => $this->onProgress($serviceOrder, $shouldSendEmail),
            default => null
        };
    }

    private function onActive(ServiceOrder $serviceOrder, bool $shouldSendEmail): void
    {
        if ($shouldSendEmail) {
            if ($serviceOrder->customer && $this->serviceBill) {
                $serviceOrder->customer->notify(new ActivatedServiceOrderNotification($this->serviceBill));
            }
        }
    }

    private function onInactive(ServiceOrder $serviceOrder, bool $shouldSendEmail): void
    {
        if ($shouldSendEmail) {
            if ($serviceOrder->customer && $this->serviceBill) {
                $serviceOrder->customer->notify(new ExpiredServiceOrderNotification($this->serviceBill));
            }
        }
    }

    private function onClosed(ServiceOrder $serviceOrder, bool $shouldSendEmail): void
    {
        if ($shouldSendEmail) {
            if ($serviceOrder->customer && $this->serviceBill) {
                $serviceOrder->customer->notify(new ClosedServiceOrderNotification($this->serviceBill));
            }
        }
    }

    private function onPayment(ServiceOrder $serviceOrder, bool $shouldSendEmail): void
    {
        $serviceBill = $this->serviceBill;

        if (is_null($serviceBill)) {
            $serviceBill = $this->createServiceBillAction->execute(
                ServiceBillData::fromCreatedServiceOrder($serviceOrder->toArray())
            );
        }

        if ($shouldSendEmail) {
            if ($serviceOrder->customer) {
                $serviceOrder->customer->notify(new ForPaymentNotification($serviceBill));
            }
        }
    }

    private function onProgress(ServiceOrder $serviceOrder, bool $shouldSendEmail): void
    {
        $serviceBill = $this->serviceBill;

        if (is_null($serviceBill)) {
            $serviceBill = $this->createServiceBillAction->execute(
                ServiceBillData::fromCreatedServiceOrder($serviceOrder->toArray())
            );
        }

        if ($shouldSendEmail) {
            if ($serviceOrder->customer) {
                $serviceOrder->customer->notify(new ForPaymentNotification($serviceBill));
            }
        }
    }
}
