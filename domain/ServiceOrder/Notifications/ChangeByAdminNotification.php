<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Notifications;

use App\Settings\ServiceSettings;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ChangeByAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private ?array $cc = [];

    private ?array $bcc = [];

    private string $url;

    private string $reference;

    private string $from;

    /** Create a new notification instance. */
    public function __construct(ServiceOrder $serviceOrder, private string $status)
    {
        $this->cc = app(ServiceSettings::class)->admin_cc;
        $this->bcc = app(ServiceSettings::class)->admin_bcc;
        $this->url = url("/admin/service-orders/$serviceOrder->reference");
        $this->reference = $serviceOrder->reference;
        $this->from = app(ServiceSettings::class)->email_sender_name;
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
        $user = Auth::user();

        return (new MailMessage())
            ->greeting('Hi Admin,')
            ->from($this->from)
            ->subject("Service order #$this->reference")
            ->line("{$user?->first_name} {$user?->last_name} has updated the status of this service order to '{$this->status}'")
            ->action('View Order', $this->url)
            ->cc($this->cc ?? [])
            ->bcc($this->bcc ?? []);
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
}
