<?php

declare(strict_types=1);

namespace Domain\Customer\Notifications;

use App\Settings\SiteSettings;
use Domain\Customer\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovedRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(Customer $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Customer $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('Approved Registration'))
            ->line(trans('Congratulations! Your request for this tier has been approved!'))
            ->line(trans('You now have access to exclusive benefits and discounts.'))
            ->action(trans('Visit Our Website'), url(app(SiteSettings::class)->domainWithScheme()))
            ->line(trans('If you have any questions or need assistance, please contact our support team.'));
    }
}
