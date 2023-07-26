<?php

declare(strict_types=1);

namespace Domain\Order\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

class OrderUpdatedInvoice extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
        return (new MailMessage)
            ->from('tenantone@example.com')
            ->view("filament.emails.order.updated");
        //     ->subject('Order Being Processed')
        //     ->greeting("Order Being Processed #XWEF24VA")
        //     ->line('Hi <User>, your item(s) in order <OrderNumber> has been forwarded 
        // to our delivery partner and on its way to <ShippingAddress>. Track your order below or key 
        // in the tracking number below on your designated courier website ')
        //     ->line('Please note that it may take up to 24 - 48 hours for tracking 
        // information to be available/updated');
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
