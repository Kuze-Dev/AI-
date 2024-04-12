<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Notifications;

use App\Settings\ServiceSettings;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ServiceBillPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private ServiceSettings $serviceSettings;

    public function __construct(
        private ServiceOrder $serviceOrder,
        private Media $pdf
    ) {
        $this->serviceSettings = app(ServiceSettings::class);
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Service Bill Paid')
            ->replyTo($this->serviceSettings->email_reply_to ?? [])
            ->from($this->serviceSettings->email_sender_name)
            ->greeting("Hi {$this->serviceOrder->customer_full_name},")
            ->line('One of your service bills has been paid!')
            ->action('View Receipt', $this->pdf->original_url)
            ->line('Thank you!');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
