<?php

declare(strict_types=1);

namespace App\Notifications\Basic;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuccessBasicNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected readonly string $message,
    ) {
    }

    /** @return non-empty-string */
    public static function databaseType(): string
    {
        return 'success';
    }

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
