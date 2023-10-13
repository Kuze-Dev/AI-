<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Notifications;

use App\Settings\ServiceSettings;
use App\Settings\SiteSettings;
use Domain\Address\Models\Address;
use Domain\Admin\Models\Admin;
use Domain\Order\Models\Order;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;

class PlaceServiceOrderMail extends Notification implements ShouldQueue
{
    use Queueable;

    private ServiceBill $serviceBill;
    private ShippingMethod $shippingMethod;
    private string $logo;
    private string $title;
    private string $description;
    private string $from;
    private string $url = "http://tenant.saas-platform.test/admin/service-orders";
    private array $replyTo;
    private ?string $footer = null;

    /** Create a new notification instance. */
    public function __construct(ServiceBill $serviceBill)
    {
        $this->serviceBill = $serviceBill;

        $this->logo = app(SiteSettings::class)->getLogoUrl();
        $this->title = app(SiteSettings::class)->name;
        $this->description = app(SiteSettings::class)->description;

        $this->from = app(ServiceSettings::class)->email_sender_name;

        $sanitizedReplyToEmails = $this->sanitizeEmailArray(app(ServiceSettings::class)->email_reply_to ?? []);
        $this->replyTo = $sanitizedReplyToEmails;

        $this->footer = app(ServiceSettings::class)->email_footer;
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
            ->subject('Order Being Placed')
            ->replyTo($this->replyTo)
            ->from($this->from)
            ->view('filament.emails.serviceOrder.created', [
                'logo' => $this->logo,
                'title' => $this->title,
                'description' => $this->description,
                'timezone' => $admin?->timezone,
                'serviceBill' => $this->serviceBill,
                'customer' => $notifiable,
                'footer' => $this->footer,
                'url' => $this->url,
            ])
            ;
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
            $email = trim($email);

            if ( ! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $sanitizedEmails[] = $email;
            }
        }

        return $sanitizedEmails;
    }
}
