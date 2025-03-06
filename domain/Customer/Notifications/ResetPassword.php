<?php

declare(strict_types=1);

namespace Domain\Customer\Notifications;

use App\Settings\FormSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends \Illuminate\Auth\Notifications\ResetPassword implements ShouldQueue
{
    use Queueable;

    #[\Override]
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->from(app(FormSettings::class)->sender_email ?? config('mail.from.address'))
            ->subject(trans('Reset Password Notification'))
            ->line(trans('You are receiving this email because we received a password reset request for your account.'))
            ->action(trans('Reset Password'), $url)
            ->line(trans('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(trans('If you did not request a password reset, no further action is required.'));
    }
}
