<?php

declare(strict_types=1);

namespace Domain\Admin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends \Illuminate\Auth\Notifications\VerifyEmail implements ShouldQueue
{
    use Queueable;

    protected function buildMailMessage($url)
    {
        return (new MailMessage())
            ->subject(trans('Please Verify your email address for your '.config('app.name').' website login'))
            ->line(trans('Please click the button below to verify your email address.'))
            ->action(trans('Verify Email Address'), $url)
            ->line(trans('If you did not create an account, no further action is required.'));
    }
}
