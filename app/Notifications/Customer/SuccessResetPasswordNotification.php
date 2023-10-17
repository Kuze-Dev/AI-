<?php

declare(strict_types=1);

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuccessResetPasswordNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reset_password_success',
            'message' => 'You have successfully reset your password.',
        ];
    }
}
