<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TestNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected readonly string $message,
    ) {}

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
