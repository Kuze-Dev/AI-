<?php

declare(strict_types=1);

namespace Support\Excel\Notifications;

use Exception;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ExportFinished extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $fileName
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /** @throws Exception */
    public function toDatabase(object $notifiable): array
    {
        /** @var string */
        $body = Str::replace(':value', $this->fileName, 'Your file [:value] is ready for download.');

        return FilamentNotification::make()
            ->success()
            ->title('Export finished')
            ->body($body)
            ->icon('heroicon-o-download')
            ->actions([
                Action::make('download')
                    ->button()
                    ->url($this->downloadUrl()),
            ])
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->greeting('Export finished')
            ->line(Str::replace(':value', $this->fileName, 'Your file [:value] is ready for download.'))
            ->action('Download', $this->downloadUrl());
    }

    protected function downloadUrl(): string
    {
        return URL::temporarySignedRoute(
            'filament-excel.download-export',
            now()->minutes(config('support.excel.export_expires_in_minute')),
            ['path' => $this->fileName]
        );
    }
}
