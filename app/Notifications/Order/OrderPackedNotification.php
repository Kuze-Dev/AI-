<?php

declare(strict_types=1);

namespace App\Notifications\Order;

use Domain\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderPackedNotification extends Notification
{
    use Queueable;

    /** Create a new notification instance. */
    public function __construct(private Order $order) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_packed',
            'message' => "Your order #{$this->order->reference} has been packed",
            'button' => 'View Order Details',
            'reference' => $this->order->reference,
        ];
    }
}
