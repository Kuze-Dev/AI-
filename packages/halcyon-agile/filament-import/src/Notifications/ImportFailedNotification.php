<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentImport\Notifications;

use Exception;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $error
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /** @throws Exception */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->danger()
            ->title('Import Failed')
            ->body($this->error)
            ->icon('heroicon-o-exclamation-circle')
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->greeting('Import Failed')
            ->line($this->error);
    }

    //    /**
    //     * horizon compatible tags
    //     */
    //    public function tags(): array
    //    {
    //        return  [];
    //    }
}
