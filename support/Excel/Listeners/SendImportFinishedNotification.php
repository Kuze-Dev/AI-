<?php

declare(strict_types=1);

namespace Support\Excel\Listeners;

use Illuminate\Support\Facades\Notification;
use Support\Excel\Events\ImportFinished;
use Support\Excel\Notifications\ImportFinished as ImportFinishedNotification;

class SendImportFinishedNotification
{
    public function handle(ImportFinished $event): void
    {
        Notification::send(
            $event->notifiable,
            new ImportFinishedNotification()
        );
    }
}
