<?php

declare(strict_types=1);

namespace Domain\Order\Notifications;

use Domain\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminOrderPlacedMail extends Notification implements ShouldQueue
{
    use Queueable;

    private string $url;

    private string $reference;

    /** Create a new notification instance. */
    public function __construct(Order $order, private ?array $cc = [], private ?array $bcc = [])
    {
        $this->url = url("/admin/orders/$order->reference");
        $this->reference = $order->reference;
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
        return (new MailMessage)
            ->greeting('Hi Admin,')
            ->subject("New Order #$this->reference")
            ->line('A new order has been placed by a customer.')
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
