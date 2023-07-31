<?php

declare(strict_types=1);

namespace App\Notifications\Order;

use Domain\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderBankPaymentNotification extends Notification
{
    use Queueable;
    private Order $order;
    private string $paymentRemarks;

    /** Create a new notification instance. */
    public function __construct(Order $order, string $paymentRemarks)
    {
        $this->order = $order;
        $this->paymentRemarks = $paymentRemarks;
    }

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
            'type' => 'bank_transfer_payment',
            'message' => "Your bank transfer for order #{$this->order->reference} has been {$this->paymentRemarks}",
            'button' => 'View Order Details',
            'reference' => $this->order->reference,
        ];
    }
}
