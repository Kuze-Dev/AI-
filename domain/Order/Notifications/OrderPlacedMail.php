<?php

declare(strict_types=1);

namespace Domain\Order\Notifications;

use Domain\Address\Models\Address;
use Domain\Admin\Models\Admin;
use Domain\Order\Enums\OrderMailStatus;
use Domain\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Mime\Email;

class OrderPlacedMail extends Notification implements ShouldQueue
{
    use Queueable;

    private Order $order;
    private Address $shippingAddress;
    private OrderMailStatus $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, Address $shippingAddress, OrderMailStatus $type)
    {
        $this->order = $order;
        $this->type = $type;
        $this->shippingAddress = $shippingAddress;
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
        $admin = Admin::first();

        $address = Arr::join(
            array_filter([
                $this->shippingAddress->address_line_1,
                $this->shippingAddress->state->country->name,
                $this->shippingAddress->state->name,
                $this->shippingAddress->zip_code,
                $this->shippingAddress->city,
            ]),
            ', '
        );

        if ($this->type === OrderMailStatus::CREATED) {
            return (new MailMessage)
                ->subject("Order Being Placed")
                ->from('tenantone@example.com')
                ->view("filament.emails.order.created", [
                    'timezone' => $admin?->timezone,
                    'order' => $this->order,
                    'customer' => $notifiable,
                    'address' => $address,
                    'paymentMethod' => $this->order->payments->first()->paymentMethod
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
