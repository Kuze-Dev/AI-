<?php

declare(strict_types=1);

namespace Domain\Customer\Notifications;

use App\Settings\CustomerSettings;
use App\Settings\ECommerceSettings;
use App\Settings\FormSettings;
use App\Settings\SiteSettings;
use Domain\Customer\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class RegisterInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(Customer $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Customer $notifiable): MailMessage
    {

        View::flushFinderCache();

        return (new MailMessage())
            ->from(app(FormSettings::class)->sender_email ?? config('mail.from.address'))
            ->subject(trans('Register Invitation'))
            ->greeting(new HtmlString(app(CustomerSettings::class)->customer_register_invitation_greetings ?? '<b>Hello</b>'))
            ->line(new HtmlString(
                trans(
                    app(CustomerSettings::class)->customer_register_invitation_body,
                    ['site' => app(SiteSettings::class)->name]
                )
            )
            )
            ->salutation(new HtmlString(
                trans(
                    app(CustomerSettings::class)->customer_register_invitation_salutation ?? '',
                    ['site' => app(SiteSettings::class)->name]
                )
            )
            ) // Custom salutation
            ->action(trans('Register Email Address'), self::url($notifiable));
    }

    private static function url(Customer $customer): string
    {
        $baseUrl = app(ECommerceSettings::class)->domainWithScheme()
            ?? app(SiteSettings::class)->domainWithScheme();

        return $baseUrl.'/register?'.http_build_query([
            'email' => $customer->email,
            'username' => $customer->username,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'mobile' => $customer->mobile,
            'gender' => $customer->gender?->value,
            'birth_date' => $customer->birth_date?->toDateString(),
            'invited' => $customer->cuid,
        ]);
    }
}
