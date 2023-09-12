<?php

declare(strict_types=1);

namespace Domain\Order\Notifications;

use Domain\Admin\Models\Admin;
use Filament\Notifications\Notification;

class OrderFailedNotifyAdmin
{
    public function execute(string $body): void
    {
        /** @var Admin $admin */
        $admin = Admin::first();

        $admin->notify(
            Notification::make()
                ->title("Customer can't placed an order")
                ->body($body)
                ->toDatabase(),
        );
    }
}
