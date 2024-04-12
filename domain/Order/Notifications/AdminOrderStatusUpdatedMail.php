<?php

declare(strict_types=1);

namespace Domain\Order\Notifications;

use App\Settings\OrderSettings;
use App\Settings\SiteSettings;
use Domain\Customer\Models\Customer;
use Domain\Order\DataTransferObjects\GuestCustomerData;
use Domain\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminOrderStatusUpdatedMail extends Notification implements ShouldQueue
{
    use Queueable;

    private string $logo;

    private string $title;

    private string $description;

    private string $from;

    private array $replyTo;

    private ?string $footer = null;

    /** Create a new notification instance. */
    public function __construct(private Order $order, private string $status, private ?string $remarks)
    {
        $this->logo = app(SiteSettings::class)->getLogoUrl();
        $this->title = app(SiteSettings::class)->name;
        $this->description = app(SiteSettings::class)->description;

        $this->from = app(OrderSettings::class)->email_sender_name;

        $sanitizedReplyToEmails = $this->sanitizeEmailArray(app(OrderSettings::class)->email_reply_to ?? []);
        $this->replyTo = $sanitizedReplyToEmails;

        $this->footer = app(OrderSettings::class)->email_footer;
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
        $customer = $this->getCustomer($notifiable);

        return (new MailMessage())
            ->subject('Order '.$this->order->reference.' has been '.$this->status)
            ->replyTo($this->replyTo)
            ->from($this->from)
            ->view('filament.emails.order.updated', [
                'logo' => $this->logo,
                'title' => $this->title,
                'description' => $this->description,
                'status' => $this->status,
                'remarks' => $this->remarks,
                'order' => $this->order,
                'customer' => $customer,
                'footer' => $this->footer,
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

    private function getCustomer(object $notifiable): Customer|GuestCustomerData
    {
        if ($notifiable instanceof Customer) {
            return $notifiable;
        } else {
            return new GuestCustomerData(
                first_name: $this->order->customer_first_name,
                last_name: $this->order->customer_last_name,
                mobile: $this->order->customer_mobile,
                email: $this->order->customer_email,
            );
        }
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
