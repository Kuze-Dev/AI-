<?php

declare(strict_types=1);

namespace Domain\Order\Notifications;

use App\Settings\OrderSettings;
use App\Settings\SiteSettings;
use Domain\Admin\Models\Admin;
use Domain\Customer\Models\Customer;
use Domain\Order\DataTransferObjects\GuestCustomerData;
use Domain\Order\DataTransferObjects\GuestPreparedOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderAddress;
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

    private OrderAddress $shippingAddress;

    private ShippingMethod $shippingMethod;

    private string $logo;

    private string $title;

    private string $description;

    private string $from;

    private array $replyTo;

    private ?string $footer = null;

    /** Create a new notification instance. */
    public function __construct(Order $order, PreparedOrderData|GuestPreparedOrderData $preparedOrderData)
    {
        $this->order = $order;

        $this->shippingAddress = $order->shippingAddress;

        $this->shippingMethod = $preparedOrderData->shippingMethod;

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
        $admin = Admin::first();

        $customer = $this->getCustomer($notifiable);

        $address = $this->getAddress();

        return (new MailMessage())
            ->subject('Order Being Placed')
            ->replyTo($this->replyTo)
            ->from($this->from)
            ->view('filament.emails.order.created', [
                'logo' => $this->logo,
                'title' => $this->title,
                'description' => $this->description,
                'timezone' => $admin?->timezone,
                'order' => $this->order,
                'customer' => $customer,
                'address' => $address,
                'paymentMethod' => $this->order->payments->first() ?
                    $this->order->payments->first()->paymentMethod : null,
                'shippingMethod' => $this->shippingMethod,
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

    private function getAddress(): string
    {
        $address = Arr::join(
            array_filter([
                $this->shippingAddress->address_line_1,
                $this->shippingAddress->city,
                $this->shippingAddress->state,
                $this->shippingAddress->zip_code,
                $this->shippingAddress->country,
            ]),
            ', '
        );

        return $address;
    }

    private function sanitizeEmailArray(array $emailArray): array
    {
        $sanitizedEmails = [];

        foreach ($emailArray as $email) {
            $email = trim($email);

            if (! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $sanitizedEmails[] = $email;
            }
        }

        return $sanitizedEmails;
    }
}
