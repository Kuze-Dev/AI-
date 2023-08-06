<?php

declare(strict_types=1);

namespace Domain\Order\Notifications;

use App\Settings\SiteSettings;
use Domain\Address\Models\Address;
use Domain\Admin\Models\Admin;
use Domain\Order\Models\Order;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;

class OrderPlacedMail extends Notification implements ShouldQueue
{
    use Queueable;

    private Order $order;
    private Address $shippingAddress;
    private ShippingMethod $shippingMethod;
    private string $logo;
    private string $title;
    private string $description;

    /** Create a new notification instance. */
    public function __construct(Order $order, Address $shippingAddress, ShippingMethod $shippingMethod)
    {
        $this->order = $order;

        /** @var \Domain\Address\Models\Address $shippingAddress */
        $this->shippingAddress = $shippingAddress;
        $this->shippingMethod = $shippingMethod;

        $this->logo = app(SiteSettings::class)->getLogoUrl();
        $this->title = app(SiteSettings::class)->name;
        $this->description = app(SiteSettings::class)->description;
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

        $this->shippingAddress->load('state.country');

        $address = Arr::join(
            array_filter([
                $this->shippingAddress->address_line_1,
                $this->shippingAddress->state && $this->shippingAddress->state->country ?
                    $this->shippingAddress->state->country->name : null,
                $this->shippingAddress->state ? $this->shippingAddress->state->name : null,
                $this->shippingAddress->zip_code,
                $this->shippingAddress->city,
            ]),
            ', '
        );

        return (new MailMessage())
            ->subject('Order Being Placed')
            ->from('tenantone@example.com')
            ->view('filament.emails.order.created', [
                'logo' => $this->logo,
                'title' => $this->title,
                'description' => $this->description,
                'timezone' => $admin?->timezone,
                'order' => $this->order,
                'customer' => $notifiable,
                'address' => $address,
                'paymentMethod' => $this->order->payments->first() ?
                    $this->order->payments->first()->paymentMethod : null,
                'shippingMethod' => $this->shippingMethod,
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
}
