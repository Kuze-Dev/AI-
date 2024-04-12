<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Notifications;

use App\Settings\ServiceSettings;
use App\Settings\SiteSettings;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceBillNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private ?ServiceOrder $serviceOrder;

    private string $logo;

    private string $title;

    private string $description;

    private string $from;

    private string $url;

    private string $payment_method = 'bank-transfer';

    private array $replyTo;

    private ?string $footer = null;

    public function __construct(private ServiceBill $serviceBill)
    {
        $this->serviceOrder = $this->serviceBill->serviceOrder;

        $this->payment_method = $this->serviceBill->serviceOrder?->latestPaymentMethod()?->slug ?? 'bank-transfer';

        $this->logo = app(SiteSettings::class)->getLogoUrl();

        $this->title = app(SiteSettings::class)->name;

        $this->description = app(SiteSettings::class)->description;

        $this->from = app(ServiceSettings::class)->email_sender_name;

        $this->url = 'http://'.app(SiteSettings::class)->front_end_domain.'/'.app(ServiceSettings::class)->domain_path_segment.
                     '?ServiceOrder='.$this->serviceOrder?->reference.'&ServiceBill='.$this->serviceBill->reference.
                     '&payment_method='.$this->payment_method;

        $this->replyTo = app(ServiceSettings::class)->email_reply_to ?? [];

        $this->footer = app(ServiceSettings::class)->email_footer;
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->replyTo($this->replyTo)
            ->from($this->from)
            ->subject('New Service Bill')
            ->view('filament.emails.serviceBill.created', [
                'logo' => $this->logo,
                'title' => $this->title,
                'description' => $this->description,
                'serviceBill' => $this->serviceBill,
                'url' => $this->url,
                'footer' => $this->footer,
            ]);
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
