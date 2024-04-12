<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Listeners;

use App\Settings\ServiceSettings;
use DateTimeZone;
use Domain\ServiceOrder\Actions\ComputeServiceBillingCycleAction;
use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Events\AdminServiceOrderStatusUpdatedEvent;
use Domain\ServiceOrder\Jobs\NotifyCustomerLatestServiceBillJob;
use Domain\ServiceOrder\Jobs\NotifyCustomerServiceOrderStatusJob;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Notifications\ChangeByAdminNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AdminServiceOrderStatusUpdatedListener
{
    public function __construct(
        private ServiceOrder $serviceOrder,
        private readonly CreateServiceBillAction $createServiceBillAction,
        private readonly ComputeServiceBillingCycleAction $computeServiceBillingCycleAction,
        private readonly ServiceSettings $serviceSettings,
        private bool $shouldNotifyCustomer = false,
    ) {
    }

    public function handle(AdminServiceOrderStatusUpdatedEvent $event): void
    {
        $this->serviceOrder = $event->serviceOrder;

        $this->shouldNotifyCustomer = $event->shouldNotifyCustomer;

        $this->prepareServiceBill();

        $this->notifyCustomer();

        $this->notifyAdmin();
    }

    private function onServiceOrderForPayment(): ServiceBill
    {
        return $this->createServiceBillAction
            ->execute(ServiceBillData::initialFromServiceOrder($this->serviceOrder));
    }

    private function onServiceOrderActive(): ServiceBill
    {
        if ($this->serviceOrder->pay_upfront) {
            throw new ModelNotFoundException(trans('No service bill found'));
        }

        /** @var \Domain\Admin\Models\Admin $admin */
        $admin = Auth::user();

        return $this->createServiceBillAction
            ->execute(
                ServiceBillData::subsequentFromServiceOrderWithAssignedDates(
                    $this->serviceOrder,
                    $this->computeServiceBillingCycleAction
                        ->execute(
                            $this->serviceOrder,
                            now()->timezone(new DateTimeZone($admin->timezone))
                        )
                )
            );
    }

    private function prepareServiceBill(): void
    {
        if (filled($this->serviceOrder->serviceBills)) {
            return;
        }

        /** @var \Domain\ServiceOrder\Models\ServiceBill|null $serviceBill */
        $serviceBill = match ($this->serviceOrder->status->value) {
            'for_payment' => $this->onServiceOrderForPayment(),
            'active', => $this->onServiceOrderActive(),
            default => null
        };

        $shouldNotifyCustomer = $serviceBill instanceof ServiceBill &&
            $this->shouldNotifyCustomer;

        if (! $shouldNotifyCustomer) {
            return;
        }

        NotifyCustomerLatestServiceBillJob::dispatch($this->serviceOrder);
    }

    private function notifyCustomer(): void
    {
        if (! $this->shouldNotifyCustomer) {
            return;
        }

        NotifyCustomerServiceOrderStatusJob::dispatch($this->serviceOrder);
    }

    private function notifyAdmin(): void
    {
        if (
            ! $this->serviceSettings->admin_should_receive &&
            empty($this->serviceSettings->admin_main_receiver)
        ) {
            return;
        }

        Notification::route(
            'mail',
            $this->serviceSettings->admin_main_receiver
        )->notify(
            new ChangeByAdminNotification(
                $this->serviceOrder,
                $this->serviceOrder->status->value
            )
        );
    }
}
