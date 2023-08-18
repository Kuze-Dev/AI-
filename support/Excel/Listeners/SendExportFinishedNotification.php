<?php

declare(strict_types=1);

namespace Support\Excel\Listeners;

use Illuminate\Support\Facades\Notification;
use Support\Excel\Events\ExportFinished;
use Support\Excel\Notifications\ExportFinished as ExportFinishedNotification;

class SendExportFinishedNotification
{
    public function handle(ExportFinished $event): void
    {
        Notification::send(
            $event->notifiable,
            new ExportFinishedNotification(fileName: $event->fileName)
        );
    }
}
