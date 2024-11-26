<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentExport\Notifications;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use HalcyonAgile\FilamentExport\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ExportFinishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $fileName)
    {
    }

    public function via(Model $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @throws \Exception
     */
    public function toDatabase(Model $notifiable): array
    {
        return FilamentNotification::make()
            ->success()
            ->title('Export finished')
            ->body($this->line())
            ->icon('heroicon-o-download')
            ->actions([
                Action::make('download')
                    ->translateLabel()
                    ->button()
                    ->url($this->downloadUrl()),
            ])
            ->getDatabaseMessage();
    }

    public function toMail(Model $notifiable): MailMessage
    {
        return (new MailMessage())
            ->greeting(trans('Export finished'))
            ->line($this->line())
            ->action(trans('Download'), $this->downloadUrl());
    }

    private function line(): string
    {
        return trans('Your file [:filename] is ready for download.', ['filename' => $this->fileName]);
    }

    protected function downloadUrl(): string
    {
        return Storage::disk(config('filament-export.temporary_files.disk'))
            ->temporaryUrl(
                Helpers::fullPath($this->fileName),
                Helpers::now()->minutes(config('filament-export.expires_in_minute')),
                ['ResponseContentDisposition' => 'attachment']
            );
    }

    //    /**
    //     * horizon compatible tags
    //     */
    //    public function tags(): array
    //    {
    //        return  [];
    //    }
}
