<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Notifications;

use App\Settings\ServiceSettings;
use App\Settings\SiteSettings;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClosedServiceOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $logo;

    private string $title;

    private string $description;

    private string $from;

    private array $replyTo;

    private ?string $footer = null;

    public function __construct(private ServiceOrder $serviceOrder)
    {
        $this->logo = app(SiteSettings::class)->getLogoUrl();

        $this->title = app(SiteSettings::class)->name;

        $this->description = app(SiteSettings::class)->description;

        $this->from = app(ServiceSettings::class)->email_sender_name;

        $this->replyTo = app(ServiceSettings::class)->email_reply_to ?? [];

        $this->footer = app(ServiceSettings::class)->email_footer;
    }

    /** @return array<int, string>*/
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Service canceled')
            ->replyTo($this->replyTo)
            ->from($this->from)
            ->view('filament.emails.serviceOrder.closed', [
                'logo' => $this->logo,
                'title' => $this->title,
                'description' => $this->description,
                'serviceOrder' => $this->serviceOrder,
                'footer' => $this->footer,
            ]);
    }

    /** @return array<string, mixed>*/
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
