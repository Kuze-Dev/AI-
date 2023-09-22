<?php

declare(strict_types=1);

namespace Domain\Customer\Notifications;

use App\Settings\ECommerceSettings;
use App\Settings\SiteSettings;
use Domain\Customer\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RejectedRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(Customer $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Customer $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(trans('Rejected Registration'))
            ->line(trans('Your request for the wholesaler tier has been rejected. Please try to register again with a different tier or send us an appeal to review.'))
            ->action(trans('Register Email Address'), self::url($notifiable));
    }

    private static function url(Customer $customer): string
    {
        $baseUrl = app(ECommerceSettings::class)->domainWithScheme()
            ?? app(SiteSettings::class)->domainWithScheme();

        return $baseUrl.'/register?'.http_build_query([
            'email' => $customer->email,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
        ]);
    }
}
