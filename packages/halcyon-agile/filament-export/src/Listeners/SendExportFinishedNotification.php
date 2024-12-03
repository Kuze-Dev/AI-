<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentExport\Listeners;

use HalcyonAgile\FilamentExport\Events\ExportFinishedEvent;
use HalcyonAgile\FilamentExport\Notifications\ExportFinishedNotification;
use Illuminate\Support\Facades\Notification;

class SendExportFinishedNotification
{
    public function handle(ExportFinishedEvent $event): void
    {
        Notification::send(
            $event->notifiable,
            new ExportFinishedNotification($event->filename)
        );
    }
}
