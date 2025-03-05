<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Notifications;

use App\Settings\ServiceSettings;
use App\Settings\SiteSettings;
use Domain\Admin\Models\Admin;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpiredServiceOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private ?ServiceOrder $serviceOrder;

    private string $logo;

    private string $title;

    private string $description;

    private string $from;

    private string $url;

    private array $replyTo;

    private string $payment_method;

    private string $footer;

    /** Create a new notification instance. */
    public function __construct(private ServiceBill $serviceBill)
    {
        $this->serviceOrder = $this->serviceBill->serviceOrder;
        $this->payment_method = $this->serviceBill->serviceOrder?->latestPaymentMethod()?->slug ?? 'bank-transfer';

        $siteSettings = app(SiteSettings::class);
        $serviceSettings = app(ServiceSettings::class);

        $this->logo = $siteSettings->getLogoUrl();
        $this->title = $siteSettings->name;
        $this->description = $siteSettings->description;
        $this->url = 'http://' . $siteSettings->front_end_domain . '/' . $serviceSettings->domain_path_segment .
                    '?ServiceOrder=' . $this->serviceOrder?->reference . '&ServiceBill=' . $this->serviceBill->reference .
                    '&payment_method=' . $this->payment_method;
        $this->from = $serviceSettings->email_sender_name;

        $sanitizedReplyToEmails = $this->sanitizeEmailArray($serviceSettings->email_reply_to ?? []);
        $this->replyTo = $sanitizedReplyToEmails;

        $this->footer = $serviceSettings->email_footer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /** Get the mail representation of the notification. */
    public function toMail(object $notifiable): MailMessage
    {
        $admin = Admin::first();

        return (new MailMessage())
            ->subject('Service Expired')
            ->replyTo($this->replyTo)
            ->from($this->from)
            ->view('filament.emails.serviceOrder.expired', [
                'logo' => $this->logo,
                'title' => $this->title,
                'description' => $this->description,
                'timezone' => $admin?->timezone,
                'serviceBill' => $this->serviceBill,
                'footer' => $this->footer,
                'url' => $this->url,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }

    private function sanitizeEmailArray(array $emailArray): array
    {
        $sanitizedEmails = [];

        foreach ($emailArray as $email) {
            $email = trim((string) $email);

            if (! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $sanitizedEmails[] = $email;
            }
        }

        return $sanitizedEmails;
    }
}
