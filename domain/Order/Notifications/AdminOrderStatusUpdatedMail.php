<?php

declare(strict_types=1);

namespace Domain\Order\Notifications;

use App\Settings\SiteSettings;
use Domain\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminOrderStatusUpdatedMail extends Notification implements ShouldQueue
{
    use Queueable;

    private Order $order;
    private string $status;
    private ?string $remarks;
    private string $logo;
    private string $title;
    private string $description;

    /** Create a new notification instance. */
    public function __construct(Order $order, string $status, ?string $remarks)
    {
        $this->order = $order;
        $this->status = $status;
        $this->remarks = $remarks;

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

        return (new MailMessage())
            ->subject('Order ' .  $this->order->reference . ' has been ' . $this->status)
            ->from('tenantone@example.com')
            ->view('filament.emails.order.updated', [
                'logo' => $this->logo,
                'title' => $this->title,
                'description' => $this->description,
                'status' => $this->status,
                'remarks' => $this->remarks,
                'order' => $this->order,
                'customer' => $notifiable,
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
