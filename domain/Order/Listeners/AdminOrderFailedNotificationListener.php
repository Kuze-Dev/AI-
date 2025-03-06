<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use Domain\Admin\Models\Admin;
use Domain\Order\Events\AdminOrderFailedNotificationEvent;
use Filament\Notifications\Notification;

class AdminOrderFailedNotificationListener
{
    /**
     * Handle the event.
     */
    public function handle(AdminOrderFailedNotificationEvent $event): void
    {
        $body = $event->body;
        $permission = $event->permission;

        // superadmin
        /** @var Admin $admin */
        $admin = Admin::first();

        $admin->notify(
            Notification::make()
                ->title("Customer can't placed an order")
                ->body($body)
                ->toDatabase(),
        );

        // admins
        $adminsWithPermission = Admin::whereHas('roles.permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->get();

        $adminsWithPermission->each(function ($adminWithPermission) use ($body) {
            $adminWithPermission->notify(Notification::make()
                ->title("Customer can't placed an order")
                ->body($body)
                ->toDatabase());
        });
    }
}
