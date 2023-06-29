<?php

declare(strict_types=1);

namespace Support\Excel\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Events\ImportFailed;
use Support\Excel\Notifications\ImportFailed as ImportFailedNotification;

class SendImportFailedNotification
{
    public function __construct(
        protected Model $notifiable
    ) {
    }

    public function handle(ImportFailed $event): void
    {
        if ($event->getException() instanceof ValidationException) {
            Notification::send(
                $this->notifiable,
                new ImportFailedNotification($event->getException()->errors()[0][0])
            );
        }
    }
}
