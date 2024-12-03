<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentImport\Listeners;

use HalcyonAgile\FilamentImport\Events\ImportFinished;
use HalcyonAgile\FilamentImport\Notifications\ImportFinishedNotification;
use Illuminate\Support\Facades\Notification;

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
