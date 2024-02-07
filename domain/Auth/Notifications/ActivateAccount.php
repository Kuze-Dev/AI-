<?php

declare(strict_types=1);

namespace Domain\Auth\Notifications;

use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ActivateAccount extends Notification implements ShouldQueue
{
    use Queueable;

    public static ?string $route = null;

    public static ?Closure $createUrlCallback = null;

    public static ?Closure $toMailCallback = null;

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $reactivationUrl = $this->reactivationUrl($notifiable);

        return static::$toMailCallback
            ? call_user_func(static::$toMailCallback, $notifiable, $reactivationUrl)
            : $this->buildMailMessage($reactivationUrl);
    }

    protected function buildMailMessage(string $url): MailMessage
    {
        return (new MailMessage())
            ->subject(trans('Activate Account'))
            ->line(trans('Please click the button below to activate your account.'))
            ->action(trans('Activate Account'), $url)
            ->line(trans('If you did not request to activate your account, no further action is required.'));
    }

    protected function reactivationUrl(mixed $notifiable): string
    {
        return static::$createUrlCallback
            ? call_user_func(static::$createUrlCallback, $notifiable)
            : URL::temporarySignedRoute(
                self::$route ?? 'activation.activate',
                now()->addMinutes(config('domain.auth.activation.expire', 60)),
                ['id' => $notifiable->getKey()]
            );
    }

    public static function toRoute(string $route): void
    {
        static::$route = $route;
    }

    public static function createUrlUsing(Closure $callback): void
    {
        static::$createUrlCallback = $callback;
    }

    public static function toMailUsing(Closure $callback): void
    {
        static::$toMailCallback = $callback;
    }
}
