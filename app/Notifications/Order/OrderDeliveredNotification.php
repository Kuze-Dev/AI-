<?php

declare(strict_types=1);

namespace App\Notifications\Order;

use Domain\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderDeliveredNotification extends Notification
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
            'type' => 'order_delivered',
            'message' => "Your order #{$this->order->reference} has been delivered",
            'button' => 'View Order Details',
            'reference' => $this->order->reference,
        ];
    }
}
