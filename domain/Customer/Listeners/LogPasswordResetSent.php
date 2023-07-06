<?php

declare(strict_types=1);

namespace Domain\Customer\Listeners;

use Domain\Customer\Events\PasswordResetSent;
use Spatie\Activitylog\ActivityLogger;

class LogPasswordResetSent
{
    public function handle(PasswordResetSent $event): void
    {
        app(ActivityLogger::class)
            ->performedOn($event->user)
            ->event('password-reset-sent')
            ->log('Password Reset Sent');
    }
}
