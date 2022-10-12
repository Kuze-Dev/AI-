<?php

declare(strict_types=1);

namespace Domain\Admin\Actions;

use Domain\Admin\Models\Admin;

class SendEmailVerificationNotificationToAdminAction
{
    public function execute(Admin $admin): void
    {
        $admin->sendEmailVerificationNotification();
    }
}
