<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Notifications;

use App\Settings\ServiceSettings;
use App\Settings\SiteSettings;
use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceBillDueDateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private ServiceBill $serviceBill;

    private string $logo;

    private string $title;

    private string $description;

    private string $from;

    private string $url;

    private string $payment_method = 'bank-transfer';

    private array $replyTo;

    private ?string $footer = null;

    public function __construct(ServiceBill $serviceBill)
    {
        $this->serviceBill = $serviceBill;

        $this->payment_method = $serviceBill->serviceOrder->latestPaymentMethod()?->slug ?? 'bank-transfer';

        $this->logo = app(SiteSettings::class)->getLogoUrl();

        $this->title = app(SiteSettings::class)->name;

        $this->description = app(SiteSettings::class)->description;

        $this->url = 'http://'.app(SiteSettings::class)->front_end_domain.'/'.app(ServiceSettings::class)->domain_path_segment.'?reference='.$serviceBill->reference.'&payment_method='.$this->payment_method;

        $this->from = app(ServiceSettings::class)->email_sender_name;

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
        $subject = trans('Payment Reminder');

        return (new MailMessage())
            ->subject($subject)
            ->replyTo($this->replyTo)
            ->from($this->from)
            ->view('filament.emails.serviceBill.due', [
                'subject' => $subject,
                'logo' => $this->logo,
                'title' => $this->title,
                'description' => $this->description,
                'serviceBill' => $this->serviceBill,
                'url' => $this->url,
                'footer' => $this->footer,
            ]);
    }
}
