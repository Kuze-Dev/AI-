<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentImport\Listeners;

use HalcyonAgile\FilamentImport\Notifications\ImportFailedNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Events\ImportFailed;

readonly class SendImportFailedNotification
{
    public function __construct(
        protected Authenticatable $notifiable
    ) {}

    public function __invoke(ImportFailed $event): void
    {
        if ($event->getException() instanceof ValidationException) {
            $errors = array_values($event->getException()->errors());

            Notification::send(
                $this->notifiable,
                new ImportFailedNotification($errors[0][0])
            );
        }
    }
}
