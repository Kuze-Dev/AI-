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
            ->subject(trans('Registration Update'))
            ->line(trans('We regret to inform you that your registration request has been rejected.'))
            ->line(trans('Please consider registering again with a different tier or contact us if you have any questions.'))
            ->action(trans('Register Again'), self::url($notifiable));
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
