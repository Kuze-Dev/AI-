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
        return FilamentNotification::make()
            ->success()
            ->title('Export finished')
            ->body('Your file [ '.$this->fileName.' ] is ready for download.')
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
            ->line('Your file [ '.$this->fileName.' ] is ready for download.')
            ->action('Download', $this->downloadUrl());
    }

    protected function downloadUrl(): string
    {
        if (tenancy()->initialized) {
            /** @var \Domain\Tenant\Models\Tenant */
            $tenant = tenancy()->tenant;

            URL::formatHostUsing(function () use ($tenant) {

                return app()->environment('local') ? 'http://' : 'https://'.$tenant->domains->first()?->domain;
            });
        }

        return URL::temporarySignedRoute(
            'filament-excel.download-export',
            now()->minutes(config('support.excel.export_expires_in_minute')),
            ['path' => $this->fileName]
        );
    }
}
