<?php

declare(strict_types=1);

namespace Domain\Admin\Actions;

use Domain\Admin\Models\Admin;
use Spatie\QueueableAction\QueueableAction;

class SendEmailVerificationNotificationToAdminAction
{
    use QueueableAction;

    public function execute(Admin $admin): void
    {
        $admin->sendEmailVerificationNotification();
    }
}
