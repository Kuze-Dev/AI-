<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Listeners;

use App\Settings\ServiceSettings;
use DateTimeZone;
use Domain\ServiceOrder\Actions\ComputeServiceBillingCycleAction;
use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Events\AdminServiceOrderStatusUpdatedEvent;
use Domain\ServiceOrder\Jobs\NotifyCustomerServiceOrderStatusJob;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Notifications\ChangeByAdminNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AdminServiceOrderStatusUpdatedListener
{
    public function __construct(
        private ServiceOrder $serviceOrder,
        private CreateServiceBillAction $createServiceBillAction,
        private ComputeServiceBillingCycleAction $computeServiceBillingCycleAction,
        private ServiceSettings $serviceSettings,
        private bool $shouldNotifyCustomer = false,
    ) {
    }

    public function handle(
        AdminServiceOrderStatusUpdatedEvent $event
    ): void {

        $this->serviceOrder = $event->serviceOrder;

        $this->shouldNotifyCustomer = $event->shouldNotifyCustomer;

        $this->prepareServiceBill();

        $this->notifyCustomer();

        $this->notifyAdmin();
    }

    private function prepareServiceBill(): void
    {
        switch ($this->serviceOrder->status) {
            case ServiceOrderStatus::FORPAYMENT:
                $this->createServiceBillAction
                    ->execute(ServiceBillData::initialFromServiceOrder($this->serviceOrder));
                break;

            case ServiceOrderStatus::ACTIVE:
                /** @var \Domain\Admin\Models\Admin $admin */
                $admin = Auth::user();

                $this->createServiceBillAction
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
                break;

            default:
                break;
        }
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
            $this->serviceSettings->admin_should_receive &&
            filled($this->serviceSettings->admin_main_receiver)
        ) {
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
}
