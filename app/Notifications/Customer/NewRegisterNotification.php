<?php

declare(strict_types=1);

namespace App\Notifications\Customer;

use Domain\Customer\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRegisterNotification extends Notification
{
    use Queueable;

    /** Create a new notification instance. */
    public function __construct(private Customer $customer) {}

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
            'type' => 'new_user',
            'message' => "Welcome to our platform {$this->customer->first_name} {$this->customer->last_name}!",
            'button' => 'View your profile',
            'referrence' => $this->customer->email,
        ];
    }
}
