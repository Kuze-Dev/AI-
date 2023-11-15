<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Notifications\RejectedRegistrationNotification;
use Illuminate\Support\Facades\Notification;

class SendRejectedEmailAction
{
    public function execute(string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        Notification::route('mail', $email)->notify(new RejectedRegistrationNotification());

        return true;
    }
}
