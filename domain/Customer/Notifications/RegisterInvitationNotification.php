<?php

declare(strict_types=1);

namespace Domain\Customer\Notifications;

use App\Settings\ECommerceSettings;
use App\Settings\FormSettings;
use App\Settings\SiteSettings;
use Domain\Customer\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(Customer $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Customer $notifiable): MailMessage
    {
        return (new MailMessage())
            ->from(app(FormSettings::class)->sender_email ?? config('mail.from.address'))
            ->subject(trans('Register Invitation'))
            ->line(trans('Join our community to see the products we’re selling. 
                        Click the button below to register your account 
                        and embark on a personalized journey with us!'))
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
