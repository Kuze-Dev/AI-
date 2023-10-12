<?php

declare(strict_types=1);

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuccessUpdatePasswordNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'update_password_success',
            'message' => 'YYou have successfully update your password.',
        ];
    }
}
