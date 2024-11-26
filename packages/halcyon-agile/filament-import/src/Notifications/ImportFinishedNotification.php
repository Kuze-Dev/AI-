<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentImport\Notifications;

use Exception;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportFinishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /** @throws Exception */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->success()
            ->title('Import finished')
            ->icon('heroicon-o-check')
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->greeting('Import finished');
    }

    //    /**
    //     * horizon compatible tags
    //     */
    //    public function tags(): array
    //    {
    //        return  [];
    //    }
}
