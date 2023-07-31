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
use Illuminate\Support\Facades\Lang;

class RegisterInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Customer $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(Lang::get('Register Invitation'))
            ->line(Lang::get('Please click the button below to register your email address.'))
            ->action(Lang::get('Register Email Address'), self::url($notifiable));
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
