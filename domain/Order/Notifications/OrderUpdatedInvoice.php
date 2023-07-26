<?php

declare(strict_types=1);

namespace Domain\Order\Notifications;

use Domain\Order\Enums\OrderNotifications;
use Domain\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

class OrderUpdatedInvoice extends Notification implements ShouldQueue
{
    use Queueable;

    private Order $order;
    private OrderNotifications $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, OrderNotifications $type)
    {
        $this->order = $order;
        $this->type = $type;
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

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        if ($this->type === OrderNotifications::CREATED) {
            return (new MailMessage)
                ->subject("Order Being Placed")
                ->from('tenantone@example.com')
                ->view("filament.emails.order.created", [
                    'order' => $this->order,
                    'customer' => $notifiable
                ]);
        } else {
            return (new MailMessage)
                ->from('tenantone@example.com')
                ->view("filament.emails.order.updated", ['order' => $this->order]);
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
