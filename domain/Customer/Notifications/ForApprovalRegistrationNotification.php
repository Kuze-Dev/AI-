<?php

declare(strict_types=1);

namespace Domain\Customer\Notifications;

use App\Settings\SiteSettings;
use Domain\Customer\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForApprovalRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(Customer $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Customer $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('For Approval Registration'))
            ->line(trans('Your request for the wholesaler tier is now being reviewed!'))
            ->line(trans('Please wait for our team to process your application.'))
            ->line(trans('We will notify you once your registration is approved.'))
            ->line(trans('Thank you for choosing our platform for your business needs.'))
            ->action(trans('Please visit our website'), url(app(SiteSettings::class)->domainWithScheme()))
            ->line(trans('If you have any questions or need assistance, please contact our support team.'));
    }
}
