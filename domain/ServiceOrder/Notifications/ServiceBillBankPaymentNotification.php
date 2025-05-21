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

class ServiceBillBankPaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private ServiceSettings $serviceSettings;

    private ?ServiceOrder $serviceOrder;

    private string $url;

    private string $payment_method = 'bank-transfer';

    public function __construct(
        private ServiceBill $serviceBill,
        private string $paymentRemarks
    ) {
        $this->serviceOrder = $this->serviceBill->serviceOrder;

        $this->serviceSettings = app(ServiceSettings::class);

        $this->url = 'http://'.app(SiteSettings::class)->front_end_domain.'/'.app(ServiceSettings::class)->domain_path_segment.
        '?ServiceOrder='.$this->serviceOrder?->reference.'&ServiceBill='.$this->serviceBill->reference.
        '&payment_method='.$this->payment_method;

    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payment {$this->paymentRemarks}")
            ->replyTo($this->serviceSettings->email_reply_to ?? [])
            ->from($this->serviceSettings->email_sender_name)
            ->greeting("Hi {$this->serviceOrder?->customer_full_name},")
            ->line("Your proof of payment for Service Bill #{$this->serviceBill->reference} has been {$this->paymentRemarks}")
            ->action('View Service Bill', $this->url)
            ->line('Thank you!');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
