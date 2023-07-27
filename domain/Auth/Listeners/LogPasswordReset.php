<?php

declare(strict_types=1);

namespace Domain\Auth\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use Spatie\Activitylog\ActivityLogger;

class LogPasswordReset
{
    public function handle(PasswordReset $event): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable&\Illuminate\Database\Eloquent\Model $model */
        $model = $event->user;

        app(ActivityLogger::class)
            ->performedOn($model)
            ->event('password-reset')
            ->log('Password Reset');
    }
}
