<?php

declare(strict_types=1);

namespace App\Notifications\Api;

use App\Enums\Api\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GeneralApiNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected readonly string $message,
        protected readonly NotificationType $type = NotificationType::INFORMATION,
    ) {
    }

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
        ];
    }
}
